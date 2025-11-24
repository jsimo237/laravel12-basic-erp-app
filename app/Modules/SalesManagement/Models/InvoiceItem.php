<?php

namespace App\Modules\SalesManagement\Models;

use App\Modules\SalesManagement\Factories\InvoiceItemFactory;
use App\Modules\SalesManagement\Helpers\InvoiceItemHelper;
use App\Modules\UsesUuidV6;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Modules\SalesManagement\Interfaces\Invoiceable;

class InvoiceItem extends BaseInvoiceItem
{
    use UsesUuidV6;

    protected $table = "sales_mgt__invoice_items";
    protected $keyType = 'string';
    public $incrementing = false;

    const MORPH_ID_COLUMN = "invoiceable_id";
    const MORPH_TYPE_COLUMN = "invoiceable_type";
    const MORPH_FUNCTION_NAME = "invoiceable";

    /* $table->text('note')->nullable();
            $table->decimal('unit_price',20,4)->default(0);
            $table->float('quantity')->default(0);
            $table->decimal('discount',10,4)->default(0);
            $table->json('taxes')->nullable(); */

    protected $casts = [
        "taxes" => 'array',
    ];

    public function getMorphClass() : string{
        return "invoice-item";
    }

    protected static function newFactory(): InvoiceItemFactory
    {
        return InvoiceItemFactory::new();
    }

    protected static function booted()
    {
        static::saved(function (self $invoiceItem){

            // Si la quantité a changé
            if($invoiceItem->wasChanged("quantity")){
                // Si la quantité est passée à 0 ==> annulation de l'item,
                if ($invoiceItem->quantity === 0) {
                    $invoiceItem->handleItemCancelled(); // Déclenchement de la logique après annulation

                    /**
                     * Si lors de la sauvegarde,
                     * le champ cancelled_at n'a pas été modifié (ce qui signifie qu'il n'a pas encore été défini),
                     */
                    if(!$invoiceItem->isDirty("cancelled_at")){
                        $invoiceItem->updateQuietly(["cancelled_at" => now()]);
                    }
                }
            }
        });
    }


    // relation
    /**
     * an invoiceitem belongs to an invoice
     */
    public function invoice(): Belongsto
    {
        return $this->belongsTo(Invoice::class,"invoice_id");
    }

    /**
     * an event belongs to a user
     */
    public function invoiceable(): Morphto
    {
        return $this->morphTo(
                    self::MORPH_FUNCTION_NAME,
                    self::MORPH_TYPE_COLUMN,
                    self::MORPH_ID_COLUMN
                );
    }


    public function getInvoice(): BaseInvoice
    {
        return $this->invoice;
    }

    public function getInvoiceable(): ?Invoiceable
    {
        return $this->invoiceable;
    }

    public function getItemId(): string|int
    {
        return $this->getInvoiceable()?->getKey();
    }

    public function getSku(): string
    {
        return $this->getInvoiceable()?->sku;
    }

    public function getName(): string
    {
        return $this->code;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function order(): HasOneThrough
    {
        return $this->hasOneThrough(
                        Order::class,  // Modèle cible (Order)
                        Invoice::class, // Modèle intermédiaire (Invoice)
                        'id',             // Clé primaire de Invoice (intermédiaire)
                        'id',             // Clé primaire de Order
                        'invoice_id',     // Clé étrangère dans InvoiceItem (vers Invoice)
                        'order_id'        // Clé étrangère dans Invoice (vers Order)
                    );

    }


    public function generateItemCode(): void
    {
        if (!$this->isDirty('code') || blank($this->code)) {
            if (!$this->invoice_id) {
                throw new \LogicException("Impossible de générer un code sans facture liée (invoice_id).");
            }

            // Récupérer la facture liée
            $invoice = $this->invoice()->first(['id', 'organization_id', 'created_at']);

            if (!$invoice) {
                throw new \LogicException("Facture introuvable pour l’item.");
            }

            // Récupérer le type du modèle lié (polymorphisme)
            $modelClass = get_class($this->invoiceable);

            // Obtenir le préfixe court via le helper
            $prefix = InvoiceItemHelper::getShortPrefixFromModel($modelClass);

            // Compter les items déjà liés à cette facture
            $nextItemCount = self::withoutGlobalScopes()
                                ->where('invoice_id', $this->invoice_id)
                                ->count() + 1;

            // Calculer le rang chronologique de la facture dans son organisation
            $invoiceRank = Invoice::withoutGlobalScopes()
                                ->where('organization_id', $invoice->organization_id)
                                ->where('created_at', '<=', $invoice->created_at)
                                ->count();

            // Générer le code final
            $this->code = "{$prefix}-{$invoiceRank}-{$nextItemCount}";
        }
    }

    public function handleItemValidated() : void{

    }

    /**
     * @throws Exception
     */
    public function handleItemCancelled() : void{

        $this->invoice->cancelAllAllocationsForAmount($this->getItemTotalAmount());
    }
}
