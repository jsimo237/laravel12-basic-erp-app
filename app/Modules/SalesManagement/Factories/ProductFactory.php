<?php

namespace App\Modules\SalesManagement\Factories;

use App\Modules\SalesManagement\Models\Product;
use App\Support\Helpers\FakeMediaHelper;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends Factory
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $buyingPrice = $this->faker->randomFloat(4, 5, 100);
        $sellingPrice = $buyingPrice * $this->faker->randomFloat(2, 1.2, 3); // Marge de 20% à 200%

        $taxRates = [5.5, 10, 20];
        $buyingTaxes = [];
        $sellingTaxes = [];

        // Générer 1 à 3 taxes aléatoires
        $numTaxes = $this->faker->numberBetween(1, 3);
        for ($i = 0; $i < $numTaxes; $i++) {
            $buyingTaxes[] = [
                'taxId' => $this->faker->uuid,
                'rate' => $this->faker->randomElement($taxRates),
                'name' => $this->faker->randomElement(['TVA', 'Taxe locale', 'Droit de douane'])
            ];

            $sellingTaxes[] = [
                'taxId' => $this->faker->uuid,
                'rate' => $this->faker->randomElement($taxRates),
                'name' => $this->faker->randomElement(['TVA', 'Taxe de vente', 'Eco-taxe'])
            ];
        }

        return [
            'sku' => strtoupper($this->faker->bothify('PROD-#####-??')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional(0.8)->paragraph, // 80% de chance d'avoir une description
            'buying_price' => $buyingPrice,
            'selling_price' => $sellingPrice,
            'buying_taxes' => $buyingTaxes,
            'selling_taxes' => $sellingTaxes,
            'can_be_sold' => $this->faker->boolean(80), // 80% de chance d'être vendable
            'can_be_purchased' => $this->faker->boolean(90), // 90% de chance d'être achetable
            'created_by' =>  null,
            'updated_by' =>  null,
        ];
    }

    /**
     * Indicate that the product can be sold.
     */
    public function canBeSold(): self
    {
        return $this->state(fn (array $attributes) => [
            'can_be_sold' => true,
        ]);
    }

    /**
     * Indicate that the product cannot be sold.
     */
    public function cannotBeSold(): self
    {
        return $this->state(fn (array $attributes) => [
            'can_be_sold' => false,
        ]);
    }

    /**
     * Indicate that the product can be purchased.
     */
    public function canBePurchased(): self
    {
        return $this->state(fn (array $attributes) => [
            'can_be_purchased' => true,
        ]);
    }

    /**
     * Indicate that the product cannot be purchased.
     */
    public function cannotBePurchased(): self
    {
        return $this->state(fn (array $attributes) => [
            'can_be_purchased' => false,
        ]);
    }

    /**
     * Indicate that the product has a specific SKU.
     */
    public function withSku(string $sku): self
    {
        return $this->state(fn (array $attributes) => [
            'sku' => $sku,
        ]);
    }

    /**
     * Indicate that the product has a specific buying price.
     */
    public function withBuyingPrice(float $price): self
    {
        return $this->state(fn (array $attributes) => [
            'buying_price' => $price,
        ]);
    }

    /**
     * Indicate that the product has a specific selling price.
     */
    public function withSellingPrice(float $price): self
    {
        return $this->state(fn (array $attributes) => [
            'selling_price' => $price,
        ]);
    }

    /**
     * Indicate that the product has a specific margin.
     */
    public function withMargin(float $marginPercent): self
    {
        return $this->state(function (array $attributes) use ($marginPercent) {
            $buyingPrice = $attributes['buying_price'] ?? $this->faker->randomFloat(4, 5, 100);
            $sellingPrice = $buyingPrice * (1 + ($marginPercent / 100));

            return [
                'buying_price' => $buyingPrice,
                'selling_price' => round($sellingPrice, 4),
            ];
        });
    }

    /**
     * Indicate that the product has no taxes.
     */
    public function withoutTaxes(): self
    {
        return $this->state(fn (array $attributes) => [
                    'buying_taxes' => [],
                    'selling_taxes' => [],
                ]);
    }

    /**
     * Factory avec image générée et uploadée via le trait
     */
    public function withImage(array $options = []): self
    {
        return $this->afterCreating(function (Product $product) use ($options) {

            $tmpDir = storage_path('app/public/tmp');

            // Options par défaut
            $width = $options['width'] ?? 640;
            $height = $options['height'] ?? 480;
            $text = $options['text'] ?? sprintf("Product %s", $product->sku);
            $mime = $options['mime'] ?? 'image/webp';

            // Générer une fake image via le helper
            $file = FakeMediaHelper::fakeImage(
                        $tmpDir,
                        $width,
                        $height,
                        $text,
                        $mime
                    );

            // Uploader via ton trait
            $product->uploadFiles($file, $options);

            // Supprimer le fichier temporaire après upload
            if (file_exists($file->getRealPath())) {
                unlink($file->getRealPath());
            }
        });
    }



    /**
     * Configure the model factory with afterCreating events.
     */
    public function configure(): self
    {
        return $this->afterCreating(function (Product $product) {
            // Vous pouvez ajouter ici des relations supplémentaires si nécessaire
            // Exemple: attacher des catégories, des images, etc.

            // if ($product->can_be_sold) {
            //     // Logique spécifique pour les produits vendables
            // }

            // if ($product->can_be_purchased) {
            //     // Logique spécifique pour les produits achetables
            // }
        });
    }
}
