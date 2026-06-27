<?php

namespace Tests\Feature\Api;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\BatchRefundItem;
use App\Models\Client;
use App\Models\ClientRefundItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Storage;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RemainingQuantityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_calculates_remaining_quantities_as_of_date(): void
    {
        $storage = Storage::factory()->create();
        $product = Product::factory()->create();

        $batch = Batch::factory()->create([
            'storage_id' => $storage->id,
            'purchased_at' => '2026-06-01 09:00:00',
        ]);

        $batchItem = BatchItem::factory()->create([
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'quantity' => 100,
            'quantity_remaining' => 40,
            'purchase_price' => 10,
        ]);

        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'created_at' => '2026-06-05 10:00:00',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'batch_item_id' => $batchItem->id,
            'quantity' => 50,
            'unit_price' => 20,
            'created_at' => '2026-06-05 10:00:00',
        ]);

        BatchRefundItem::factory()->create([
            'batch_id' => $batch->id,
            'batch_item_id' => $batchItem->id,
            'quantity' => 10,
            'refunded_at' => '2026-06-03 12:00:00',
        ]);

        $response = $this->getJson('/api/storages/remaining?date=2026-06-04');

        $response->assertOk()
            ->assertJsonFragment([
                'product_id' => $product->id,
                'storage_id' => $storage->id,
                'quantity' => 90,
            ]);
    }
}
