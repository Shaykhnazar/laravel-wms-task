<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\InventoryScenario;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use InventoryScenario;
    use LazilyRefreshDatabase;

    public function test_it_creates_batch_and_stock(): void
    {
        ['provider' => $provider, 'storage' => $storage] = $this->purchaseContext();
        $product = Product::factory()->create();

        $this->postJson('/api/purchases', [
            'provider_id' => $provider->id,
            'storage_id' => $storage->id,
            'purchased_at' => '2026-06-01T10:00:00Z',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 50, 'purchase_price' => 12.50],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.items.0.quantity_remaining', 50);

        $this->assertDatabaseHas('batch_items', [
            'product_id' => $product->id,
            'quantity' => 50,
            'quantity_remaining' => 50,
        ]);
    }
}
