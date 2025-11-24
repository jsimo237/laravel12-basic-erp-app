<?php

namespace App\Modules\SubscriptionsManagement\Interfaces;

use App\Modules\SubscriptionsManagement\Models\Subscription;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property Subscription[] $subscriptions
 */
interface HasSubscriptions
{

    public function subscriptions() : MorphMany;
}