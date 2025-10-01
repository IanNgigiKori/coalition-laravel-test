<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products', [ProductController::class, 'getProducts']);
Route::put('/products/{id}', [ProductController::class, 'update']);