<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->text('description')->nullable();
            
            // Additional product info
            $table->string('manufacturer')->nullable();
            $table->string('brand')->nullable();
            $table->text('specifications')->nullable();
            $table->integer('warranty_period')->nullable(); // in months
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('weight_unit')->nullable(); // kg, g, lb
            $table->decimal('dimensions_length', 10, 2)->nullable();
            $table->decimal('dimensions_width', 10, 2)->nullable();
            $table->decimal('dimensions_height', 10, 2)->nullable();
            $table->string('dimensions_unit')->nullable(); // cm, m, inch
            
            $table->string('image')->nullable();
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            
            // Tax and discount
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('is_taxable')->default(true);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            
            // Stock management
            $table->integer('minimum_stock_level')->default(0);
            $table->boolean('low_stock_alert')->default(true);
            $table->boolean('expiry_alert')->default(false);
            $table->integer('expiry_alert_days')->default(30); // Alert before X days
            
            // Enhanced tracking options
            $table->boolean('track_serial')->default(false);
            $table->boolean('track_batch')->default(false);
            $table->boolean('track_imei')->default(false);
            $table->boolean('has_variations')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
