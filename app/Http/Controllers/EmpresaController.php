<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    /**
     * Exibe a lista de empresas.
     */
    public function index(Request $request)
    {
        $empresas = Empresa::when($request->filled('search'), function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->input('search') . '%');
            })
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        return view('cadastros.empresas.index', compact('empresas'));
    }

    /**
     * Exibe o formulário para criar uma nova empresa.
     */
    public function create()
    {
        return view('cadastros.empresas.create');
    }

    /**
     * Armazena uma nova empresa no banco de dados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas',
            'endereco' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'horas_contratadas' => 'required|integer',
        ]);

        Empresa::create($request->all());

        return redirect()->route('empresas.index')->with('success', 'Empresa criada com sucesso!');
    }

    /**
     * Exibe o formulário para editar uma empresa existente.
     */
    public function edit(Empresa $empresa)
    {
        $clientes = $empresa->users()->role(['cliente', 'clientedc'])->get();

        return view('cadastros.empresas.edit', compact('empresa', 'clientes'));
    }

    /**
     * Atualiza uma empresa existente no banco de dados.
     */
    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas,cnpj,' . $empresa->id,
            'endereco' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'horas_contratadas' => 'required|integer',
        ]);

        $empresa->update($request->all());

        return redirect()->route('empresas.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove uma empresa do banco de dados.
     */
    public function destroy(Empresa $empresa)
    {
        $empresa->delete();

        return redirect()->route('empresas.index')->with('success', 'Empresa excluída com sucesso!');
    }
}
