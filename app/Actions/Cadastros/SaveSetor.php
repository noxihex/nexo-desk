<?php

namespace App\Actions\Cadastros;

use App\Models\Setor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaveSetor
{
    public function handle(array $input, ?int $id = null): Setor
    {
        app(AuthorizeCatalogs::class)->handle();
        $data = Validator::make($input, ['nome' => 'required|string|max:255'])->validate();

        return DB::transaction(function () use ($data, $id) {
            $setor = $id === null ? new Setor : Setor::lockForUpdate()->findOrFail($id);
            $setor->fill($data)->save();

            return $setor;
        });
    }
}
