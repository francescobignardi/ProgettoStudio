<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ciao', function () {
    return view('helloworld', [
        'nome' => 'Francesco',
        'colori' => ['rosso', 'blu', 'giallo', 'verde'],
        'ruoli' => ['Paolo' => 'Amministratore', 'Luca' => 'Operaio', 'Luigi' => 'Risorse umane']
    ]);
});
