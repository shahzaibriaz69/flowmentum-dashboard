<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ghl_locations', function (Blueprint $table) {
            $table->timestamp('mt_last_synced_at')->nullable()->after('ghl_company_id');
        });

        Schema::table('markettime_orders', function (Blueprint $table) {
            $table->foreignId('ghl_location_id')->nullable()->after('id')
                ->constrained('ghl_locations')->nullOnDelete();
            $table->string('ghl_company_id')->nullable()->after('ghl_location_id');
        });
    }

    public function down(): void
    {
        Schema::table('ghl_locations', function (Blueprint $table) {
            $table->dropColumn('mt_last_synced_at');
        });

        Schema::table('markettime_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ghl_location_id');
            $table->dropColumn('ghl_company_id');
        });
    }
};