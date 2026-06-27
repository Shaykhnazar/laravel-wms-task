<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\InventoryScenario;
use Tests\TestCase;

class AvailableProductsTest extends TestCase
{
    use InventoryScenario;
    use LazilyRefreshDatabase;

    public function test_it_returns_products_with_stock_and_category(): void
    {
        $category = Category::factory()->create(['name' => 'Black Tea']);
        ['product' => $product] = $this->stock(7, productData: [
            'category_id' => $category->id,
            'name' => 'Earl Grey',
            'price' => 19.99,
        ]);

        $this->getJson('/api/products/available')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $product->id,
                'name' => 'Earl Grey',
                'category_name' => 'Black Tea',
                'price' => '19.99',
                'qty' => 7,
            ]);
    }

    public function test_it_excludes_zero_stock_products(): void
    {
        $this->stock(0);

        $this->getJson('/api/products/available')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_it_paginates_results(): void
    {
        $this->stock(5, productData: ['name' => 'Alpha Tea']);
        $this->stock(5, productData: ['name' => 'Beta Tea']);

        $this->getJson('/api/products/available?per_page=1&page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.name', 'Alpha Tea');
    }
}
