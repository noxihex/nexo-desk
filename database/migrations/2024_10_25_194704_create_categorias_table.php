<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();  // Nome da categoria, único
            $table->enum('prioridade', ['Alta', 'Normal', 'Baixa'])->default('Normal'); // Prioridade
            $table->integer('slatotal'); // Quantidade de minutos para resolução
            $table->integer('slaupdate'); // Quantidade de minutos para atualização
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categorias');
    }
}
