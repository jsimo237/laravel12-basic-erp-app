<?php

namespace App\Modules\SubscriptionsManagement\Models;


use App\Modules\BaseModel;
use App\Modules\SubscriptionsManagement\Factories\PlanFactory;

/**
 * @property string title
 */
class Plan extends BaseModel {

    protected $table = "subscriptions_mgt__plans";


    protected static function newFactory(){
        return PlanFactory::new();
    }

    public function getObjectName(): string
    {
        return $this->title;
    }

    //RELATIONS
    public function packages(){
        return $this->hasMany(Package::class,"plan_id");
    }
}
