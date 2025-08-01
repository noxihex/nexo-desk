<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssumidoTransferidoFinalizadoFieldsToTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Adiciona colunas para controle de usuário e data/hora
            $table->unsignedBigInteger('assumido_por_usuario_id')->nullable()->after('atribuido_ao_analista_id')->comment('Usuário que assumiu o ticket');
            $table->timestamp('data_hora_assumido')->nullable()->after('assumido_por_usuario_id')->comment('Data e hora em que o ticket foi assumido');

            $table->unsignedBigInteger('transferido_por_usuario_id')->nullable()->after('data_hora_assumido')->comment('Usuário que transferiu o ticket');
            $table->timestamp('data_hora_transferido')->nullable()->after('transferido_por_usuario_id')->comment('Data e hora em que o ticket foi transferido');

            $table->unsignedBigInteger('finalizado_por_usuario_id')->nullable()->after('data_hora_transferido')->comment('Usuário que finalizou o ticket');
            $table->timestamp('data_hora_finalizado')->nullable()->after('finalizado_por_usuario_id')->comment('Data e hora em que o ticket foi finalizado');

            // Adiciona chaves estrangeiras, caso necessário
            $table->foreign('assumido_por_usuario_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('transferido_por_usuario_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('finalizado_por_usuario_id')->references('id')->on('users')->onDelete('set null');
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
            // Remove as colunas e chaves estrangeiras adicionadas
            $table->dropForeign(['assumido_por_usuario_id']);
            $table->dropForeign(['transferido_por_usuario_id']);
            $table->dropForeign(['finalizado_por_usuario_id']);

            $table->dropColumn('assumido_por_usuario_id');
            $table->dropColumn('data_hora_assumido');
            $table->dropColumn('transferido_por_usuario_id');
            $table->dropColumn('data_hora_transferido');
            $table->dropColumn('finalizado_por_usuario_id');
            $table->dropColumn('data_hora_finalizado');
        });
    }
}
