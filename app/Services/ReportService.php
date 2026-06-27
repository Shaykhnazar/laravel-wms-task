<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

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

}
