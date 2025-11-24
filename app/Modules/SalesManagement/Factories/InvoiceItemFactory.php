<?php

namespace App\Modules\SalesManagement\Factories;


use App\Modules\SalesManagement\Interfaces\Invoiceable;
use App\Modules\SalesManagement\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Kirago\BusinessCore\Modules\SalesManagement\Interfaces\BaseInvoiceContract;

class InvoiceItemFactory extends Factory
{

    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 100, 5000);
        $total = $quantity * $unitPrice;

        return [
            'code' => 'ITEM-' . $this->faker->unique()->randomNumber(4),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'note' => $this->faker->sentence(),
            'discount' => $this->faker->randomFloat(2, 0, 100),
            'taxes' => [
                'tva' => [
                    'rate' => 19.25,
                    'amount' => $total * 0.1925
                ]
            ],
        ];
    }

    public function forInvoice(BaseInvoiceContract $invoice): static
    {
        return $this->state([
                    'invoice_id' => $invoice->getKey(),
                ]);
    }

    public function forInvoiceable(Invoiceable $invoiceable): static
    {
        return $this->state([
            'invoiceable_id' => $invoiceable->getKey(),
            'invoiceable_type' => $invoiceable->getMorphClass(),
        ]);
    }

}
