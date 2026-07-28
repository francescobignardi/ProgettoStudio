<?php

use App\Http\Controllers\PrimoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ciao', [PrimoController::class, 'ciao']);

Route::get('/products', [ProductController::class, 'index']);

Route::get('products/create', [ProductController::class, 'create']);

Route::get('/products/{id}', [ProductController::class, 'show']);

Route::post('/products', [ProductController::class, 'store']);

Route::get('/products/{id}/edit', [ProductController::class, 'edit']);

Route::put('/products/{id}', [ProductController::class, 'update']);

Route::delete('/products/{id}', [ProductController::class, 'destroy']);

Route::resource('suppliers', SupplierController::class)->only(['index', 'create', 'store', 'show']);
