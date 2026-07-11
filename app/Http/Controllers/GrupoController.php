<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    /**
     * Exibe a lista de grupos.
     */
    public function index(Request $request)
    {
        $grupos = Grupo::when($request->filled('search'), function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->input('search') . '%');
            })
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        return view('cadastros.grupos.index', compact('grupos'));
    }

    /**
     * Exibe o formulário de criação de um novo grupo.
     */
    public function create()
    {
        return view('cadastros.grupos.create'); // Retorna a view de criação de grupos
    }

    /**
     * Armazena um novo grupo no banco de dados.
     */
    public function store(Request $request)
    {
        // Validação com mensagem personalizada de unicidade
        $request->validate([
            'nome' => 'required|string|max:255|unique:grupos,nome',
        ], [
            'nome.unique' => 'O nome do grupo já está em uso. Por favor, escolha outro nome.',
        ]);

        // Cria o grupo
        Grupo::create($request->all());

        return redirect()->route('grupos.index')->with('success', 'Grupo criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição de um grupo existente.
     */
    public function edit(Grupo $grupo)
    {
        return view('cadastros.grupos.edit', compact('grupo')); // Retorna a view de edição de grupo com o grupo existente
    }

    /**
     * Atualiza um grupo existente.
     */
    public function update(Request $request, Grupo $grupo)
    {
        // Validação com mensagem personalizada de unicidade
        $request->validate([
            'nome' => 'required|string|max:255|unique:grupos,nome,' . $grupo->id,
        ], [
            'nome.unique' => 'O nome do grupo já está em uso. Por favor, escolha outro nome.',
        ]);

        // Atualiza o grupo
        $grupo->update($request->all());

        return redirect()->route('grupos.index')->with('success', 'Grupo atualizado com sucesso!');
    }

    /**
     * Remove um grupo existente.
     */
    public function destroy(Grupo $grupo)
{
    // Setando o grupo dos usuários para null antes de deletar o grupo
    $grupo->users()->update(['grupo_id' => null]);

    // Agora podemos excluir o grupo
    $grupo->delete();

    return redirect()->route('grupos.index')->with('success', 'Grupo excluído com sucesso!');
}

}
