<?php

namespace App\Http\Controllers;

class RelatorioController extends Controller
{
    public function index()
    {
        return view('relatorios.horas.index');
    }
}
