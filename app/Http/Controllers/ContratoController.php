<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contratos = Contrato::paginate(10);
        return view('cadastros.contratos.index', compact('contratos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cadastros.contratos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:contratos',
            'valor' => 'required|numeric|min:0',
            'horas_contratadas' => 'required|integer|min:0',
            'descricao' => 'nullable|string',
        ]);

        Contrato::create($request->all());

        return redirect()->route('contratos.index')->with('success', 'Contrato criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contrato $contrato)
    {
        // Geralmente não é necessário para um CRUD simples, mas pode ser implementado se precisar de uma página de detalhes.
        return view('cadastros.contratos.show', compact('contrato'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contrato $contrato)
    {
        return view('cadastros.contratos.edit', compact('contrato'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contrato $contrato)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:contratos,nome,' . $contrato->id,
            'valor' => 'required|numeric|min:0',
            'horas_contratadas' => 'required|integer|min:0',
            'descricao' => 'nullable|string',
        ]);

        $contrato->update($request->all());

        return redirect()->route('contratos.index')->with('success', 'Contrato atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contrato $contrato)
    {
        $contrato->delete();
        return redirect()->route('contratos.index')->with('success', 'Contrato excluído com sucesso!');
    }
}
