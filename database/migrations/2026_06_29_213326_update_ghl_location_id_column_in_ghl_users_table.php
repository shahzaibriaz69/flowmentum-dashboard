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
        Schema::table('ghl_users', function(Blueprint $table) {
            $table->dropColumn('ghl_location_id');
            $table->json('ghl_location_ids')->nullable()->after('ghl_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::table('ghl_users', function(Blueprint $table) {
            $table->dropColumn('ghl_location_ids');
            $table->string('ghl_location_id')->nullable()->after('ghl_user_id');
        });
    }
};
