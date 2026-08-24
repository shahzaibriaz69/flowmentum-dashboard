<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('ghl_contacts', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index(); // Maps to ghl_locations.ghl_location_id
            $table->string('ghl_contact_id')->unique()->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('type')->nullable();
            $table->string('source')->nullable();
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('date_added')->nullable();
            $table->timestamp('date_updated')->nullable();
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_contacts');
    }
};
