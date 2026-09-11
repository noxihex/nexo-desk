<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:acesso admin');
    }

    public function index(): View
    {
        return view('administracao.auditoria.index');
    }
}
