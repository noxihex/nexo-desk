<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePermissaoFromUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remover a coluna 'permissao'
            $table->dropColumn('permissao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Caso queira reverter, você pode recriar a coluna 'permissao'
            $table->enum('permissao', ['analista', 'supervisor', 'administrador', 'cliente', 'clientedc'])->nullable();
        });
    }
}
