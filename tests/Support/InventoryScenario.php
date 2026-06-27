<?php

namespace Tests\Support;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Storage;

trait InventoryScenario
{
    /**
     * @return array{batch: Batch, product: Product, batchItem: BatchItem}
     */
    protected function stock(int $quantity, array $batchData = [], array $productData = []): array
    {
        $batch = Batch::factory()->create($batchData);
        $product = Product::factory()->create($productData);
        $batchItem = BatchItem::factory()->create([
            'batch_id' => $batch->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'quantity_remaining' => $quantity,
        ]);

        return compact('batch', 'product', 'batchItem');
    }

    protected function client(): Client
    {
        return Client::factory()->create();
    }

    protected function totalRemaining(): int
    {
        return (int) BatchItem::query()->sum('quantity_remaining');
    }

    /**
     * @return array{provider: Provider, storage: Storage}
     */
    protected function purchaseContext(): array
    {
        return [
            'provider' => Provider::factory()->create(),
            'storage' => Storage::factory()->create(),
        ];
    }
}
