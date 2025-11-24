<?php

namespace App\Modules\SalesManagement\Models\Traits;


use App\Modules\OrganizationManagement\Interfaces\BelongsToOrganization;
use App\Modules\SalesManagement\Interfaces\Payer;
use App\Modules\SalesManagement\Models\Payment;
use App\Modules\SalesManagement\Models\PaymentAllocation;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Billable
{
    /**
     * Relation indirecte vers les paiements via les allocations.
     */
    public function payments() : HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            PaymentAllocation::class,
            'allocatable_id',      // clé étrangère sur PaymentAllocation pointant vers Invoice.id
            'id',                // clé primaire sur Payment
            'id',                  // clé locale sur Invoice
            'payment_id'      // clé étrangère sur PaymentAllocation pointant vers Payment.id
            )->where('allocatable_type', (new static)->getMorphClass());
    }

    /**
     * Relation avec les allocations de paiement
     */
    public function paymentAllocations(): MorphMany
    {
        return $this->morphMany(PaymentAllocation::class, 'allocatable');
    }

    /**
     * Payer une facture en utilisant le solde disponible
     * Gère automatiquement l'allocation sur plusieurs paiements
     *
     * @param ?float $amount Montant total à payer
     * @param ?string $description Description de l'allocation
     * @return array Les allocations créées
     * @throws Exception Si le solde est insuffisant
     */
    public function payWithBalance(
        ?float $amount = null,
        ?Payer $payer = null,
        ?string $description = null,

    ): array
    {

        $amount ??= $this->getAmountDue();
        $payer ??= $this->recipient;

        /** @var BelongsToOrganization $this */

        // Verrouillage pour éviter les races conditions

        if (!$payer->hasSufficientBalance($amount)) {
            throw new Exception('Solde insuffisant pour continuer');
        }

        $allocations = [];
        $remainingAmount = $amount;

        /**
         * Récupère les paiements disponibles triés par ancienneté (FIFO)
         * Seulement les paiements avec du solde restant
         */
        $availablePayments = $payer->payments()
                                 ->availables() // On utilise asc pour consommer les plus petits paiements en premier
                                ->lockForUpdate()
                                ->get();

        foreach ($availablePayments as $payment) {
            /**
             * Si le montant restant à allouer est couvert, on arrête
             * (utile si on a déjà couvert le montant dans une itération précédente)
             *
             * @var Payment $payment
             */

            if ($remainingAmount <= 0) break;

            /**
             * Montant à allouer depuis ce paiement
             * - Le minimum entre le montant restant du paiement et le montant restant à allouer
             * - On ne peut pas allouer plus que ce qui reste à allouer
             * - On ne peut pas allouer plus que ce qui reste dans le paiement
             * @var float $allocatableAmount
             */
            $allocatableAmount = min($payment->remaining_amount, $remainingAmount);

            /**
             * CRÉATION DE L'ALLOCATION
             *
             * L'allocation lie un paiement spécifique à une facture/entité
             * et track le montant utilisé et restant
             */
            $allocation = new PaymentAllocation();
            $allocation->amount_allocated = $allocatableAmount;
            $allocation->amount_remaining = $allocatableAmount; // Initialement tout est disponible
            $allocation->allocated_at = now();
            $allocation->allocation_reference = $description ?? $this->getReference();
            $allocation->payment()->associate($payment);
            $allocation->allocatable()->associate($this);
            $allocation->organization()->associate($this->organization);
            $allocation->save();

            // Mise à jour du paiement source
            $payment->decrementQuietly('remaining_amount', $allocatableAmount);

            // Mise à jour du solde global
            $payer->updateBalance(
                        -$allocatableAmount,
                        "Allocation de paiement pour {$this->getObjectName()}" ,
                        null,
                        $this
                    );

            $remainingAmount -= $allocatableAmount;
            $allocations[] = $allocation;
        }
     //   dd($remainingAmount,$amount,$allocations);

        if ($remainingAmount > 0) {
            throw new Exception('Paiement incomplet');
        }

        return $allocations;
    }

    /**
     * ANNULER UNE ALLOCATION - Re-crédite les paiements sources
     *
     * Cette méthode est appelée lorsqu'une facture est annulée
     * Elle remet les montants alloués dans les paiements d'origine
     *
     * @param PaymentAllocation $allocation Allocation à annuler
     * @param float|null $amount Montant spécifique à annuler (si null, annule tout)
     * @throws Exception
     */
    public function cancelPaymentAllocation(PaymentAllocation $allocation, ?float $amount = null): void
    {
        /**
         *  Verrouillage des modèles concernés
         *
         * @var Payer $lockedModel L'entité courante (member, etc.)
         * @var PaymentAllocation $lockedAllocation L'allocation à annuler
         * @var Payment $lockedPayment Le paiement source de l'allocation
         */
        $lockedModel = $this->lockForUpdate()->first();
        $lockedAllocation = $allocation->lockForUpdate()->first();
        $lockedPayment = $lockedAllocation->payment->lockForUpdate()->first();

        $amountToRelease = $amount ?? $lockedAllocation->amount_remaining;

        if ($amountToRelease > $lockedAllocation->amount_remaining) {
            throw new Exception('Montant à annuler supérieur au montant restant');
        }

        /**
         * PROCESSUS DE RE-CRÉDITATION
         * 1. Re-créditer le paiement source
         * 2. Re-créditer le solde global
         * 3. Mettre à jour l'allocation
         */
        $lockedPayment->increment('remaining_amount', $amountToRelease);
        $lockedModel->increment('balance', $amountToRelease);

        $lockedAllocation->amount_remaining -= $amountToRelease;
        $lockedAllocation->amount_used -= $amountToRelease;
        $lockedAllocation->save();

        // Si l'allocation est complètement annulée, on peut la supprimer
        if ($lockedAllocation->amount_remaining <= 0) {
            $lockedAllocation->delete();
        }
    }


    /**
     * Annuler des allocations jusqu'à un montant spécifique
     *  - Utile pour annuler partiellement une facture
     *  - Le montant total de la ligne (getItemTotalAmount) sera restitué dans le solde du payeur
     *     lié au paiement rattaché aux allocations
     *
     * @param float $amount
     * @param BelongsToOrganization|null $basedEntity
     * @return bool
     */
    public function cancelAllAllocationsForAmount(
        float $amount,
        ?BelongsToOrganization $basedEntity): bool
    {
        $allocations =   $this->paymentAllocations()
                                ->whereNotNull("cancelled_at")
                                ->where('amount_remaining', '>', 0)
                                ->orderBy('amount_remaining') // On u
                                ->lockForUpdate()
                                ->get();

        $remainingAmount = $amount;

       if ($allocations){

           foreach ($allocations as $allocation) {

               if ($remainingAmount <= 0) break;

               /**
                *  Verrouillage des modèles concernés
                *
                * @var PaymentAllocation $lockedAllocation L'allocation à annuler
                * @var Payment $lockedPayment Le paiement source de l'allocation
                */

               $lockedAllocation = $allocation->lockForUpdate()->first();
               $lockedPayment = $lockedAllocation->payment->lockForUpdate()->first();
               $payer = $lockedPayment->payer;

               /**
                * Montant à re-créditer
                *  - Utile si on a déjà couvert le montant dans une itération précédente
                *  - Ne pas dépasser le montant restant dans l'allocation ni le montant demandé)
                */
               $amountToRelease = min($lockedAllocation->amount_remaining, $remainingAmount);


               $formatedAmount = format_amount(
                                       $amountToRelease,
                               "FCFA",
                               ' ',
                                       $payer->getOrganization()
                                   );


               /**
                * PROCESSUS DE RE-CRÉDITATION
                * 1. Re-créditer le paiement source
                * 2. Re-créditer le solde global
                * 3. Mettre à jour l'allocation
                *
                */
               $lockedPayment->incrementQuietly('remaining_amount', $amountToRelease);

               // Mise à jour du solde global
               $payer->updateBalance(
                       $amountToRelease,
                       "Retour de ${$formatedAmount} suite a l'anullation de {$this->getObjectName()}" ,
                       null,
                       $this
                   );

               $remainingAmount -= $amountToRelease;

               $lockedAllocation->amount_remaining -= $amountToRelease;
               $lockedAllocation->cancellations_count++;

               // Si l'allocation est complètement annulée, on peut la supprimer
               if ($remainingAmount <= 0) {
                   $lockedAllocation->cancelled_at = now();
                   // $lockedAllocation->delete();
               }
               $lockedAllocation->save();
           }
       }

       return true;
    }
}
