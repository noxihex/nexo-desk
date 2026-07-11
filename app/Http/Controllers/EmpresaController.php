<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Contrato; // ADICIONADO: Importa o model de Contrato
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
        // ADICIONADO: Busca todos os contratos para listá-los no formulário
        $contratos = Contrato::all();
        // MODIFICADO: Envia os contratos para a view
        return view('cadastros.empresas.create', compact('contratos'));
    }

    /**
     * Armazena uma nova empresa no banco de dados.
     */
    public function store(Request $request)
    {
        // MODIFICADO: Adiciona a validação para os contratos
        $request->validate([
            'nome' => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas',
            'endereco' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'horas_contratadas' => 'required|integer',
            'contratos'   => 'nullable|array', // Valida que 'contratos' é um array (se enviado)
            'contratos.*' => 'exists:contratos,id', // Valida que cada ID de contrato existe
        ]);

        // MODIFICADO: Captura a empresa criada em uma variável
        $empresa = Empresa::create($request->all());

        // ADICIONADO: Se houver contratos selecionados, sincroniza a relação
        if ($request->has('contratos')) {
            $empresa->contratos()->sync($request->contratos);
        }

        return redirect()->route('empresas.index')->with('success', 'Empresa criada com sucesso!');
    }

    /**
     * Exibe o formulário para editar uma empresa existente.
     */
    public function edit(Empresa $empresa)
    {
        // Sua lógica original foi mantida
        $clientes = $empresa->users()->role(['cliente', 'clientedc'])->get();

        // ADICIONADO: Busca todos os contratos para o select
        $contratos = Contrato::all();

        // MODIFICADO: Envia a lista de contratos para a view, além dos dados que já eram enviados
        return view('cadastros.empresas.edit', compact('empresa', 'clientes', 'contratos'));
    }

    /**
     * Atualiza uma empresa existente no banco de dados.
     */
    public function update(Request $request, Empresa $empresa)
    {
        // MODIFICADO: Adiciona a validação para os contratos
        $request->validate([
            'nome' => 'required|string|max:255',
            'razao_social' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas,cnpj,' . $empresa->id,
            'endereco' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'horas_contratadas' => 'required|integer',
            'contratos'   => 'nullable|array',
            'contratos.*' => 'exists:contratos,id',
        ]);

        $empresa->update($request->all());

        // ADICIONADO: Sincroniza os contratos (adiciona/remove conforme a seleção)
        // O `?? []` garante que, se nenhum contrato for enviado, todos os vínculos serão removidos.
        $empresa->contratos()->sync($request->contratos ?? []);

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
