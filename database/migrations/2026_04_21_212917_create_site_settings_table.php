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
        Schema::create('site_settings', function(Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('label');
            $table->string('value');
            $table->string('category')->nullable();
            $table->string('element')->nullable();
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->json('rules')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('site_settings');
    }
};
