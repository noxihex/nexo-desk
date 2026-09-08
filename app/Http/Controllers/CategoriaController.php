<?php

namespace App\Http\Controllers;

use App\Actions\Cadastros\DeleteCategoria;
use App\Actions\Cadastros\SaveCategoria;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        return view('cadastros.categorias.index');
    }

    public function create()
    {
        return view('cadastros.categorias.create');
    }

    public function store(Request $request)
    {
        app(SaveCategoria::class)->handle($request->all());

        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Categoria $categoria)
    {
        return view('cadastros.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        app(SaveCategoria::class)->handle($request->all(), $categoria->id);

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Categoria $categoria)
    {
        app(DeleteCategoria::class)->handle($categoria->id);

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
