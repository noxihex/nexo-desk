<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGrupoIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        // Adiciona o campo grupo_id e remove o campo grupo
        $table->unsignedBigInteger('grupo_id')->nullable()->after('id');
        $table->foreign('grupo_id')->references('id')->on('grupos')->onDelete('set null');
        $table->dropColumn('grupo'); // Remove o campo antigo que armazenava o nome do grupo
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
        // Reverte as alterações
        $table->string('grupo')->nullable()->after('email');
        $table->dropForeign(['grupo_id']);
        $table->dropColumn('grupo_id');
    });
}
}
