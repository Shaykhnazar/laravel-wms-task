<?php

use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\BatchRefundController;
use Illuminate\Support\Facades\Route;

Route::post('/purchases', [PurchaseController::class, 'store']);
Route::post('/batches/{batch}/refunds', [BatchRefundController::class, 'store']);
