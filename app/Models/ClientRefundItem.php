<?php

namespace App\Models;

use Database\Factories\ClientRefundItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientRefundItem extends Model
{
    /** @use HasFactory<ClientRefundItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'quantity',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'refunded_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
