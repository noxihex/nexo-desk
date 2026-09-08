<?php

namespace App\Actions\Cadastros;

use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SaveCategoria
{
    public function handle(array $input, ?int $id = null): Categoria
    {
        app(AuthorizeCatalogs::class)->handle();

        return DB::transaction(function () use ($input, $id) {
            $categoria = $id === null ? new Categoria : Categoria::lockForUpdate()->findOrFail($id);
            $data = Validator::make($input, [
                'nome' => ['required', 'string', Rule::unique('categorias', 'nome')->ignore($categoria->id)],
                'prioridade' => 'required|in:Alta,Normal,Baixa',
                'slatotal' => 'required|integer|min:1',
                'slaupdate' => 'required|integer|min:1',
                'setor_ids' => 'nullable|array',
                'setor_ids.*' => 'integer|distinct|exists:setores,id',
            ])->validate();
            $setorIds = collect($data['setor_ids'] ?? [])->map(fn ($id) => (int) $id)->sort()->values();
            $setorLegado = $setorIds->contains((int) $categoria->setor_id)
                ? $categoria->setor_id : $setorIds->first();

            $categoria->fill(collect($data)->except('setor_ids')->all());
            $categoria->setor_id = $setorLegado;
            $categoria->save();
            $categoria->setores()->sync($setorIds->all());

            return $categoria;
        });
    }
}
