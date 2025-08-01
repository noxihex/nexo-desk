<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensagem_id')->constrained('mensagens')->onDelete('cascade'); // Relaciona com a mensagem
            $table->string('file_path'); // Caminho do arquivo
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_attachments');
    }
}
