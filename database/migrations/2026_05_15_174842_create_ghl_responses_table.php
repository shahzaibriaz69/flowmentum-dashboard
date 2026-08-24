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
        Schema::create('ghl_responses', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_contact_id')->index();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('date_created')->nullable();
            $table->timestamp('first_response')->nullable();
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_responses');
    }
};
