<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCategoriaForeignInTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Remover a chave estrangeira existente
            $table->dropForeign(['categoria_id']);

            // Alterar a coluna para permitir valores nulos
            $table->foreign('categoria_id')
                  ->references('id')
                  ->on('categorias')
                  ->onDelete('set null') // Define que, ao excluir a categoria, o campo será setado como NULL
                  ->change();
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
            // Remover a chave estrangeira existente
            $table->dropForeign(['categoria_id']);

            // Restaurar a antiga chave estrangeira com onDelete('cascade')
            $table->foreign('categoria_id')
                  ->references('id')
                  ->on('categorias')
                  ->onDelete('cascade')
                  ->change();
        });
    }
}
