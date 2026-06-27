<?php

namespace App\Models;

use Database\Factories\BatchRefundItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchRefundItem extends Model
{
    /** @use HasFactory<BatchRefundItemFactory> */
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'batch_item_id',
        'quantity',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'refunded_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function batchItem(): BelongsTo
    {
        return $this->belongsTo(BatchItem::class);
    }
}
