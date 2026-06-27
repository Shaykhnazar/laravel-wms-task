<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Support\InventoryScenario;
use Tests\TestCase;

class BatchRefundTest extends TestCase
{
    use InventoryScenario;
    use LazilyRefreshDatabase;

    public function test_it_refunds_from_batch(): void
    {
        ['batch' => $batch, 'batchItem' => $batchItem] = $this->stock(20);

        $this->postJson("/api/batches/{$batch->id}/refunds", [
            'refunded_at' => '2026-06-02T12:00:00Z',
            'items' => [['batch_item_id' => $batchItem->id, 'quantity' => 5]],
        ])->assertOk();

        $this->assertDatabaseHas('batch_items', ['id' => $batchItem->id, 'quantity_remaining' => 15]);
        $this->assertDatabaseHas('batch_refund_items', ['batch_item_id' => $batchItem->id, 'quantity' => 5]);
    }

    public function test_it_rejects_refund_above_remaining_stock(): void
    {
        ['batch' => $batch, 'batchItem' => $batchItem] = $this->stock(3);

        $this->postJson("/api/batches/{$batch->id}/refunds", [
            'refunded_at' => '2026-06-02T12:00:00Z',
            'items' => [['batch_item_id' => $batchItem->id, 'quantity' => 5]],
        ])->assertUnprocessable();
    }
}
