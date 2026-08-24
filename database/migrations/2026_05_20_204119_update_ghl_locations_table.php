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
        Schema::table('ghl_locations', function(Blueprint $table) {

            // Contact Info
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('website')->nullable()->after('phone');

            // Owner Info
            $table->string('first_name')->nullable()->after('website');
            $table->string('last_name')->nullable()->after('first_name');

            // Metadata
            $table->string('snapshot_id')->nullable()->after('domain');

            $table->timestamp('ghl_date_added')->nullable()->after('snapshot_id');
            $table->timestamp('ghl_date_updated')->nullable()->after('ghl_date_added');

            // System
            $table->boolean('is_active')->default(true)->after('settings');

            // Raw API Response
            $table->json('raw_data')->nullable()->after('is_active');

            // Change logo_url from string to text
            $table->text('logo_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::table('ghl_locations', function(Blueprint $table) {

            $table->dropColumn([
                'email',
                'phone',
                'website',
                'first_name',
                'last_name',
                'snapshot_id',
                'ghl_date_added',
                'ghl_date_updated',
                'is_active',
                'raw_data',
            ]);

            $table->string('logo_url')->nullable()->change();
        });
    }
};
