<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'parent_id' => null,
            'provider_id' => Provider::factory(),
        ];
    }

    public function child(Category $parent): static
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
            'provider_id' => null,
        ]);
    }
}
