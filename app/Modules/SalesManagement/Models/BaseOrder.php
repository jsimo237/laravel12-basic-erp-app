<?php

namespace App\Modules\SalesManagement\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Modules\BaseModel;
use App\Modules\SalesManagement\Interfaces\BaseOrderContract;
use App\Modules\SalesManagement\Interfaces\OrderItemContract;
use App\Modules\SalesManagement\Interfaces\Billable;
use App\Modules\SalesManagement\Interfaces\ContainItemsContract;
use App\Modules\SalesManagement\Interfaces\HasBillingDetails;
use App\Modules\SalesManagement\Interfaces\HasRecipient;
use App\Modules\SalesManagement\Traits\WithOrderCapacities;
use App\Modules\SecurityManagement\Models\User;
use App\Support\Contracts\EventNotifiableContract;
use App\Support\Contracts\GenerateUniqueValueContrat;


/**
* @property string|int id
* @property string status
* @property string code
* @property string note
* @property Invoice invoice
* @property bool has_no_taxes
* @property \Illuminate\Database\Eloquent\Collection payments
* @property OrderItemContract[] items
* @property array<string, mixed> discounts
* @property DateTime expired_at
* @property DateTime processed_at
*/
abstract class BaseOrder extends BaseModel implements
    EventNotifiableContract,GenerateUniqueValueContrat,
    HasBillingDetails,
    ContainItemsContract,BaseOrderContract,HasRecipient
{


    use WithOrderCapacities;

    const INVOICING_TYPE_PRODUCT = 'PRODUCT';

    const INVOICING_TYPE_AMOUNT = 'AMOUNT';


    public function getRelationsMethods(): array
    {
        return [];
    }

    public function getObjectName(): string
    {
        return $this->code;
    }

    /************ Relations ************/

    /**
     * An Order is created by a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,"user_id");
    }


    /************ Abstract functions ************/
    abstract public function refreshOrder() : void;

    abstract public function items() : HasMany;

    abstract public function invoice() : HasOne;

    abstract public function generateUniqueValue(string $field = "code") : void ;



}