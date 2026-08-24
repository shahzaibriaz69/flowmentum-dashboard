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
        Schema::create('ghl_states', function(Blueprint $table) {
            $table->id();
            $table->string('abbr');
            $table->string('name');
            $table->json('area_codes');
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_states');
    }
};
