<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchItem;
use App\Models\BatchRefundItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BatchRefundService
{
    /**
     * @param  array{refunded_at: string, items: array<int, array{batch_item_id: int, quantity: int}>}  $data
     *
     * @throws \Throwable
     */
    public function refund(Batch $batch, array $data): Batch
    {
        return DB::transaction(function () use ($batch, $data) {
            foreach ($data['items'] as $item) {
                $batchItem = BatchItem::query()
                    ->whereKey($item['batch_item_id'])
                    ->where('batch_id', $batch->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($item['quantity'] > $batchItem->quantity_remaining) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Refund quantity exceeds remaining stock for batch item {$batchItem->id}."
                        ],
                    ]);
                }

                $batchItem->decrement('quantity_remaining', $item['quantity']);

                BatchRefundItem::query()->create([
                    'batch_id' => $batch->id,
                    'batch_item_id' => $batchItem->id,
                    'quantity' => $item['quantity'],
                    'refunded_at' => $data['refunded_at'],
                ]);
            }

            return $batch->load(['items', 'refundItems']);
        });
    }
}
