<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrimoController extends Controller
{
    public function ciao()
    {
        return view('helloworld', [
            'nome' => 'Francesco',
            'colori' => ['rosso', 'blu', 'giallo', 'verde'],
            'ruoli' => ['Paolo' => 'Amministratore', 'Luca' => 'Operaio', 'Luigi' => 'Risorse umane']
        ]);
    }
}
