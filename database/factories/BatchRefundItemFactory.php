<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\BatchRefundItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BatchRefundItem> */
class BatchRefundItemFactory extends Factory
{
    protected $model = BatchRefundItem::class;

    public function definition(): array
    {
        return [
            'batch_id' => Batch::factory(),
            'batch_item_id' => BatchItem::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'refunded_at' => fake()->dateTimeBetween('-1 week'),
        ];
    }
}
