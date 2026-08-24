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
        Schema::table('ghl_contacts', function(Blueprint $table) {
            $table->string('name')->nullable()->after('last_name');
            $table->string('state')->nullable()->after('phone');
            $table->unsignedBigInteger('state_id')->nullable()->after('state');
            $table->string('country')->nullable()->after('state_id');
            $table->string('dnd')->default('0')->after('country');
            $table->string('city')->nullable()->after('dnd');
            $table->string('company')->nullable()->after('city');
            $table->string('assigned_to_ghl_user')->nullable()->after('company');
            $table->date('date_of_birth')->nullable()->after('assigned_to_ghl_user');
            $table->string('ghl_company_id')->nullable()->after('date_of_birth');
            $table->string('postal_code')->nullable()->after('ghl_company_id');
            $table->unsignedBigInteger('user_id')->nullable()->after('postal_code');
        });
    }

    public function down() : void
    {
        Schema::table('ghl_contacts', function(Blueprint $table) {
            $table->dropColumn([
                'name', 'state', 'state_id', 'country', 'dnd', 'city',
                'company', 'assigned_to_ghl_user', 'date_of_birth', 'ghl_company_id',
                'postal_code', 'user_id',
            ]);
        });
    }
};
