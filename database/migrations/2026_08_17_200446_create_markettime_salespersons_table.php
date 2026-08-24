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
        if (Schema::hasTable('markettime_salespersons'))
        {
            return;
        }

        Schema::create('markettime_salespersons', function(Blueprint $table) {
            $table->id();

            $table->string('record_id')->unique();
            $table->string('name');
            $table->string('abbreviation')->nullable();

            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();

            $table->string('cell_country_code')->nullable();
            $table->string('cell')->nullable();

            $table->string('phone_country_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_extension')->nullable();

            $table->string('fax_country_code')->nullable();
            $table->string('fax')->nullable();
            $table->string('fax_extension')->nullable();

            $table->string('email')->nullable();

            $table->boolean('active')->default(false);
            $table->string('status')->nullable();

            $table->text('notes')->nullable();

            $table->string('image_path')->nullable();

            $table->boolean('approved')->default(false);

            $table->bigInteger('date_added')->nullable();
            $table->unsignedBigInteger('user_added')->nullable();

            $table->bigInteger('date_modified')->nullable();
            $table->bigInteger('user_modified')->nullable();

            $table->boolean('record_deleted')->default(false);

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('po_prefix')->nullable();
            $table->string('order_code')->nullable();

            $table->string('external_id')->nullable();
            $table->string('external_id2')->nullable();
            $table->string('ghl_location_id');
            // Complex API data
            $table->json('manufacturers_commission_data')->nullable();
            $table->json('salesperson_group_mappings')->nullable();

            $table->timestamps();

            $table->index('active');
            $table->index('email');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('markettime_salespersons');
    }
};
