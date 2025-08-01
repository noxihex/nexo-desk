<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Adiciona a coluna status com valores enumerados e o valor padrão 'aberto'
            $table->enum('status', ['aberto', 'pendente cliente', 'pendente analista', 'fechado'])
                ->default('aberto')
                ->after('descricao'); // Ajuste a posição de acordo com onde quer colocar a coluna
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
            // Remove a coluna status se a migration for revertida
            $table->dropColumn('status');
        });
    }
}
