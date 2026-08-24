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
        Schema::table('markettime_orders', function(Blueprint $table) {
            $table->longText('sync_error')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::table('markettime_orders', function(Blueprint $table) {
            $table->string('sync_error')->nullable()->change();
        });
    }
};
