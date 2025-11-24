<?php

namespace App\Modules\SalesManagement\Models\Traits;

use App\Modules\OrganizationManagement\Interfaces\BelongsToOrganization;
use App\Modules\SalesManagement\Constants\BalanceHistoryAction;
use App\Modules\SalesManagement\Constants\PaymentStatuses;
use App\Modules\SalesManagement\Interfaces\Payer;
use App\Modules\SalesManagement\Models\PayerBalanceHistory;
use App\Modules\SalesManagement\Models\Payment;
use App\Modules\SecurityManagement\Models\Views\BalanceMemberView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait pour gérer le solde d'un modèle (créditer, débiter, historique des paiements)
 * Fournit les fonctionnalités de gestion de solde
 * NE contient PAS de transactions internes - à gérer au niveau appelant
 * @package App\Modules\SalesManagement\Models\Traits
 * @mixin Model
 */
trait HasBalance
{
    /**
     * Boot the trait - Initialisation du solde à 0 si non défini
     */
    protected static function bootHasBalance()
    {
        static::creating(function (self $model) {
            if (!isset($model->balance)) {
                $model->{$model->getBalanceField()} = 0;
            }
        });
    }

    /**
     * Relation avec les paiements entrants
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payer');
    }

    public function balanceHistories(): MorphMany
    {
        return $this->morphMany(PayerBalanceHistory::class, 'payer');
    }

    /**
     * Vérifier si le solde est suffisant avec verrouillage
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->getBalance() >= $amount;
    }

    /**
     * Obtenir le solde formaté
     */
    public function getFormattedBalance(): string
    {
        return   format_amount(
                    $this->getBalance(),
                        "FCFA",
                        ' ',
                        $this->getOrganization()
                    );
    }

    public function getBalanceField(): string
    {
        return 'balance';
    }
    public function getBalance(): float
    {
        $field = $this->getBalanceField();
        return $this->$field;
    }

    /**
     * Met à jour le solde de manière transactionnelle
     *
     * @param float $amount Montant à ajouter (positif) ou soustraire (négatif)
     * @param string|null $reason Raison de la modification
     * @param float|null $absoluteValue Si fourni, définit le solde à cette valeur exacte
     */
    public function updateBalance(
        float $amount,
        ?string $reason,
        ?float $absoluteValue = null,
        ?BelongsToOrganization $entity = null): bool
    {
        // Verrouillage pour éviter les concurrences
        /**
         * @var Payer|Model $payer
         */
        $payer = $this->lockForUpdate()->first();

        $column = $payer->getBalanceField();
        $balanceBefore = $payer->getBalance();
        $action = BalanceHistoryAction::CREDIT;

        if ($absoluteValue !== null) {
            // Définition de la valeur absolue
            $payer->$column = $absoluteValue;
            $action = BalanceHistoryAction::REPAIR;
            $payer->saveQuietly();
            $amountChanged = $absoluteValue - $balanceBefore;
        } elseif ($amount > 0) {
            $payer->incrementQuietly($column, $amount); // Ajout
            $amountChanged = $amount;
        } elseif ($amount < 0) {
            $payer->decrementQuietly($column, abs($amount)); // Soustraction
            $amountChanged = $amount;
            $action = BalanceHistoryAction::DEBIT;
        } else {
            $amountChanged = 0;  // amount = 0 : aucune opération
            $action = BalanceHistoryAction::RESET;
        }

        $payer->refresh();
        $balanceAfter = $payer->getBalance();

        // Enregistrement de l'historique
        $history = new PayerBalanceHistory();
        $history->amount_processed = $amountChanged;
        $history->action = $action;
        $history->reason = $reason;
        $history->payer_balance_before = $balanceBefore;
        $history->payer_balance_after = $balanceAfter;
        $history->payer()->associate($payer);
        $history->entity()->associate($entity);
        $history->save();

        // Journalisation de l'activité
        activity()
        ->performedOn($payer)
        ->withProperties([
            'amount_changed' => $amountChanged,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'absolute_value' => $absoluteValue,
            'reason' => $reason
        ])
        ->log('members.balance-updated');

        return true;
    }

