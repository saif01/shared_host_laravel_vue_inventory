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
        Schema::table('stock_ledgers', function (Blueprint $table) {
            // Add batch tracking support for FIFO/LIFO
            $table->foreignId('batch_id')->nullable()->after('product_id')->constrained('product_batches')->nullOnDelete();
            $table->foreignId('serial_id')->nullable()->after('batch_id')->constrained('product_serials')->nullOnDelete();
            
            // Additional cost tracking for FIFO/LIFO/WAVG
            $table->decimal('weighted_avg_cost', 15, 2)->default(0)->after('unit_cost');
            $table->enum('cost_method', ['fifo', 'lifo', 'wavg', 'specific'])->default('wavg')->after('weighted_avg_cost');
            
            // Enhanced balance tracking
            $table->decimal('value_before', 15, 2)->default(0)->after('balance_after'); // Inventory value before
            $table->decimal('value_after', 15, 2)->default(0)->after('value_before'); // Inventory value after
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ledgers', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['serial_id']);
            $table->dropColumn([
                'batch_id',
                'serial_id',
                'weighted_avg_cost',
                'cost_method',
                'value_before',
                'value_after'
            ]);
        });
    }
};

