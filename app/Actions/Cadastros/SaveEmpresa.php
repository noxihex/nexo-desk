<?php

namespace App\Actions\Cadastros;

use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SaveEmpresa
{
    public function handle(array $input, ?int $id = null): Empresa
    {
        app(AuthorizeCatalogs::class)->handle();

        return DB::transaction(function () use ($input, $id) {
            $empresa = $id === null ? new Empresa : Empresa::lockForUpdate()->findOrFail($id);
            $data = Validator::make($input, [
                'nome' => 'required|string|max:255',
                'razao_social' => 'nullable|string|max:255',
                'cnpj' => ['required', 'string', 'max:18', Rule::unique('empresas', 'cnpj')->ignore($empresa->id)],
                'endereco' => 'required|string|max:255',
                'bairro' => 'required|string|max:255',
                'cidade' => 'required|string|max:255',
                'estado' => 'required|string|max:2',
                'horas_contratadas' => 'required|integer',
            ])->validate();
            $empresa->fill($data)->save();

            return $empresa;
        });
    }
}
