<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Setor; // Importação do modelo Setor
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    /**
     * Exibe a lista de categorias.
     */
    public function index(Request $request)
{
    $categorias = Categoria::with('setores')
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->where('nome', 'like', '%' . $request->input('search') . '%');
        })
        ->orderBy('nome')
        ->paginate(10)
        ->withQueryString();

    return view('cadastros.categorias.index', compact('categorias'));
}



    /**
     * Mostra o formulário de criação de uma nova categoria.
     */
    public function create()
    {
        // Buscar todos os setores para exibir no formulário
        $setores = Setor::all();
        return view('cadastros.categorias.create', compact('setores'));
    }

    /**
     * Armazena uma nova categoria no banco de dados.
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $validatedData = $request->validate([
            'nome' => 'required|string|unique:categorias',
            'prioridade' => 'required|in:Alta,Normal,Baixa',
            'slatotal' => 'required|integer|min:1',
            'slaupdate' => 'required|integer|min:1',
            'setor_ids' => 'nullable|array',
            'setor_ids.*' => 'integer|distinct|exists:setores,id',
        ]);

        DB::transaction(function () use ($validatedData) {
            $setorIds = collect($validatedData['setor_ids'] ?? [])->map(function ($id) {
                return (int) $id;
            })->sort()->values();

            $categoria = Categoria::create([
                'nome' => $validatedData['nome'],
                'prioridade' => $validatedData['prioridade'],
                'slatotal' => $validatedData['slatotal'],
                'slaupdate' => $validatedData['slaupdate'],
                'setor_id' => $setorIds->first(),
            ]);
            $categoria->setores()->sync($setorIds->all());
        });

        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso!');
    }

    /**
     * Mostra o formulário de edição de uma categoria existente.
     */
    public function edit(Categoria $categoria)
    {
        $categoria->load('setores');
        // Buscar todos os setores para exibir no formulário
        $setores = Setor::all();
        return view('cadastros.categorias.edit', compact('categoria', 'setores'));
    }

    /**
     * Atualiza uma categoria existente no banco de dados.
     */
    public function update(Request $request, Categoria $categoria)
    {
        // Validação dos dados
        $validatedData = $request->validate([
            'nome' => 'required|string|unique:categorias,nome,' . $categoria->id,
            'prioridade' => 'required|in:Alta,Normal,Baixa',
            'slatotal' => 'required|integer|min:1',
            'slaupdate' => 'required|integer|min:1',
            'setor_ids' => 'nullable|array',
            'setor_ids.*' => 'integer|distinct|exists:setores,id',
        ]);

        DB::transaction(function () use ($validatedData, $categoria) {
            $setorIds = collect($validatedData['setor_ids'] ?? [])->map(function ($id) {
                return (int) $id;
            })->sort()->values();
            $setorLegado = $setorIds->contains((int) $categoria->setor_id)
                ? $categoria->setor_id
                : $setorIds->first();

            $categoria->update([
                'nome' => $validatedData['nome'],
                'prioridade' => $validatedData['prioridade'],
                'slatotal' => $validatedData['slatotal'],
                'slaupdate' => $validatedData['slaupdate'],
                'setor_id' => $setorLegado,
            ]);
            $categoria->setores()->sync($setorIds->all());
        });

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Remove uma categoria do banco de dados.
     */
    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
        return redirect()->route('categorias.index')->with('success', 'Categoria excluída com sucesso!');
    }

    public function categoriasPorSetor($setorId)
    {
        try {
            // Busca categorias relacionadas ao setor
            $categorias = Categoria::whereHas('setores', function ($query) use ($setorId) {
                $query->whereKey($setorId);
            })->orderBy('nome')->get();

            // Retorna a lista de categorias em JSON
            return response()->json($categorias);
        } catch (\Exception $e) {
            // Log de erro (opcional)
            \Log::error('Erro ao buscar categorias: ' . $e->getMessage());

            // Retorna uma resposta de erro
            return response()->json(['error' => 'Erro ao buscar categorias.'], 500);
        }
    }


}
