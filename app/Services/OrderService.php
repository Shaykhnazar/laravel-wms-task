<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\BatchItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * @param  array<int, array{id: int, qty: int}>  $products
     *
     * @throws \Throwable
     */
    public function create(int $clientId, array $products): Order
    {
        return DB::transaction(function () use ($clientId, $products) {
            $order = Order::query()->create([
                'client_id' => $clientId,
            ]);

            foreach ($products as $line) {
                $this->allocateProduct($order, $line['id'], $line['qty']);
            }

            return $order->load(['items.batchItem', 'items.product']);
        });
    }

    /**
     * Allocate requested quantity for one product using FIFO (oldest batch first).
     *
     * @throws InsufficientStockException when stock is not sufficient
     */
    protected function allocateProduct(Order $order, int $productId, int $requestedQty): void
    {
        // FIFO: oldest purchased batch first; lock rows so concurrent orders cannot oversell
        $batchItems = BatchItem::query()
            ->select('batch_items.*')
            ->join('batches', 'batches.id', '=', 'batch_items.batch_id')
            ->where('batch_items.product_id', $productId)
            ->where('batch_items.quantity_remaining', '>', 0)
            ->orderBy('batches.purchased_at')
            ->orderBy('batch_items.id')
            ->lockForUpdate()
            ->get();

        $product = Product::query()->findOrFail($productId);
        $remaining = $requestedQty;
        $allocated = 0;

        foreach ($batchItems as $batchItem) {
            if ($remaining <= 0) {
                break;
            }

            // Take what this batch can cover; one order line per batch item row
            $take = min($remaining, $batchItem->quantity_remaining);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'batch_item_id' => $batchItem->id,
                'quantity' => $take,
                'unit_price' => $product->price,
            ]);

            $batchItem->decrement('quantity_remaining', $take);
            $remaining -= $take;
            $allocated += $take;
        }

        // Roll back the whole order transaction if we could not fulfill the full quantity
        if ($remaining > 0) {
            throw new InsufficientStockException($productId, $requestedQty, $allocated);
        }
    }
}
