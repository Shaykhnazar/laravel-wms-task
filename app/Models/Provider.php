<?php

namespace App\Models;

use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    /** @use HasFactory<ProviderFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

}
