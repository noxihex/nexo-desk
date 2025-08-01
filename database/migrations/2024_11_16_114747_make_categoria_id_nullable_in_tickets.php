<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeCategoriaIdNullableInTickets extends Migration
{
    public function up()
    {
        // Altera a coluna `categoria_id` para aceitar valores NULL
        DB::statement('ALTER TABLE tickets MODIFY categoria_id BIGINT(20) UNSIGNED NULL;');
    }

    public function down()
    {
        // Reverte a alteração para tornar `categoria_id` obrigatório novamente
        DB::statement('ALTER TABLE tickets MODIFY categoria_id BIGINT(20) UNSIGNED NOT NULL;');
    }
}
