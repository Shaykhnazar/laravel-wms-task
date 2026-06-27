<?php

use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\BatchRefundController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

Route::post('/purchases', [PurchaseController::class, 'store']);
Route::post('/batches/{batch}/refunds', [BatchRefundController::class, 'store']);
Route::get('/products/available', [ProductController::class, 'available']);
