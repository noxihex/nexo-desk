<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackupsTable extends Migration
{
    public function up()
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id(); // ID automático
            $table->timestamp('data_hora')->default(DB::raw('CURRENT_TIMESTAMP')); // Coluna de data e hora, com valor padrão
            $table->string('local_arquivo'); // Caminho para o local do arquivo
            $table->enum('status', ['sucesso', 'falha'])->default('sucesso'); // Novo campo status com valores 'sucesso' ou 'falha'
            $table->timestamps(); // Cria colunas created_at e updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('backups');
    }
}
