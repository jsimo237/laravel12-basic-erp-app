<?php

namespace App\Modules\SalesManagement\Models;

use App\Modules\SalesManagement\Constants\OrderStatuses;
use App\Modules\SalesManagement\Factories\OrderFactory;
use App\Modules\SubscriptionsManagement\Models\Subscription;
use App\Modules\UsesUuidV6;
use App\Support\Helpers\ReferenceGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Support\Exceptions\NewIdCannotGeneratedException;
use App\Support\Helpers\PDFHelper;
use Illuminate\Support\Str;


/**
 * @property-read Subscription $subscription
 */
class Order extends BaseOrder
{
    protected $table = "sales_mgt__orders";
    protected $keyType = 'string';
    public $incrementing = false;

    use UsesUuidV6;

    protected $fillable = [
        'code',
        'expired_at',
        'processed_at',
        'note',
        'status',
        'has_no_taxes',
        'discounts',
    ];

    protected $casts = [
        "discounts"    => "array",
        "expired_at"   => "datetime",
        "processed_at" => "datetime",
        "status"       => OrderStatuses::class
    ];


    public function getMorphClass() : string{
        return "order";
    }

    protected static function newFactory()
    {
        return OrderFactory::new();
    }

    protected static function booted(){

        static::creating(function (self $order){
            if(!$order->isDirty("code")){
                $order->generateUniqueValue();
            }
        });

    }
    /**
     * Trouver le modèle par sa clé de route (code)
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->where('id', $value)
                   ->orWhere('code', $value)
                    ->firstOrFail();
    }
    public function refreshOrder(): void
    {
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class,"order_id");
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class,"order_id");
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class,"order_id");
    }

    /**
     * Génère la facture au format PDF.
     *
     * @return array
     */
    public function generateInvoicePDF(): array
    {
        /**
         * @var BcInvoice $invoice
         */
        $invoice = $this->invoice;

        return PDFHelper::generateStream(
                    Str::slug($invoice->code),
                    [
                        "view" => [
                            "file" => "pdf.invoices.bill",
                            "data" => compact("invoice"),
                        ]
                    ]
                );
    }


    /**
     * @throws NewIdCannotGeneratedException
     */
    public function generateUniqueValue(string $field = "code"): void
    {
        $organization = $this->getOrganization();
        // les options supplémentaire applicable à l'opération de decompte
//        $options = [
//            "key" => $field,
//            "prefix" => "ORD".$organization->getKey(),
//            "suffix" => date("Ym"),
//            "separator" => "-",
//            "charLengthNextId" => 0,
//            "uniquesBy" => [
//                ["column" => "organization_id" , "value" => $organization->getKey()]
//            ],
//            "countBy" => [
//                ["column" => "organization_id" , "value" => $organization->getKey()],
//                ["column" => "created_at" , "value" => date("Y-m")],
//            ]
//        ];
        $this->{$field} =  ReferenceGenerator::generate(
                  static::class,
                            $organization,
                            "ORD",
                            null,
                            ["key" => "code"]
                        );
    }

    public function send(): void
    {
        // TODO: Implement send() method.
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo( __FUNCTION__,"recipient_type","recipient_id");
    }
}
