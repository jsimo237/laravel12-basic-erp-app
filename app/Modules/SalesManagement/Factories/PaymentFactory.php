<?php

namespace App\Modules\SalesManagement\Factories;

use App\Modules\SalesManagement\Constants\PaymentSource;
use App\Modules\SalesManagement\Constants\PaymentStatuses;
use App\Modules\SalesManagement\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{

    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'code' => 'PAY-' . $this->faker->unique()->randomNumber(6),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'remaining_amount' => function (array $attributes) {
                return $attributes['amount'];
            },
            'status' => $this->faker->randomElement(PaymentStatuses::cases()),
            'method' => $this->faker->randomElement(['cash', 'card', 'transfer', 'momo']),
            'source_code' => $this->faker->randomElement(PaymentSource::cases()),
            'source_reference' => $this->faker->bothify('REF-#####-??'),
            'note' => $this->faker->sentence(),
            'paid_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),
            'currency' => 'XAF',
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => PaymentStatuses::VALIDATED,
            'paid_at' => now(),
        ]);
    }

    public function forPayer($payer): static
    {
        return $this->state([
            'payer_id' => $payer->id,
            'payer_type' => get_class($payer),
        ]);
    }

}
