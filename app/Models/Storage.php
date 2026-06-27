<?php

namespace App\Models;

use Database\Factories\StorageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    /** @use HasFactory<StorageFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
