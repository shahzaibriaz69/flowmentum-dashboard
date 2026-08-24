<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('ghl_appointments', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_contact_id')->index(); // Maps to ghl_contacts.ghl_contact_id
            $table->string('calendar_id')->nullable()->index(); // Maps to ghl_calendars.ghl_calendar_id
            $table->string('ghl_appointment_id')->unique()->index();
            $table->string('title')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_appointments');
    }
};
