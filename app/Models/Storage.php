<?php

namespace App\Models;

use Database\Factories\StorageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Storage extends Model
{
    /** @use HasFactory<StorageFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
