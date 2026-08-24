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
        Schema::table('ghl_contact_tags', function(Blueprint $table) {
            $table->string('ghl_location_id')->nullable()->index()->after('id');
            // Update unique constraint
            $table->dropUnique('uniq_ghl_contact_tag');
            $table->unique(['ghl_location_id', 'ghl_contact_id', 'ghl_tag_id'], 'uniq_ghl_contact_tag_loc');
        });

        Schema::table('ghl_contact_custom_fields', function(Blueprint $table) {
            $table->string('ghl_location_id')->nullable()->index()->after('id');
            // Update unique constraint
            $table->dropUnique('uniq_ghl_contact_cf');
            $table->unique(['ghl_location_id', 'ghl_contact_id', 'ghl_field_id'], 'uniq_ghl_contact_cf_loc');
        });

        Schema::table('ghl_stage_change_logs', function(Blueprint $table) {
            $table->string('ghl_location_id')->nullable()->index()->after('id');
        });
    }

    public function down() : void
    {
        Schema::table('ghl_stage_change_logs', function(Blueprint $table) {
            $table->dropColumn('ghl_location_id');
        });

        Schema::table('ghl_contact_custom_fields', function(Blueprint $table) {
            $table->dropUnique('uniq_ghl_contact_cf_loc');
            $table->unique(['ghl_contact_id', 'ghl_field_id'], 'uniq_ghl_contact_cf');
            $table->dropColumn('ghl_location_id');
        });

        Schema::table('ghl_contact_tags', function(Blueprint $table) {
            $table->dropUnique('uniq_ghl_contact_tag_loc');
            $table->unique(['ghl_contact_id', 'ghl_tag_id'], 'uniq_ghl_contact_tag');
            $table->dropColumn('ghl_location_id');
        });
    }
};
