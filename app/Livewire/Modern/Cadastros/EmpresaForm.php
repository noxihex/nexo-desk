<?php

namespace App\Livewire\Modern\Cadastros;

use App\Actions\Cadastros\SaveEmpresa;
use App\Models\Empresa;
use Livewire\Attributes\Locked;

class EmpresaForm extends CatalogComponent
{
    #[Locked]
    public ?int $recordId = null;
    public string $nome = '';
    public string $razao_social = '';
    public string $cnpj = '';
    public string $endereco = '';
    public string $bairro = '';
    public string $cidade = '';
    public string $estado = '';
    public string $horas_contratadas = '';

    public function mount(?int $recordId = null): void
    {
        $this->recordId = $recordId;
        $record = $recordId ? Empresa::findOrFail($recordId) : null;
        foreach (['nome', 'razao_social', 'cnpj', 'endereco', 'bairro', 'cidade', 'estado', 'horas_contratadas'] as $field) {
            $this->$field = (string) old($field, $record?->$field ?? '');
        }
    }

    public function save(): void
    {
        $this->resetValidation();
        $input = $this->only(['nome', 'razao_social', 'cnpj', 'endereco', 'bairro', 'cidade', 'estado', 'horas_contratadas']);
        $input = array_map('trim', $input);
        $input['cnpj'] = preg_replace('/\D/', '', $input['cnpj']);
        app(SaveEmpresa::class)->handle($input, $this->recordId);
        session()->flash('success', $this->recordId ? 'Empresa atualizada com sucesso!' : 'Empresa criada com sucesso!');
        $this->redirectRoute('empresas.index', navigate: false);
    }

    public function render()
    {
        return view('livewire.modern.cadastros.empresa-form');
    }
}
