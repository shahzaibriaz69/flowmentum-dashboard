<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->string('event_type')->nullable();
            $table->string('location_id')->nullable()->index();
            $table->string('contact_id')->nullable()->index();
            $table->string('conversation_platform_id')->nullable()->index();
            $table->string('conversation_provider_id')->nullable();
            $table->string('chat_widget_id')->nullable();
            $table->text('from_address')->nullable();
            $table->text('to_address')->nullable();
            $table->unsignedInteger('message_type_id')->nullable();
            $table->string('message_type_string')->nullable();
            $table->string('user_id')->nullable()->index();
            $table->json('mentions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->dropColumn([
                'event_type', 'location_id', 'contact_id', 'conversation_platform_id',
                'conversation_provider_id', 'chat_widget_id', 'from_address', 'to_address',
                'message_type_id', 'message_type_string', 'user_id', 'mentions',
            ]);
        });
    }
};
