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
        Schema::create('ghl_opportunity_custom_fields', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_opportunity_id')->index();
            $table->string('ghl_field_id')->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['ghl_opportunity_id', 'ghl_field_id'], 'uniq_ghl_opportunity_cf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('ghl_opportunity_custom_fields');
    }
};
