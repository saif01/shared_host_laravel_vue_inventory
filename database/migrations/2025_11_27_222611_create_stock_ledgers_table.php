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
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->foreignId('serial_id')->nullable()->constrained('product_serials')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->enum('type', ['in', 'out']); // stock in or stock out
            $table->enum('reference_type', [
                'purchase', 'opening_stock', 'adjustment', 'transfer_in', 'transfer_out',
                'sales', 'return', 'damage', 'lost', 'purchase_return'
            ]);
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of purchase, sales, transfer, etc.
            $table->string('reference_number')->nullable(); // PO number, Invoice number, etc.
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('weighted_avg_cost', 15, 2)->default(0);
            $table->enum('cost_method', ['fifo', 'lifo', 'wavg', 'specific'])->default('wavg');
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->integer('balance_after')->default(0); // Stock balance after this transaction
            $table->decimal('value_before', 15, 2)->default(0); // Inventory value before
            $table->decimal('value_after', 15, 2)->default(0); // Inventory value after
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('transaction_date');
            $table->timestamps();
            $table->index(['product_id', 'warehouse_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
