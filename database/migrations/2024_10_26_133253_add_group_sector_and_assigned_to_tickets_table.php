<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupSectorAndAssignedToTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Campo para associar o ticket ao grupo
            $table->foreignId('grupo_id')
                  ->nullable()
                  ->constrained('grupos')
                  ->nullOnDelete();

            // Campo para associar o ticket ao setor
            $table->foreignId('setor_id')
                  ->nullable()
                  ->constrained('setores')
                  ->nullOnDelete();

            // Campo para o analista atribuído ao ticket
            $table->foreignId('atribuido_ao_analista_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['grupo_id']);
            $table->dropColumn('grupo_id');

            $table->dropForeign(['setor_id']);
            $table->dropColumn('setor_id');

            $table->dropForeign(['atribuido_ao_analista_id']);
            $table->dropColumn('atribuido_ao_analista_id');
        });
    }
}
