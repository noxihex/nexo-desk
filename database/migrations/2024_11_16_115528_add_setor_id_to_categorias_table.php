<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSetorIdToCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->unsignedBigInteger('setor_id')->nullable()->after('nome'); // Adiciona a coluna setor_id
            $table->foreign('setor_id')->references('id')->on('setores')->onDelete('set null'); // Define a chave estrangeira
        });
    }

    public function down()
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropForeign(['setor_id']); // Remove a chave estrangeira
            $table->dropColumn('setor_id');   // Remove a coluna setor_id
        });
    }
}
