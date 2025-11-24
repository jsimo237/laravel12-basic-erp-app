<?php

namespace App\Modules\SalesManagement\Factories;


use App\Modules\OrganizationManagement\Models\Organization;
use App\Modules\SalesManagement\Constants\OrderStatuses;
use App\Modules\SalesManagement\Models\BaseCustomer;
use App\Modules\SalesManagement\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{

    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'code' => 'ORD-' . $this->faker->unique()->randomNumber(6),
            'status' => $this->faker->randomElement(OrderStatuses::cases()),
            'note' => $this->faker->sentence(),
            'discounts' => [
                'global' => [
                    'type' => 'percentage',
                    'value' => $this->faker->randomFloat(2, 0, 20)
                ]
            ],
            'expired_at' => $this->faker->dateTimeBetween('+5 days', '+30 days'),
            'processed_at' => $this->faker->optional(0.3)->dateTimeBetween('-30 days', 'now'),
            'has_no_taxes' => $this->faker->boolean(20),
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => OrderStatuses::VALIDATED,
            'processed_at' => now(),
        ]);
    }


    public function forOrganization(Organization $organization): static
    {
        return $this->state([
                    'organization_id' => $organization->getKey(),
                ]);
    }

    public function forRecipient(BaseCustomer $recipient): static
    {
        return $this->state([
                    'recipient_id' => $recipient->getKey(),
                    'recipient_type' => $recipient->getMorphClass(),
                ]);
    }


}
