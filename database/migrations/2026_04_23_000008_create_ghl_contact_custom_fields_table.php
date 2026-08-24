<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::create('ghl_contact_custom_fields', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_contact_id')->index();
            $table->string('ghl_field_id')->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['ghl_contact_id', 'ghl_field_id'], 'uniq_ghl_contact_cf');
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_contact_custom_fields');
    }
};
