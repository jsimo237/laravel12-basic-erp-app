<?php

namespace App\Modules\SalesManagement\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Modules\MediableModel;
use App\Modules\SalesManagement\Interfaces\RecipientInteractWithOrderAndInvoice;
use App\Modules\SecurityManagement\Interfaces\AuthenticatableModelContract;
use App\Modules\SecurityManagement\Traits\HasUser;


/**
 * @property int id
 * @property string firstname
 * @property string lastname
 * @property string fullname
 * @property string username
 * @property string email
 * @property string phone
 */
abstract class BaseCustomer extends MediableModel
    implements AuthenticatableModelContract,
    RecipientInteractWithOrderAndInvoice {

    use HasUser;

    //FUNCTIONS

   /************ Abstract functions ************/
    abstract public static function getAuthIdentifiersFields() : array;
    abstract public static function getAuthPasswordField() : string;
   // abstract public function getGuardName() : string;
    abstract public function guardName() : string;
    abstract public function orders() : MorphMany;
    abstract public function invoices() : MorphMany;


    //
    public function getObjectName(): string
    {
        return $this->fullname;
    }
}
