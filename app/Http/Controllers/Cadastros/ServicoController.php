<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Servico;
use Illuminate\Http\Request;

class ServicoController extends Controller
{
    /**
     * Mostra o formulário para criar um novo serviço.
     */
    public function create(Empresa $empresa)
    {
        return view('cadastros.servicos.create', compact('empresa'));
    }

    /**
     * Armazena um novo serviço no banco de dados.
     */
    public function store(Request $request, Empresa $empresa)
    {
        // 1. ATUALIZAÇÃO: Adicionamos a validação para os novos campos
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'informacoes' => 'nullable|array',
            'informacoes.*.campo' => 'nullable|string|max:255',
            'informacoes.*.valor' => 'nullable|string|max:255',
            'questionario' => 'nullable|array',
            'questionario.*' => 'nullable|string|max:255',
        ]);

        // 2. ATUALIZAÇÃO: Adicionamos a lógica para limpar campos vazios
        if (isset($validated['informacoes'])) {
            $validated['informacoes'] = array_filter($validated['informacoes'], fn($info) => !empty($info['campo']));
        }
        if (isset($validated['questionario'])) {
            $validated['questionario'] = array_filter($validated['questionario'], fn($pergunta) => !empty($pergunta));
            $validated['questionario'] = array_values($validated['questionario']);
        }

        $empresa->servicos()->create($validated);

        return redirect()->route('empresas.edit', $empresa->id)->with('success', 'Serviço criado com sucesso!');
    }

    /**
     * Mostra o formulário para editar um serviço existente.
     */
    public function edit(Servico $servico)
    {
        return view('cadastros.servicos.edit', compact('servico'));
    }

    /**
     * Atualiza um serviço existente no banco de dados.
     */
    public function update(Request $request, Servico $servico)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'informacoes' => 'nullable|array',
            'informacoes.*.campo' => 'nullable|string|max:255',
            'informacoes.*.valor' => 'nullable|string|max:255',
            'questionario' => 'nullable|array',
            'questionario.*' => 'nullable|string|max:255',
        ]);

        // Filtra campos vazios para não salvar lixo no JSON
        if (isset($validated['informacoes'])) {
            $validated['informacoes'] = array_filter($validated['informacoes'], fn($info) => !empty($info['campo']));
        }
        if (isset($validated['questionario'])) {
            $validated['questionario'] = array_filter($validated['questionario'], fn($pergunta) => !empty($pergunta));
            $validated['questionario'] = array_values($validated['questionario']);
        }

        $servico->update($validated);

        return redirect()->route('empresas.edit', $servico->empresa_id)->with('success', 'Serviço atualizado com sucesso!');
    }

    /**
     * Remove um serviço do banco de dados.
     */
    public function destroy(Servico $servico)
    {
        $empresaId = $servico->empresa_id;
        $servico->delete();

        return redirect()->route('empresas.edit', $empresaId)->with('success', 'Serviço excluído com sucesso!');
    }
}