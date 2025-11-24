<?php

namespace App\Modules\SalesManagement\Models;


use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use App\Modules\BaseModel;
use App\Modules\SalesManagement\Interfaces\BaseInvoiceContract;
use App\Modules\SalesManagement\Interfaces\InvoiceItemContract;
use App\Modules\SalesManagement\Interfaces\BaseOrderContract;
use App\Modules\SalesManagement\Interfaces\Billable;
use App\Modules\SalesManagement\Interfaces\ContainItemsContract;
use App\Modules\SalesManagement\Interfaces\HasBillingDetails;
use App\Modules\SalesManagement\Interfaces\HasRecipient;
use App\Modules\SalesManagement\Traits\WithOrderCapacities;
use App\Support\Constants\Statuses;
use App\Support\Contracts\EventNotifiableContract;
use App\Support\Contracts\GenerateUniqueValueContrat;

/**
 * @property string|int id
 * @property string status
 * @property string code
 * @property string note
 * @property bool has_no_taxes
 * @property \Illuminate\Database\Eloquent\Collection payments
 * @property InvoiceItemContract[] items
 * @property array<string, mixed> discounts
 * @property DateTime expired_at
 * @property DateTime processed_at
 * @property string invoicing_status
 * @property string delivery_status
 */
abstract class BaseInvoice extends BaseModel implements
    EventNotifiableContract, GenerateUniqueValueContrat,
    ContainItemsContract,BaseInvoiceContract,
    Billable,HasRecipient,HasBillingDetails
{

    use WithOrderCapacities;

    protected $dates = [
        'expired_at',
        'processed_at',
    ];

    protected $casts = [
     //   'is_opened' => 'boolean',
        'discounts' => 'array',
    ];

    protected $attributes = [
        'status' => Statuses::INVOICE_CREATED,
     //   'invoice_type' =>  BaseOrder::INVOICING_TYPE_PRODUCT,
      //  'is_opened' =>  true,
    ];


    protected static function booted()
    {
        static::creating(function (self $invoice) {
            $invoice->generateUniqueValue();
        });

        static::saving(function (self $invoice) {
            $invoice->refreshInvoice();
        });

        self::saved(function (BaseInvoiceContract $invoice) {

            /**
             * @var BaseOrderContract
             */
            $order = $invoice->order;

            if ($order) {
               // if (!$order->invoicing_type) {
                //    $order->invoicing_type = BaseOrder::INVOICING_TYPE_PRODUCT;
                //    $order->save();
               // }
            }
        });

    }


    /************ Abstract functions ************/
    abstract public function refreshInvoice() : void;

    abstract public function items() : HasMany;

    abstract public function payments() : HasMany;

    abstract public function order() : BelongsTo;

    abstract public function recipient() : MorphTo;

    abstract public function handleInvoicePaid() :void;

    abstract public function send() :void;

    abstract public function generateUniqueValue(string $field = "code") : void ;

    abstract public function produceBillPDF() : array;

    /************ computing functions  ************/
}