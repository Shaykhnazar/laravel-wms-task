<?php

namespace Tests\Feature\Api;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\InventoryScenario;
use Tests\TestCase;

class OrderFifoTest extends TestCase
{
    use InventoryScenario;
    use LazilyRefreshDatabase;

    public function test_it_allocates_from_oldest_batch_first(): void
    {
        $client = $this->client();
        $product = Product::factory()->create(['price' => 25.00]);

        $olderBatch = Batch::factory()->create(['purchased_at' => '2026-06-01 10:00:00']);
        $newerBatch = Batch::factory()->create(['purchased_at' => '2026-06-10 10:00:00']);

        $olderItem = BatchItem::factory()->create([
            'batch_id' => $olderBatch->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'quantity_remaining' => 5,
        ]);

        $newerItem = BatchItem::factory()->create([
            'batch_id' => $newerBatch->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'quantity_remaining' => 10,
        ]);

        $this->postJson('/api/orders', [
            'client_id' => $client->id,
            'products' => [['id' => $product->id, 'qty' => 8]],
        ])->assertCreated();

        $this->assertDatabaseHas('order_items', ['batch_item_id' => $olderItem->id, 'quantity' => 5]);
        $this->assertDatabaseHas('order_items', ['batch_item_id' => $newerItem->id, 'quantity' => 3]);
        $this->assertDatabaseHas('batch_items', ['id' => $olderItem->id, 'quantity_remaining' => 0]);
        $this->assertDatabaseHas('batch_items', ['id' => $newerItem->id, 'quantity_remaining' => 7]);
    }
}
