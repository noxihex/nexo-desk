<?php

namespace App\Http\Controllers;

use App\Actions\Cadastros\DeleteEmpresa;
use App\Actions\Cadastros\SaveEmpresa;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        return view('cadastros.empresas.index');
    }

    public function create()
    {
        return view('cadastros.empresas.create');
    }

    public function store(Request $request)
    {
        app(SaveEmpresa::class)->handle($request->all());

        return redirect()->route('empresas.index')->with('success', 'Empresa criada com sucesso!');
    }

    public function edit(Empresa $empresa)
    {
        return view('cadastros.empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        app(SaveEmpresa::class)->handle($request->all(), $empresa->id);

        return redirect()->route('empresas.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    public function destroy(Empresa $empresa)
    {
        app(DeleteEmpresa::class)->handle($empresa->id);

        return redirect()->route('empresas.index')->with('success', 'Empresa excluída com sucesso!');
    }

}
