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
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BatchProfitTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_calculates_profit_per_batch_with_refunds(): void
    {
        $batch = Batch::factory()->create();
        $product = Product::factory()->create(['price' => 30.00]);

        $batchItem = BatchItem::factory()->create([
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'purchase_price' => 10.00,
            'quantity' => 10,
            'quantity_remaining' => 0,
        ]);

        $client = Client::factory()->create();
        $order = Order::factory()->create(['client_id' => $client->id]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'batch_item_id' => $batchItem->id,
            'quantity' => 10,
            'unit_price' => 30.00,
        ]);

        BatchRefundItem::factory()->create([
            'batch_id' => $batch->id,
            'batch_item_id' => $batchItem->id,
            'quantity' => 2,
            'refunded_at' => now(),
        ]);

        ClientRefundItem::factory()->create([
            'order_item_id' => $orderItem->id,
            'quantity' => 1,
            'refunded_at' => now(),
        ]);

        $response = $this->getJson('/api/batches/profit');

        $response->assertOk()
            ->assertJsonFragment([
                'batch_id' => $batch->id,
                'cost' => '80.00',
                'revenue' => '270.00',
                'profit' => '190.00',
            ]);
    }
}
