<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BatchItem> */
class BatchItemFactory extends Factory
{
    protected $model = BatchItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(10, 100);

        return [
            'batch_id' => Batch::factory(),
            'product_id' => Product::factory(),
            'purchase_price' => fake()->randomFloat(2, 1, 50),
            'quantity' => $quantity,
            'quantity_remaining' => $quantity,
        ];
    }
}
