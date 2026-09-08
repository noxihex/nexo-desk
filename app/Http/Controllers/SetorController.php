<?php

namespace App\Http\Controllers;

use App\Actions\Cadastros\DeleteSetor;
use App\Actions\Cadastros\SaveSetor;
use App\Models\Setor;
use Illuminate\Http\Request;

class SetorController extends Controller
{
    public function index(Request $request)
    {
        return view('cadastros.setores.index');
    }

    public function create()
    {
        return view('cadastros.setores.create');
    }

    public function store(Request $request)
    {
        app(SaveSetor::class)->handle($request->all());

        return redirect()->route('setores.index')->with('success', 'Setor criado com sucesso!');
    }

    public function edit(Setor $setor)
    {
        return view('cadastros.setores.edit', compact('setor'));
    }

    public function update(Request $request, Setor $setor)
    {
        app(SaveSetor::class)->handle($request->all(), $setor->id);

        return redirect()->route('setores.index')->with('success', 'Setor atualizado com sucesso!');
    }

    public function destroy(Setor $setor)
    {
        app(DeleteSetor::class)->handle($setor->id);

        return redirect()->route('setores.index')->with('success', 'Setor excluído com sucesso!');
    }

}
