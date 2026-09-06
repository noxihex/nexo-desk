<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationAndEmailInfrastructure extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            foreach (['novo_ticket', 'nova_mensagem', 'sla', 'ticket_resolvido'] as $event) {
                $table->boolean($event . '_database')->default(true);
                $table->boolean($event . '_mail')->default(true);
            }
            $table->timestamps();
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('event_key', 191);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('channel', 20);
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->unique(['event_key', 'user_id', 'channel'], 'notification_delivery_unique');
        });

        Schema::create('inbound_mailboxes', function (Blueprint $table) {
            $table->id();
            $table->string('address')->unique();
            $table->foreignId('setor_id')->constrained('setores')->onDelete('restrict');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('inbound_emails', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30)->default('mailgun');
            $table->string('provider_message_id', 191)->nullable();
            $table->string('webhook_token_hash', 64)->unique();
            $table->string('sender');
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->string('status', 30)->default('received');
            $table->string('failure_reason')->nullable();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mensagem_id')->nullable()->constrained('mensagens')->nullOnDelete();
            $table->timestamps();
            $table->unique(['provider', 'provider_message_id'], 'inbound_provider_message_unique');
        });

        Schema::create('email_reply_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sla_alert_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->string('type', 20);
            $table->timestamp('reference_at');
            $table->timestamps();
            $table->unique(['ticket_id', 'type', 'reference_at'], 'sla_alert_occurrence_unique');
        });

        Schema::table('mensagens', function (Blueprint $table) {
            $table->string('tipo', 20)->default('publica')->after('descricao');
            $table->string('origem', 20)->default('web')->after('tipo');
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('sla_update_reference_at')->nullable()->after('data_hora_transferido');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('sla_update_reference_at');
        });
        Schema::table('mensagens', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'origem']);
        });
        Schema::dropIfExists('sla_alert_occurrences');
        Schema::dropIfExists('email_reply_tokens');
        Schema::dropIfExists('inbound_emails');
        Schema::dropIfExists('inbound_mailboxes');
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('notifications');
    }
}
