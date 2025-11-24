<?php

namespace App\Modules\SalesManagement\Interfaces;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property Collection paymentsAllocations
 */
interface Billable
{
    public function produceBillPDF() : array;

    public function getTotalAmount(): float;
    public function getAmountDue(): float;

    public function markAsPaid(): void;
    public function markAsPartialPaid(): void;
    public function markAsUnpaid(): void;

    public function isPaid(): bool;
    public function getReference():string;

    public function paymentsAllocations() : MorphMany;

    //public function getPaymentAllocations() : Collection;
}
