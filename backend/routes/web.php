<?php

use App\Http\Controllers\PrimoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ciao', [PrimoController::class, 'ciao']);
