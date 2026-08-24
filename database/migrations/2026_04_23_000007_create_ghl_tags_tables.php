<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('ghl_tags', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_tag_id')->index(); // Sometimes GHL returns a string as ID
            $table->string('name');
            $table->timestamps();

            $table->unique(['ghl_location_id', 'name'], 'uniq_ghl_tag_location');
        });

        Schema::create('ghl_contact_tags', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_contact_id')->index(); // ghl_contact_id
            $table->string('ghl_tag_id')->index();     // ghl_tag_id or name
            $table->timestamps();

            $table->unique(['ghl_contact_id', 'ghl_tag_id'], 'uniq_ghl_contact_tag');
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_contact_tags');
        Schema::dropIfExists('ghl_tags');
    }
};
