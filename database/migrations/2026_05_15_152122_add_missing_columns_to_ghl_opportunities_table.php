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
        Schema::table('ghl_opportunities', function(Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->after('ghl_location_id');
            $table->string('assigned_to_ghl_user')->nullable()->index()->after('user_id');
            $table->timestamp('date_added')->nullable()->after('source');
        });
    }

    public function down() : void
    {
        Schema::table('ghl_opportunities', function(Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'assigned_to_ghl_user', 'date_added']);
        });
    }
};
