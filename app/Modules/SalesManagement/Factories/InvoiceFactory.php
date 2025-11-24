<?php

namespace App\Modules\SalesManagement\Factories;


use App\Modules\SalesManagement\Constants\BalanceHistoryAction;
use App\Modules\SalesManagement\Models\BaseCustomer;
use App\Modules\SalesManagement\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{

    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'code' => 'INV-' . $this->faker->unique()->randomNumber(6),
            'status' => $this->faker->randomElement(BalanceHistoryAction::cases()),
            'amount' => $this->faker->randomFloat(2, 5000, 100000),
            'amount_paid' => 0,
            'note' => $this->faker->sentence(),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'invoicing_status' => $this->faker->randomElement(['pending', 'sent', 'viewed']),
            'delivery_status' => $this->faker->randomElement(['pending', 'delivered', 'failed']),
            'has_no_taxes' => $this->faker->boolean(20),
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'status' => BalanceHistoryAction::PAID,
//            'amount_paid' => function (array $attributes) {
//                return $attributes['amount'];
//            },
        ]);
    }

    public function forOrganization($organization): static
    {
        return $this->state([
                    'organization_id' => $organization->id,
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
