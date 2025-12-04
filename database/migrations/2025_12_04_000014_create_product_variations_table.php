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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('variation_type'); // e.g., Size, Color, Material
            $table->string('variation_value'); // e.g., Large, Red, Cotton
            $table->string('sku')->unique()->nullable(); // Unique SKU for this variation
            $table->string('barcode')->nullable();
            $table->decimal('additional_cost', 15, 2)->default(0); // Extra cost for this variation
            $table->decimal('additional_price', 15, 2)->default(0); // Extra selling price for this variation
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            $table->index(['product_id', 'variation_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};

