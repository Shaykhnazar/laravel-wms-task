<?php

use App\Http\Controllers\Api\BatchRefundController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StorageReportController;
use Illuminate\Support\Facades\Route;

Route::post('/purchases', [PurchaseController::class, 'store']);
Route::post('/batches/{batch}/refunds', [BatchRefundController::class, 'store']);
Route::get('/products/available', [ProductController::class, 'available']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/storages/remaining', [StorageReportController::class, 'remaining']);
