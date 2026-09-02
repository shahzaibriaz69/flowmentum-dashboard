<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('location_id')->nullable()->index();
            $table->timestamp('last_message_at')->nullable()->index();
        });

        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->string('content_type')->nullable();
            $table->string('source')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['location_id', 'last_message_at']);
        });

        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->dropColumn(['status', 'content_type', 'source', 'attachments', 'sent_at']);
        });
    }
};
