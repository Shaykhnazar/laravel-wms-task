<?php

namespace Database\Factories;

use App\Models\ClientRefundItem;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ClientRefundItem> */
class ClientRefundItemFactory extends Factory
{
    protected $model = ClientRefundItem::class;

    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'quantity' => fake()->numberBetween(1, 3),
            'refunded_at' => fake()->dateTimeBetween('-1 week'),
        ];
    }
}
