<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Get products that have stock on hand, with total available quantity per product.
     *
     * @return Collection<int, object>
     */
    public function availableProducts(): Collection
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
            ->get();
    }

}
