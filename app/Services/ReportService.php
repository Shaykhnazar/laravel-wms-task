<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get products that have stock on hand, with total available quantity per product.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function availableProducts(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.price',
                'categories.name as category_name',
            ])
            ->selectRaw('SUM(batch_items.quantity_remaining) as qty')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('batch_items', function ($join): void {
                $join->on('batch_items.product_id', '=', 'products.id')
                    ->where('batch_items.quantity_remaining', '>', 0);
            })
            ->groupBy('products.id', 'products.name', 'products.price', 'categories.name')
            ->orderBy('products.name')
            ->paginate($perPage);
    }

    /**
     * Remaining stock per product and storage as of the given date (end of day).
     *
     * @return Collection<int, object{product_id: int, storage_id: int, quantity: int}>
     */
    public function remainingQuantities(Carbon $date): Collection
    {
        $endOfDay = $date->copy()->endOfDay();

        $rows = DB::select('
            SELECT product_id, storage_id,
                CASE WHEN SUM(signed_qty) < 0 THEN 0 ELSE SUM(signed_qty) END AS quantity
            FROM (
                SELECT batch_items.product_id, batches.storage_id, batch_items.quantity AS signed_qty
                FROM batch_items
                INNER JOIN batches ON batches.id = batch_items.batch_id
                WHERE batches.purchased_at <= ?

                UNION ALL

                SELECT order_items.product_id, batches.storage_id, -order_items.quantity AS signed_qty
                FROM order_items
                INNER JOIN orders ON orders.id = order_items.order_id
                INNER JOIN batch_items ON batch_items.id = order_items.batch_item_id
                INNER JOIN batches ON batches.id = batch_items.batch_id
                WHERE orders.created_at <= ?

                UNION ALL

                SELECT batch_items.product_id, batches.storage_id, -batch_refund_items.quantity AS signed_qty
                FROM batch_refund_items
                INNER JOIN batch_items ON batch_items.id = batch_refund_items.batch_item_id
                INNER JOIN batches ON batches.id = batch_refund_items.batch_id
                WHERE batch_refund_items.refunded_at <= ?

                UNION ALL

                SELECT order_items.product_id, batches.storage_id, client_refund_items.quantity AS signed_qty
                FROM client_refund_items
                INNER JOIN order_items ON order_items.id = client_refund_items.order_item_id
                INNER JOIN batch_items ON batch_items.id = order_items.batch_item_id
                INNER JOIN batches ON batches.id = batch_items.batch_id
                WHERE client_refund_items.refunded_at <= ?
            ) AS movements
            GROUP BY product_id, storage_id
        ', [$endOfDay, $endOfDay, $endOfDay, $endOfDay]);

        return collect($rows)->map(fn ($row) => (object) [
            'product_id' => (int) $row->product_id,
            'storage_id' => (int) $row->storage_id,
            'quantity' => (int) $row->quantity,
        ])->values();
    }
}
