<?php

namespace App\Modules\SalesManagement\Factories;


use App\Modules\SalesManagement\Interfaces\BillableItem;
use App\Modules\SalesManagement\Interfaces\Orderable;
use App\Modules\SalesManagement\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{

    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->randomFloat(2, 1000, 20000);
        $total = $quantity * $unitPrice;

        return [
            'code' => 'ORD-ITEM-' . $this->faker->unique()->randomNumber(4),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'note' => $this->faker->sentence(),
            'discount' => $this->faker->randomFloat(2, 0, 50),
            'taxes' => [
                'tva' => [
                    'rate' => 19.25,
                    'amount' => $total * 0.1925
                ]
            ],
        ];
    }

    public function forOrder($order): static
    {
        return $this->state([
                    'order_id' => $order->id,
                ]);
    }

    public function forOrderable(Orderable $ordereable): static
    {
        return $this->state([
            'ordereable_id' => $ordereable->getKey(),
            'ordereable_type' => $ordereable->getMorphClass(),
        ]);
    }

}
