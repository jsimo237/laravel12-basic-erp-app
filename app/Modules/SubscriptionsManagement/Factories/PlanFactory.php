<?php

namespace App\Modules\SubscriptionsManagement\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Modules\SubscriptionsManagement\Models\Plan;

/**
 * @extends Factory
 */
class PlanFactory extends Factory
{

    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
