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
        Schema::create('markettime_orders', function(Blueprint $table) {
            // ────────────────────────────────────────────────────────────────
            // PRIMARY IDENTIFIERS
            // ────────────────────────────────────────────────────────────────
            $table->id();
            $table->bigInteger('mt_record_id')->unique()->index();

            // ────────────────────────────────────────────────────────────────
            // FOREIGN KEY REFERENCES (to other synced MT entities)
            // ────────────────────────────────────────────────────────────────
            $table->string('mt_rep_group_id')->index();        // e.g., R1359
            $table->string('mt_manufacturer_id')->index();     // e.g., M24350
            $table->string('mt_retailer_id')->index();         // e.g., B2134487 (customer ID)
            $table->bigInteger('mt_retailer_ship_to_id')->nullable()->index();
            $table->string('mt_salesperson_assigned_id')->nullable()->index(); // e.g., S14368
            $table->string('mt_salesperson_order_writer_id')->nullable();

            // ────────────────────────────────────────────────────────────────
            // ORDER META & TRACKING
            // ────────────────────────────────────────────────────────────────
            $table->string('po_number')->nullable()->index();
            $table->string('manufacturer_order_number')->nullable();
            $table->string('order_type')->nullable();          // "Total Only", "Line Item"
            $table->string('order_code')->nullable();
            $table->string('public_order_id')->nullable()->unique();
            $table->string('external_id')->nullable();
            $table->string('external_id_2')->nullable();

            // ────────────────────────────────────────────────────────────────
            // ORDER STATUS (QUERYABLE COLUMNS)
            // ────────────────────────────────────────────────────────────────
            $table->string('rep_group_order_status')->default('OPEN')->index();   // OPEN, INVOICED, etc.
            $table->string('manufacturer_order_status')->default('OPEN')->index(); // OPEN, SHIPPED, etc.

            // ────────────────────────────────────────────────────────────────
            // FINANCIAL
            // ────────────────────────────────────────────────────────────────
            $table->decimal('order_total', 12, 2)->nullable();
            $table->decimal('order_discount', 10, 2)->default(0);
            $table->string('payment_term')->nullable();        // "Net 30 Days", etc.
            $table->string('payment_token')->nullable();

            // ────────────────────────────────────────────────────────────────
            // DATES (QUERYABLE)
            // ────────────────────────────────────────────────────────────────
            $table->date('order_date')->nullable()->index();
            $table->date('request_date')->nullable();
            $table->date('ship_date')->nullable()->index();
            $table->date('cancel_date')->nullable();
            $table->timestamp('date_added')->nullable();
            $table->timestamp('date_modified')->nullable()->index();  // For delta sync

            // ────────────────────────────────────────────────────────────────
            // SHIPPING & FULFILLMENT
            // ────────────────────────────────────────────────────────────────
            $table->string('shipping_method')->nullable();
            $table->string('fob_location')->nullable();
            $table->boolean('accept_back_order')->default(false);
            $table->text('shipping_notes')->nullable();

            // ────────────────────────────────────────────────────────────────
            // COMMUNICATION FLAGS
            // ────────────────────────────────────────────────────────────────
            $table->integer('retailer_date_sent')->nullable();
            $table->integer('manufacturer_date_sent')->nullable();
            $table->string('retailer_method_sent')->nullable();
            $table->string('manufacturer_method_sent')->nullable();

            // ────────────────────────────────────────────────────────────────
            // AUDIT/METADATA
            // ────────────────────────────────────────────────────────────────
            $table->integer('user_added')->nullable();
            $table->integer('user_modified')->nullable();
            $table->string('order_writer_id')->nullable();
            $table->boolean('record_deleted')->default(false)->index();
            $table->string('origin')->nullable();              // "Toys 2000, Inc."

            // ────────────────────────────────────────────────────────────────
            // NESTED DATA (JSON COLUMNS)
            // ────────────────────────────────────────────────────────────────

            // Order line items with full product details
            $table->json('order_details')->nullable();

            // Billing address block
            $table->json('bill_to_address')->nullable();

            // Shipping address block
            $table->json('ship_to_address')->nullable();

            // Payment records (invoices, commissions)
            $table->json('order_payments')->nullable();

            // Promotional details
            $table->json('order_promotions')->nullable();

            // RepGroup reference data
            $table->json('rep_group_data')->nullable();

            // Notes and special instructions
            $table->json('notes_and_instructions')->nullable();

            // Buyer/contact info
            $table->json('buyer_info')->nullable();

            // Additional order metadata
            $table->json('order_metadata')->nullable();

            // ────────────────────────────────────────────────────────────────
            // SYNC TRACKING
            // ────────────────────────────────────────────────────────────────
            $table->string('sync_status')->default('synced')->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_error')->nullable();

            // ────────────────────────────────────────────────────────────────
            // GHL REFERENCE (for bidirectional sync)
            // ────────────────────────────────────────────────────────────────
            $table->string('ghl_opportunity_id')->nullable()->unique()->index();
            $table->timestamp('ghl_synced_at')->nullable();

            // ────────────────────────────────────────────────────────────────
            // TIMESTAMPS
            // ────────────────────────────────────────────────────────────────
            $table->timestamps();

            // ────────────────────────────────────────────────────────────────
            // INDEXES FOR COMMON QUERIES
            // ────────────────────────────────────────────────────────────────
            $table->index(
                ['mt_rep_group_id', 'date_modified'],
                'idx_repgroup_modified'
            );

            $table->index(
                ['mt_retailer_id', 'order_date'],
                'idx_retailer_orderdate'
            );

            $table->index(
                ['mt_manufacturer_id', 'manufacturer_order_status'],
                'idx_manu_status'
            );

            $table->index(
                ['mt_salesperson_assigned_id', 'order_date'],
                'idx_salesperson_date'
            );

            $table->index(
                ['rep_group_order_status', 'date_modified'],
                'idx_rep_status_mod'
            );

            $table->index(
                ['manufacturer_order_status', 'date_modified'],
                'idx_manu_status_mod'
            );

            $table->index(
                ['sync_status', 'created_at'],
                'idx_sync_created'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() : void
    {
        Schema::dropIfExists('markettime_orders');
    }
};
