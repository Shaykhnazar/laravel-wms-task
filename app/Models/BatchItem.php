<?php

namespace App\Models;

use Database\Factories\BatchItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchItem extends Model
{
    /** @use HasFactory<BatchItemFactory> */
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'product_id',
        'purchase_price',
        'quantity',
        'quantity_remaining',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function refundItems(): HasMany
    {
        return $this->hasMany(BatchRefundItem::class);
    }
}
