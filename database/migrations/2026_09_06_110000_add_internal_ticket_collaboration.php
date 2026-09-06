<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInternalTicketCollaboration extends Migration
{
    public function up()
    {
        Schema::table('message_attachments', function (Blueprint $table) {
            $table->string('disk', 20)->default('public')->after('file_path');
        });

        Schema::create('mensagem_mencoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mensagem_id')->constrained('mensagens')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['mensagem_id', 'user_id']);
        });

        Schema::create('ticket_seguidores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['ticket_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_seguidores');
        Schema::dropIfExists('mensagem_mencoes');
        Schema::table('message_attachments', function (Blueprint $table) {
            $table->dropColumn('disk');
        });
    }
}
