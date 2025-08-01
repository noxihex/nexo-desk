<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMensagensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mensagens', function (Blueprint $table) {
            $table->id();
            $table->text('descricao');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // id_do_usuario
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade'); // id_do_ticket
            $table->timestamps(); // data e hora são gerenciadas com created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mensagens');
    }
}
