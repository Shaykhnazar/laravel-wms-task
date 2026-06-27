<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
        ];
    }
}
