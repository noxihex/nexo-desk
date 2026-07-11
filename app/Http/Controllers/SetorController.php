<?php

namespace App\Http\Controllers;

use App\Models\Setor;
use Illuminate\Http\Request;

class SetorController extends Controller
{
    /**
     * Exibe a lista de setores.
     */
    public function index(Request $request)
    {
        $setores = Setor::when($request->filled('search'), function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->input('search') . '%');
            })
            ->orderBy('nome')
            ->get();

        return view('cadastros.setores.index', compact('setores'));
    }

    /**
     * Exibe o formulário para criar um novo setor.
     */
    public function create()
    {
        return view('cadastros.setores.create');
    }

    /**
     * Armazena um novo setor no banco de dados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        Setor::create($request->all());

        return redirect()->route('setores.index')->with('success', 'Setor criado com sucesso!');
    }

    /**
     * Exibe o formulário para editar um setor existente.
     */
    public function edit(Setor $setor)
    {
        return view('cadastros.setores.edit', compact('setor'));
    }

    /**
     * Atualiza um setor existente no banco de dados.
     */
    public function update(Request $request, Setor $setor)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $setor->update($request->all());

        return redirect()->route('setores.index')->with('success', 'Setor atualizado com sucesso!');
    }

    /**
     * Remove um setor do banco de dados.
     */
    public function destroy(Setor $setor)
    {
        $setor->delete();

        return redirect()->route('setores.index')->with('success', 'Setor excluído com sucesso!');
    }
}
