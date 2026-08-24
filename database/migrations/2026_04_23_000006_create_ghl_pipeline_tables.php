<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('ghl_pipelines', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_pipeline_id')->unique()->index();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('ghl_pipeline_stages', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_pipeline_id')->index();
            $table->string('ghl_stage_id')->unique()->index();
            $table->string('name')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('ghl_opportunities', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_contact_id')->index();
            $table->string('ghl_pipeline_id')->index();
            $table->string('ghl_pipeline_stage_id')->index();
            $table->string('ghl_opportunity_id')->unique()->index();
            $table->string('name')->nullable();
            $table->decimal('monetary_value', 15, 2)->default(0);
            $table->string('status')->nullable(); // open, won, lost, abandoned
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_opportunities');
        Schema::dropIfExists('ghl_pipeline_stages');
        Schema::dropIfExists('ghl_pipelines');
    }
};
