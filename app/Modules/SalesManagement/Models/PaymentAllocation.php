<?php

namespace App\Modules\SalesManagement\Models;

use App\Modules\BaseModel;
use App\Modules\UsesUuidV6;
use DateTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class PaymentAllocation
 * @package App\Modules\SalesManagement\Models
 * @property string id
 * @property string allocatable_type
 * @property string allocatable_id
 * @property float amount_allocated
 * @property float amount_remaining
 * @property float amount_used
 * @property string|null allocation_reference
 * @property DateTime|null allocated_at
 * @property DateTime|null cancelled_at
 * @property float cancellations_count
 * @property string payment_id
 * @property Payment|null payment
 */
class PaymentAllocation extends BaseModel
{
    use UsesUuidV6;

    protected $table = "sales_mgt__payments_allocations";
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'allocatable_type',
        'allocatable_id',
        'amount_allocated',
        'amount_remaining',
        'amount_used',
        'allocation_reference',
        'payment_id',
        'allocated_at',
        'cancelled_at',
        'cancellations_count',
    ];

    protected $casts = [
        "allocated_at" => "datetime",
        "cancelled_at" => "datetime",
        "amount_used" => "decimal:2",
        "amount_allocated" => "decimal:2",
        "payer_balance_before" => "decimal:2",
        "payer_balance_after" => "decimal:2",
    ];

    public function getMorphClass() : string{
        return "payment-allocation";
    }


    // Relations

    public function allocatable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__,"allocatable_type","allocatable_id");
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }


    public function getObjectName(): string
    {
       return $this->allocation_reference;
    }
}
