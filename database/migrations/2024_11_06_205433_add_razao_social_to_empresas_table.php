<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRazaoSocialToEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('empresas', function (Blueprint $table) {
        $table->string('razao_social')->nullable()->after('nome');
    });
}

public function down()
{
    Schema::table('empresas', function (Blueprint $table) {
        $table->dropColumn('razao_social');
    });
}
}
