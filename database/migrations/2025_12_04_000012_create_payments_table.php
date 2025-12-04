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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->enum('payment_type', ['purchase', 'sale', 'expense', 'refund'])->default('purchase');
            $table->enum('party_type', ['supplier', 'customer']); // Who is paying/receiving
            $table->unsignedBigInteger('party_id'); // supplier_id or customer_id
            $table->string('reference_type')->nullable(); // Purchase, Sale, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of purchase, sale, etc.
            $table->string('reference_number')->nullable(); // Invoice/PO number
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'card', 'mobile_banking', 'other'])->default('cash');
            $table->string('transaction_reference')->nullable(); // Cheque number, transaction ID, etc.
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['party_type', 'party_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

