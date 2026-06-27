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

    /**
     * Calculate cost, revenue, and profit for each purchase batch.
     *
     * Cost = sum(purchase_price × qty) − provider refund cost.
     * Revenue = sum(unit_price × sold qty) − client refund revenue.
     *
     * @return Collection<int, object{batch_id: int, cost: float, revenue: float, profit: float}>
     */
    public function profitPerBatch(): Collection
    {
        $rows = DB::select('
            SELECT batches.id AS batch_id,
                ROUND(COALESCE(purchases.purchase_cost, 0) - COALESCE(provider_refunds.refund_cost, 0), 2) AS cost,
                ROUND(COALESCE(sales.revenue, 0) - COALESCE(client_refunds.client_refund_revenue, 0), 2) AS revenue,
                ROUND(
                    (COALESCE(sales.revenue, 0) - COALESCE(client_refunds.client_refund_revenue, 0))
                    - (COALESCE(purchases.purchase_cost, 0) - COALESCE(provider_refunds.refund_cost, 0)),
                    2
                ) AS profit
            FROM batches
            LEFT JOIN (
                SELECT batch_id, SUM(purchase_price * quantity) AS purchase_cost
                FROM batch_items
                GROUP BY batch_id
            ) AS purchases ON purchases.batch_id = batches.id
            LEFT JOIN (
                SELECT batch_refund_items.batch_id,
                    SUM(batch_items.purchase_price * batch_refund_items.quantity) AS refund_cost
                FROM batch_refund_items
                INNER JOIN batch_items ON batch_items.id = batch_refund_items.batch_item_id
                GROUP BY batch_refund_items.batch_id
            ) AS provider_refunds ON provider_refunds.batch_id = batches.id
            LEFT JOIN (
                SELECT batch_items.batch_id,
                    SUM(order_items.unit_price * order_items.quantity) AS revenue
                FROM order_items
                INNER JOIN batch_items ON batch_items.id = order_items.batch_item_id
                GROUP BY batch_items.batch_id
            ) AS sales ON sales.batch_id = batches.id
            LEFT JOIN (
                SELECT batch_items.batch_id,
                    SUM(order_items.unit_price * client_refund_items.quantity) AS client_refund_revenue
                FROM client_refund_items
                INNER JOIN order_items ON order_items.id = client_refund_items.order_item_id
                INNER JOIN batch_items ON batch_items.id = order_items.batch_item_id
                GROUP BY batch_items.batch_id
            ) AS client_refunds ON client_refunds.batch_id = batches.id
            ORDER BY batches.id
        ');

        return collect($rows)->map(fn ($row) => (object) [
            'batch_id' => (int) $row->batch_id,
            'cost' => (float) $row->cost,
            'revenue' => (float) $row->revenue,
            'profit' => (float) $row->profit,
        ])->values();
    }
}
