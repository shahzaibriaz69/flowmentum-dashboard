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
        Schema::table('ghl_opportunity_custom_fields', function(Blueprint $table) {
            $table->string('ghl_location_id')->nullable()->index()->after('id');
            // Update unique constraint to include location
            $table->dropUnique('uniq_ghl_opportunity_cf');
            $table->unique(['ghl_location_id', 'ghl_opportunity_id', 'ghl_field_id'], 'uniq_ghl_opportunity_cf_loc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::table('ghl_opportunity_custom_fields', function(Blueprint $table) {
            $table->dropUnique('uniq_ghl_opportunity_cf_loc');
            $table->unique(['ghl_opportunity_id', 'ghl_field_id'], 'uniq_ghl_opportunity_cf');
            $table->dropColumn('ghl_location_id');
        });
    }
};
