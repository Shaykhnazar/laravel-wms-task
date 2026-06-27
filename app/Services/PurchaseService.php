<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchItem;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * @param  array{provider_id: int, storage_id: int, purchased_at: string, items: array<int, array{product_id: int, quantity: int, purchase_price: float|string}>}  $data
     *
     * @throws \Throwable
     */
    public function purchase(array $data): Batch
    {
        return DB::transaction(function () use ($data) {
            $batch = Batch::query()->create([
                'provider_id' => $data['provider_id'],
                'storage_id' => $data['storage_id'],
                'purchased_at' => $data['purchased_at'],
            ]);

            foreach ($data['items'] as $item) {
                BatchItem::query()->create([
                    'batch_id' => $batch->id,
                    'product_id' => $item['product_id'],
                    'purchase_price' => $item['purchase_price'],
                    'quantity' => $item['quantity'],
                    'quantity_remaining' => $item['quantity'],
                ]);
            }

            return $batch->load('items');
        });
    }
}
