<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('ghl_agencies', function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('ghl_company_id')->nullable()->index();
            $table->text('private_integration_token')->nullable();
            $table->string('agency_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('site_link')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_agencies');
    }
};
