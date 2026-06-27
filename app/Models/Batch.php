<?php

namespace App\Models;

use Database\Factories\BatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    /** @use HasFactory<BatchFactory> */
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'storage_id',
        'purchased_at',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(Storage::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BatchItem::class);
    }

    public function refundItems(): HasMany
    {
        return $this->hasMany(BatchRefundItem::class);
    }
}
