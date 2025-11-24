<?php

namespace App\Modules\SalesManagement\Models;

use App\Modules\SalesManagement\Constants\InvoiceStatuses;
use App\Modules\SalesManagement\Factories\OrderFactory;
use App\Modules\SalesManagement\Models\Traits\Billable;
use App\Modules\SalesManagement\Interfaces\Billable as BillableInterface;
use App\Modules\UsesUuidV6;
use App\Support\Helpers\ReferenceGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Modules\SalesManagement\Constants\PaymentStatuses;
use App\Support\Exceptions\NewIdCannotGeneratedException;
use App\Support\Helpers\PDFHelper;
use Illuminate\Support\Str;

class Invoice extends BaseInvoice implements BillableInterface
{
    use UsesUuidV6,Billable;

    protected $table = "sales_mgt__invoices";
    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        "status" => InvoiceStatuses::class,
        "discounts" => 'array',
    ];


    public function getMorphClass() : string{
        return "invoice";
    }

    protected static function newFactory()
    {
        return OrderFactory::new();
    }

    protected static function booted(){

        static::creating(function (self $invoice){
            if(!$invoice->isDirty("code")){
                $invoice->generateUniqueValue();
            }
        });

    }


    public function refreshInvoice(): void
    {
        // TODO: Implement refreshInvoice() method.
    }

    public function items(): HasMany
    {
       return $this->hasMany(InvoiceItem::class,"invoice_id");
    }


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class,"order_id");
    }

    public function handleInvoicePaid(): void
    {
        // TODO: Implement handleInvoicePaied() method.
    }

    public function send(): void
    {
        // TODO: Implement send() method.
    }

    /**
     * @throws NewIdCannotGeneratedException|\Kirago\BusinessCore\Support\Exceptions\NewIdCannotGeneratedException
     */
    public function generateUniqueValue(string $field = "code"): void
    {
        $organization = $this->getOrganization();

        $this->{$field} =  ReferenceGenerator::generate(
                        static::class,
                                $organization,
                            "INV",
                            null,
                                ["key" => "code"]
                            );
    }

    public function produceBillPDF(): array
    {
        return PDFHelper::generateStream(
                            Str::slug($this->code),
                            [
                            "view" => [
                                "file" => "pdf.invoices.print",
                                "data" => [
                                    "invoice" => $this
                                ],
                            ]

                        ]);
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo( __FUNCTION__,"recipient_type","recipient_id");
    }

    public function getTotalPaid(): float
    {
       return $this->paymentsAllocations()
                   ->whereHas('payment', function ($query) {
                       $query->where('status', PaymentStatuses::VALIDATED->value);
                   })
                  ->sum("amount_remaining");
    }

    public function getTotalRemaining(): float
    {
       return $this->getTotalAmount() - $this->getTotalPaid();
    }

    public function getAmountDue(): float
    {
        return $this->getTotalRemaining();
    }

    public function markAsPaid(): void
    {
        // TODO: Implement markAsPaid() method.
    }

    public function markAsPartialPaid(): void
    {
        // TODO: Implement markAsPartialPaid() method.
    }

    public function markAsUnpaid(): void
    {
        // TODO: Implement markAsUnpaid() method.
    }

    public function isPaid(): bool
    {
        // TODO: Implement isPaid() method.
    }

    public function getReference(): string
    {
        return $this->code;
    }

    public function getPaymentAllocations(): Collection
    {
        // TODO: Implement getPaymentAllocations() method.
    }

    public function paymentsAllocations(): MorphMany
    {
        return $this->morphMany(PaymentAllocation::class, 'allocatable');
    }
}
