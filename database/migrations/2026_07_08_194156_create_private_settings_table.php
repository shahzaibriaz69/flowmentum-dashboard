<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('private_settings', function (Blueprint $table) {
             $table->id();
            $table->string('key');
            $table->string('label');
            $table->string('category')->nullable();
            $table->string('element')->nullable();
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->json('rules')->nullable();
            $table->json('roles')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('private_settings');
    }
};
