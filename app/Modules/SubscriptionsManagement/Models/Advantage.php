<?php

namespace App\Modules\SubscriptionsManagement\Models;


use App\Modules\BaseModel;
use App\Modules\SubscriptionsManagement\Factories\AdvantageFactory;

class Advantage extends BaseModel {

    protected $table = "subscriptions_mgt__advantages";

    protected static function newFactory(){
        return AdvantageFactory::new();
    }

    public function getObjectName(): string
    {
        // TODO: Implement getObjectName() method.
    }
}
