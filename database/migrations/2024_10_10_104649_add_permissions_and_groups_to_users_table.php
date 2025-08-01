<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPermissionsAndGroupsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Alteração para ENUM em permissao
            $table->enum('permissao', ['analista', 'supervisor', 'administrador', 'cliente', 'clientedc'])->nullable();

            // Manter grupo e setor como string por enquanto
            $table->string('grupo')->nullable();
            $table->string('setor')->nullable();
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
            // Remover as colunas no rollback
            $table->dropColumn(['permissao', 'grupo', 'setor']);
        });
    }
}
