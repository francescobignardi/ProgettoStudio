<?php

use App\Http\Controllers\PrimoController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ciao', [PrimoController::class, 'ciao']);

Route::get('/products', [ProductController::class, 'index']);
