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
        Schema::create('ghl_invoices', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_location_id')->index();
            $table->string('ghl_invoice_id')->unique()->index(); // Globally unique GHL ID
            $table->string('invoice_number')->index();
            $table->string('status')->default('draft');
            $table->boolean('live_mode')->default(false);
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('currency')->default('USD');
            $table->string('ghl_contact_id')->index(); // GHL Contact ID
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('discount_type')->default('fixed');
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('ghl_invoice_business_details', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_invoice_id')->index(); // Related by GHL ID
            $table->string('name')->nullable();
            $table->json('address')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('website')->nullable();
            $table->text('logo_url')->nullable();
            $table->json('custom_values')->nullable();
            $table->timestamps();
        });

        Schema::create('ghl_invoice_items', function(Blueprint $table) {
            $table->id();
            $table->string('ghl_invoice_id')->index(); // Related by GHL ID
            $table->string('ghl_product_id')->nullable();
            $table->string('ghl_price_id')->nullable();
            $table->string('currency')->default('USD');
            $table->string('name')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('amount', 15, 2)->default(0);
            $table->json('taxes')->nullable();
            $table->timestamps();
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('ghl_invoice_items');
        Schema::dropIfExists('ghl_invoice_business_details');
        Schema::dropIfExists('ghl_invoices');
    }
};