    /**
     * Ajoute un montant au solde
     */
    public function addToBalance(float $amount, ?string $reason = null): bool
    {
        if ($amount <= 0) {
            return false; // On n'ajoute pas un montant négatif ou nul
        }
        return $this->updateBalance($amount, $reason);
    }

    /**
     * Soustrait un montant du solde
     */
    public function subtractFromBalance(float $amount, ?string $reason = null): bool
    {
        if ($amount <= 0) {
            return false; // On ne soustrait pas un montant négatif ou nul
        }
        return $this->updateBalance(-$amount, $reason);
    }

    /**
     * Définit le solde à une valeur absolue
     */
    public function setBalance(float $value, ?string $reason = null): bool
    {
        return $this->updateBalance(0, $reason, $value);
    }

    /**
     * Réinitialise le solde à zéro
     */
    public function resetBalance(?string $reason = null): bool
    {
        return $this->setBalance(0, $reason ?: 'Réinitialisation du solde');
    }

    /**
     * Met à jour le solde basé sur le calcul réel des paiements
     */
    public function recalculateBalanceFromPayments(?string $reason = null): bool
    {
        $realBalance = $this->calculateRealBalance();
        return $this->setBalance($realBalance, $reason ?: 'Recalcul à partir des paiements');
    }

    /**
     * Corrige automatiquement le solde si incohérent
     */
    public function autoCorrectBalanceIfNeeded(?string $reason = null): bool
    {
        $consistency = $this->verifyBalanceConsistency();

        if (!$consistency['is_consistent']) {
            return $this->setBalance(
                $consistency['calculated_balance'],
                $reason ?: sprintf('Auto-correction: écart de %s détecté', $consistency['difference'])
            );
        }

        return false;
    }


    /**
     * Calcule le solde réel à partir des paiements validated
     */
    public function calculateRealBalance(): float
    {
        return (float) $this->payments()
                        ->whereIn('status', [
                            PaymentStatuses::VALIDATED->value
                        ])
                        ->sum('remaining_amount');
    }

    /**
     * Vérification avec recalcul périodique
     */
    public function getVerifiedBalance(): float
    {
        $storedBalance = $this->getBalance();
        $realBalance = $this->calculateRealBalance();
        $tolerance = (float) config('business-core.balance.tolerance', 0.01);

        // Vérification de cohérence (optionnelle)
        if (abs($storedBalance - $realBalance) > $tolerance ) {
            // Log de l'écart et correction si nécessaire
            $this->updateBalance(0, 'Auto-correction: écart détecté');
            return $realBalance;
        }

        return $storedBalance;
    }

    /**
     * Vérifie la cohérence entre le solde stocké et le calcul réel
     */

    public function verifyBalanceConsistency(): array
    {
        // Utilisation de la vue pour une vérification rapide
        $viewBalance = $this->balanceHistory;

        if (!$viewBalance) {
            return [
                'stored_balance' => $this->getBalance(),
                'calculated_balance' => 0,
                'difference' => $this->getBalance(),
                'is_consistent' => $this->getBalance() == 0
            ];
        }

        $storedBalance = $this->getBalance();
        $viewBalanceValue = (float) $viewBalance->balance;
        $difference = $storedBalance - $viewBalanceValue;

        $tolerance = config('balance.tolerance', 0.01);
        $isConsistent = abs($difference) < (float) $tolerance;

        return [
            'stored_balance' => $storedBalance,
            'calculated_balance' => $viewBalanceValue,
            'difference' => $difference,
            'is_consistent' => $isConsistent
        ];
    }



    public function balanceHistory(): MorphOne
    {
     return $this->morphOne(BalanceMemberView::class,"payer");
    }

    public function availablesPayments()
    {
     return $this->payments()->availables()->get();
    }

}
