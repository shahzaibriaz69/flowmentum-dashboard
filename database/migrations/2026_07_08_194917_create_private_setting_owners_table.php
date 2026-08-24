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
        Schema::create('private_setting_owners', function(Blueprint $table) {
            $table->id();

            $table->foreignId('private_setting_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->morphs('relatable');

            $table->string('value');

            $table->timestamps();

            $table->unique([
                'private_setting_id',
                'relatable_id',
                'relatable_type',
            ],'private_setting_owners_private_setting_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('private_setting_owners');
    }
};
