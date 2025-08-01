<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function __construct()
    {
        // Garante que apenas usuários com permissão de admin acessem
        $this->middleware('can:acesso admin');
    }

    public function index(Request $request)
    {
        // Consulta os registros de auditoria
        $audits = Audit::with('user')->latest()->paginate(10);

        return view('administracao.auditoria.index', compact('audits'));
    }
}
