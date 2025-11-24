<?php

namespace App\Modules\SubscriptionsManagement\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\BaseModel;

/**
 * @property string name
 */
class PackageHasAdvantage extends BaseModel {

    protected $table = "subscriptions_mgt__package_has_advantages";


    public function getObjectName(): string
    {
        return $this->name;
    }
}
