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
        Schema::create('product_bin_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('bin_location_id')->constrained('bin_locations')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->boolean('is_primary')->default(false); // Primary storage location for this product
            $table->timestamps();
            
            $table->index(['product_id', 'warehouse_id']);
            $table->index(['bin_location_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bin_locations');
    }
};

