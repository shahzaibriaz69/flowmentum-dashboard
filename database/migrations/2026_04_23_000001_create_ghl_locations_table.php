<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('ghl_locations', function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ghl_location_id')->unique()->index();
            $table->string('name')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('ghl_company_id')->nullable()->index();

            // Metadata
            $table->string('domain')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('timezone')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_locations');
    }
};
