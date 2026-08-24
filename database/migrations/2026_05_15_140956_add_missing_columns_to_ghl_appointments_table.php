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
        Schema::table('ghl_appointments', function(Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->string('assigned_to_ghl_user')->nullable()->after('ghl_appointment_id');
            $table->string('ghl_company_id')->nullable()->after('ghl_location_id');
            $table->text('address')->nullable()->after('assigned_to_ghl_user');
            $table->string('ghl_group_id')->nullable()->after('title');
            $table->text('users')->nullable()->after('ghl_group_id');
            $table->string('source')->nullable()->after('users');
            $table->timestamp('date_added')->nullable()->after('notes');
            $table->timestamp('date_updated')->nullable()->after('date_added');
        });
    }

    public function down() : void
    {
        Schema::table('ghl_appointments', function(Blueprint $table) {
            $table->dropColumn([
                'user_id', 'assigned_to_ghl_user', 'ghl_company_id', 'address',
                'ghl_group_id', 'users', 'source', 'date_added', 'date_updated',
            ]);
        });
    }
};
