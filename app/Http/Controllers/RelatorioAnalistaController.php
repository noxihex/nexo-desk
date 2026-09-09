<?php

namespace App\Http\Controllers;

class RelatorioAnalistaController extends Controller
{
    public function index()
    {
        return view('relatorios.analista.index');
    }
}
