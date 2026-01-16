<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => to_route('products.index'));

Route::resource('products', ProductController::class)->only('index', 'store', 'update');
Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete']);

// WhatsApp Webhook Routes
Route::get('/whatsapp/webhook', [WhatsAppController::class, 'verify']);
Route::post('/whatsapp/webhook', [WhatsAppController::class, 'handleWebhook']);
