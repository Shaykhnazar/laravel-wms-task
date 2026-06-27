<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Provider;
use App\Models\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Batch> */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'storage_id' => Storage::factory(),
            'purchased_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
