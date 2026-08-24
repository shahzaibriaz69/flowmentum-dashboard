<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('ghl_stage_change_logs', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_opportunity_stage_id')->index();
            $table->string('ghl_pipeline_id')->index();
            $table->string('from_stage_id')->nullable()->index();
            $table->string('to_stage_id')->index();
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_stage_change_logs');
    }
};
