<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableForEmpresaRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove o campo empresa (varchar)
            $table->dropColumn('empresa');

            // Adiciona o campo empresa_id como chave estrangeira
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('set null');
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
            // Adiciona o campo empresa de volta
            $table->string('empresa')->nullable();

            // Remove o campo empresa_id e a foreign key
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
    }
}
