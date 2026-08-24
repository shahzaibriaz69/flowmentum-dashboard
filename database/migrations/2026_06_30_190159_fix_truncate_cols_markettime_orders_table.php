<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up() : void
    {
        Schema::table('markettime_orders', function(Blueprint $table) {

            $table->bigInteger('retailer_date_sent')
                ->nullable()
                ->change();

            $table->bigInteger('manufacturer_date_sent')
                ->nullable()
                ->change();

            $table->bigInteger('user_added')
                ->nullable()
                ->change();

            $table->bigInteger('user_modified')
                ->nullable()
                ->change();

            $table->bigInteger('order_writer_id')
                ->nullable()
                ->change();

            $table->timestamp('retailer_date_sent')->nullable()->change();
            $table->timestamp('manufacturer_date_sent')->nullable()->change();
        });
    }

    public function down() : void
    {
        Schema::table('markettime_orders', function(Blueprint $table) {

            $table->integer('retailer_date_sent')
                ->nullable()
                ->change();

            $table->integer('manufacturer_date_sent')
                ->nullable()
                ->change();

            $table->integer('user_added')
                ->nullable()
                ->change();

            $table->integer('user_modified')
                ->nullable()
                ->change();

            $table->string('order_writer_id')
                ->nullable()
                ->change();
        });
    }
};
