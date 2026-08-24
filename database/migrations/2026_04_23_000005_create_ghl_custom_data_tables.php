<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('ghl_custom_fields', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_field_id')->index();
            $table->string('name')->nullable();
            $table->string('field_key')->nullable();
            $table->string('data_type')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->unique(['ghl_location_id', 'ghl_field_id'], 'uniq_ghl_cf_location');
        });

        Schema::create('ghl_custom_values', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_value_id')->index();
            $table->string('name')->nullable();
            $table->text('value')->nullable();
            $table->string('field_key')->nullable();
            $table->timestamps();

            $table->unique(['ghl_location_id', 'ghl_value_id'], 'uniq_ghl_cv_location');
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_custom_values');
        Schema::dropIfExists('ghl_custom_fields');
    }
};
