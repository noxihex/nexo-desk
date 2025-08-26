<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira para conectar com a tabela 'empresas'
            // onDelete('cascade') garante que, se uma empresa for deletada,
            // todos os seus serviços também serão.
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            
            $table->string('nome'); // Nome do serviço
            
            // Coluna JSON para as "Informações" (até 5 pares de chave/valor)
            // É nullable() porque pode não ter nenhuma informação preenchida.
            $table->json('informacoes')->nullable();
            
            // Coluna JSON para o "Questionário" (até 5 perguntas)
            // Também pode ser nulo.
            $table->json('questionario')->nullable();
            
            $table->timestamps(); // Cria as colunas created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicos');
    }
}
