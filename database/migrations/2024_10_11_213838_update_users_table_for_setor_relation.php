<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableForSetorRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove o campo 'setor' do tipo varchar
            $table->dropColumn('setor');

            // Adiciona o campo 'setor_id' como chave estrangeira
            $table->unsignedBigInteger('setor_id')->nullable()->after('grupo_id');

            // Definindo a chave estrangeira para 'setor_id' referenciando a tabela 'setores'
            $table->foreign('setor_id')->references('id')->on('setores')->onDelete('set null');
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
            // Adiciona o campo 'setor' novamente se for necessário reverter
            $table->string('setor')->nullable();

            // Remove a chave estrangeira e o campo 'setor_id'
            $table->dropForeign(['setor_id']);
            $table->dropColumn('setor_id');
        });
    }
}
