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
        Schema::table('ghl_tags', function(Blueprint $table) {
            $table->string('ghl_tag_id')->nullable()->change();
        });
    }

    public function down() : void
    {
        Schema::table('ghl_tags', function(Blueprint $table) {
            $table->string('ghl_tag_id')->nullable(false)->change();
        });
    }
};
