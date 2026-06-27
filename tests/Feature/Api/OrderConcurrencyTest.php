<?php

namespace Tests\Feature\Api;

use App\Models\BatchItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\InventoryScenario;
use Tests\TestCase;

class OrderConcurrencyTest extends TestCase
{
    use InventoryScenario;
    use LazilyRefreshDatabase;

    public function test_sequential_orders_never_oversell_stock(): void
    {
        $client = $this->client();
        ['product' => $product] = $this->stock(100);

        $this->postJson('/api/orders', [
            'client_id' => $client->id,
            'products' => [['id' => $product->id, 'qty' => 60]],
        ])->assertCreated();

        $this->postJson('/api/orders', [
            'client_id' => $client->id,
            'products' => [['id' => $product->id, 'qty' => 50]],
        ])->assertUnprocessable();

        $this->assertSame(40, $this->totalRemaining());
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_failed_order_rolls_back_without_partial_writes(): void
    {
        $client = $this->client();
        ['product' => $product] = $this->stock(2);

        $this->postJson('/api/orders', [
            'client_id' => $client->id,
            'products' => [['id' => $product->id, 'qty' => 5]],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(2, BatchItem::query()->value('quantity_remaining'));
    }

    public function test_multiple_lines_in_one_order_are_atomic(): void
    {
        $client = $this->client();
        ['product' => $inStock] = $this->stock(10);
        ['product' => $outOfStock] = $this->stock(0);

        $this->postJson('/api/orders', [
            'client_id' => $client->id,
            'products' => [
                ['id' => $inStock->id, 'qty' => 5],
                ['id' => $outOfStock->id, 'qty' => 1],
            ],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, BatchItem::query()->where('product_id', $inStock->id)->value('quantity_remaining'));
    }
}
