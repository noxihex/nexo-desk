<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropNotificacoesTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('notificacoes');
    }

    public function down()
    {
        if (! Schema::hasTable('notificacoes')) {
            Schema::create('notificacoes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('titulo');
                $table->text('mensagem');
                $table->boolean('lida')->default(false);
                $table->timestamps();
            });
        }
    }
}
