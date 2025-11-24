<?php

namespace App\Modules\SalesManagement\Interfaces;

use App\Modules\OrganizationManagement\Interfaces\BelongsToOrganization;
use App\Modules\OrganizationManagement\Models\Organization;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface pour les entités pouvant avoir un solde (balance)
 * @package App\Modules\SalesManagement\Interfaces
 * @property float balance
 * @property-read ?MorphMany payments Relation avec les paiements
 * @property-read ?MorphMany transactions Relation avec les paiements
 * @property-read ?Organization organization Relation avec l'organisation
 */
interface Payer
{

    public function payments(): MorphMany;

    public function transactions(): MorphMany;

    public function updateBalance(
        float $amount,
        ?string $reason,
        ?float $absoluteValue,
        ?BelongsToOrganization $entity
    ): bool;
    /**
     * Vérifier si le solde est suffisant
     */
    public function hasSufficientBalance(float $amount): bool;

    /**
     * Obtenir le solde actuel
     */
    public function getBalance(): float;

    /**
     * Obtenir le solde formaté
     */
    public function getFormattedBalance(): string;

    /**
     * Historique des mouvements de solde
     */
    public function balanceHistory();

    public function verifyBalanceConsistency(): array;

    public function setBalance(float $value, ?string $reason = null): bool;

}
