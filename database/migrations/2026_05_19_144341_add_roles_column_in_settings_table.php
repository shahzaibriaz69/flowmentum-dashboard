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
        Schema::table('site_settings', function(Blueprint $table) {
            $table->json('roles')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::table('site_settings', function(Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
