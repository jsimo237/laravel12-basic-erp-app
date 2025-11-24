<?php

namespace App\Modules\SalesManagement\Models;

use App\Modules\BaseModel;
use App\Modules\OrganizationManagement\Interfaces\BelongsToOrganization;
use App\Modules\SalesManagement\Constants\PaymentTransactionAction;
use App\Modules\SalesManagement\Constants\PaymentTransactionStatuses;
use App\Modules\SalesManagement\Interfaces\Payer;
use App\Modules\UsesUuidV6;
use Illuminate\Database\Eloquent\Relations\MorphTo;


/**
 * Class PaymentTransaction
 * @package App\Modules\SalesManagement\Models
 * @property string id
 * @property string payer_type
 * @property string payer_id
 * @property string entity_id
 * @property string entity_type
 * @property string action
 * @property string reason
 * @property float amount
 * @property float payer_balance_before
 * @property float payer_balance_after
 * @property-read ?Payer payer
 * @property-read BelongsToOrganization|null entity
 */
class PaymentTransaction extends BaseModel
{
    use UsesUuidV6;

    protected $table = "sales_mgt__payments_transactions";
    protected string $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'payer_type',
        'payer_id',
        'entity_type',
        'entity_id',
        'reason',
        'amount_processed',
        'action',
        'payer_balance_before',
        'payer_balance_after',
        'status',
    ];

    protected $casts = [
        "amount_processed" => "decimal:2",
        "payer_balance_before" => "decimal:2",
        "payer_balance_after" => "decimal:2",
        "action" => PaymentTransactionAction::class,
        "status" => PaymentTransactionStatuses::class,
    ];

    public function getMorphClass() : string{
        return "payment-transaction";
    }

    protected static function booted()
    {
        static::saving(function (self $transaction){

            if(!$transaction->isDirty('organization_id')){
                $organization = $transaction->payer?->organization
                                ?? $transaction->entity?->organization ?? null;

                $transaction->organization()->associate($organization);
            }

        });
    }


    // Relations

    public function entity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__);
    }

    public function payer(): MorphTo
    {
        return $this->morphTo(__FUNCTION__);
    }

    public function getObjectName(): string
    {
       return $this->getKey();
    }
}
