<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => to_route('products.index'));

Route::resource('products', ProductController::class)->only('index', 'store', 'update');
Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete']);