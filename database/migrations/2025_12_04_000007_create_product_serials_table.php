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
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->string('imei_number')->nullable()->unique();
            $table->enum('status', ['in_stock', 'sold', 'damaged', 'returned', 'transferred'])->default('in_stock');
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('sold_to_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->date('sold_date')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'warehouse_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_serials');
    }
};

