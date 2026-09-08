<?php

namespace App\Actions\Cadastros;

use App\Models\Categoria;
use App\Models\InboundMailbox;
use App\Models\Setor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteSetor
{
    public function handle(int $id): void
    {
        app(AuthorizeCatalogs::class)->handle();

        DB::transaction(function () use ($id) {
            $setor = Setor::lockForUpdate()->findOrFail($id);

            if (InboundMailbox::where('setor_id', $id)->exists()) {
                throw ValidationException::withMessages([
                    'delete' => 'Este setor está vinculado a uma caixa de e-mail. Altere o setor da caixa antes de excluí-lo.',
                ]);
            }

            Categoria::where('setor_id', $id)->lockForUpdate()->get()->each(function ($categoria) use ($id) {
                $categoria->update(['setor_id' => $categoria->setores()
                    ->where('setores.id', '!=', $id)->orderBy('setores.id')->value('setores.id')]);
            });

            $setor->delete();
        });
    }
}
