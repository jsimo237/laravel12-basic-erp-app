<?php

namespace App\Modules\SalesManagement\Models;

use App\Modules\SalesManagement\Factories\OrderItemFactory;
use App\Modules\SalesManagement\Helpers\InvoiceItemHelper;
use App\Modules\SalesManagement\Helpers\OrderItemHelper;
use App\Modules\UsesUuidV6;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Modules\OrganizationManagement\Models\Organization;
use App\Modules\SalesManagement\Interfaces\BaseOrderContract;
use App\Modules\SalesManagement\Interfaces\Orderable;


class OrderItem extends BaseOrderItem
{

    use UsesUuidV6;

    protected $table = "sales_mgt__order_items";
    protected $keyType = 'string';
    public $incrementing = false;

    const MORPH_ID_COLUMN = "orderable_id";
    const MORPH_TYPE_COLUMN = "orderable_type";
    const MORPH_FUNCTION_NAME = "orderable";

    protected $casts = [
        "taxes" => 'array',
    ];


    public function getMorphClass() : string{
        return "order-item";
    }

    protected static function newFactory()
    {
        return OrderItemFactory::new();
    }

    protected static function booted()
    {
        static::creating(function (self $item){
            $item->generateItemCode();
        });
    }

    // relation
    /**
     * an invoiceitem belongs to an invoice
     */
    public function order(): Belongsto
    {
        return $this->belongsTo(Order::class,"order_id");
    }

    /**
     * an event belongs to a user
     */
    public function orderable(): Morphto
    {
        return $this->morphTo(
                    self::MORPH_FUNCTION_NAME,
                    self::MORPH_TYPE_COLUMN,
                    self::MORPH_ID_COLUMN
                );
    }

    public function getOrder(): ?BaseOrderContract
    {
        return $this->order;
    }

    public function getOrderable(): ?Orderable
    {
        return $this->orderable;
    }

    public function getOrganization(): Organization
    {
        return $this->order?->getOrganization();
    }

    public function invoice(): HasOneThrough
    {
        return $this->hasOneThrough(
                        Invoice::class, // Modèle cible (Invoice)
                        Order::class,   // Modèle intermédiaire (Order)
                        'id',           // Clé primaire de Order (intermédiaire)
                        'order_id',     // Clé étrangère dans Invoice (vers Order)
                        'order_id',     // Clé étrangère dans OrderItem (vers Order)
                        'id'            // Clé primaire de Order (intermédiaire)
                    );

    }

    public function getObjectName(): string
    {
       return $this->code;
    }


    public function generateItemCode(): void
    {
        if (!$this->isDirty('code') || blank($this->code)) {
            if (!$this->order_id) {
                throw new \LogicException("Impossible de générer un code sans commande liée (order_id).");
            }

            // Récupérer la facture liée
            $order = $this->order()->first(['id', 'organization_id', 'created_at']);

            if (!$order) {
                throw new \LogicException("Commande introuvable pour l’item.");
            }

            // Récupérer le type du modèle lié (polymorphisme)
            $modelClass = get_class($this->orderable);

            // Obtenir le préfixe court via le helper
            $prefix = OrderItemHelper::getShortPrefixFromModel($modelClass);

            // Compter les items déjà liés à cette facture
            $nextItemCount = self::withoutGlobalScopes()
                                ->where('order_id', $this->order_id)
                                ->count() + 1;

            // Calculer le rang chronologique de la facture dans son organisation
            $orderRank = Order::withoutGlobalScopes()
                                ->where('organization_id', $order->organization_id)
                                ->where('created_at', '<=', $order->created_at)
                                ->count();

            // Générer le code final
            $this->code = "{$prefix}-{$orderRank}-{$nextItemCount}";
        }
    }

}
