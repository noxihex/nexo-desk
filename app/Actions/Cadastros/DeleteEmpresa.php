<?php

namespace App\Actions\Cadastros;

use App\Models\Empresa;
use Illuminate\Support\Facades\DB;

class DeleteEmpresa
{
    public function handle(int $id): void
    {
        app(AuthorizeCatalogs::class)->handle();
        DB::transaction(fn () => Empresa::lockForUpdate()->findOrFail($id)->delete());
    }
}
