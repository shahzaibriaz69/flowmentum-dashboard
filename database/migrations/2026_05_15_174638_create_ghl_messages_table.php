<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() : void
    {
        Schema::create('ghl_messages', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_contact_id')->index();
            $table->string('conversation_id')->index()->nullable();
            $table->string('ghl_message_id')->unique()->index()->nullable();
            $table->string('ghl_call_id')->index()->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ghl_user_id')->index()->nullable();
            $table->string('type')->nullable(); // SMS, CALL, EMAIL, etc.
            $table->string('message_type')->nullable();
            $table->string('direction')->nullable(); // inbound, outbound
            $table->string('status')->nullable();
            $table->longText('body')->nullable();
            $table->json('attachments')->nullable();
            $table->string('content_type')->nullable();
            $table->string('source')->nullable();
            $table->string('subject')->nullable();
            $table->integer('call_duration')->nullable();
            $table->string('call_status')->nullable();
            $table->text('call_recording_url')->nullable();
            $table->string('email_message_id')->nullable();
            $table->string('thread_id')->nullable();
            $table->string('from_email')->nullable();
            $table->json('to_email')->nullable();
            $table->string('chat_widget_id')->nullable();
            $table->string('conversation_provider_id')->nullable();
            $table->string('assigned_to_ghl_user')->nullable();
            $table->boolean('delete_in_ghl')->default(false);
            $table->string('ghl_company_id')->nullable();
            $table->timestamp('date_added')->nullable();
            $table->timestamp('date_updated')->nullable();
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_messages');
    }
};
