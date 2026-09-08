<?php

namespace App\Actions\Cadastros;

use App\Models\Categoria;
use Illuminate\Support\Facades\DB;

class DeleteCategoria
{
    public function handle(int $id): void
    {
        app(AuthorizeCatalogs::class)->handle();
        DB::transaction(fn () => Categoria::lockForUpdate()->findOrFail($id)->delete());
    }
}
