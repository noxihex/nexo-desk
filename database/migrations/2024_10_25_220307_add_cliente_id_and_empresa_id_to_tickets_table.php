<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClienteIdAndEmpresaIdToTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();

            $table->foreign('cliente_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');

            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
    }
}
