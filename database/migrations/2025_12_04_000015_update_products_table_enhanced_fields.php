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
        Schema::table('products', function (Blueprint $table) {
            // Add sub-category support
            $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained('categories')->nullOnDelete();
            
            // Enhanced tracking options
            $table->boolean('track_batch')->default(false)->after('track_serial');
            $table->boolean('track_imei')->default(false)->after('track_batch');
            $table->boolean('has_variations')->default(false)->after('track_imei');
            
            // Additional product info
            $table->string('manufacturer')->nullable()->after('description');
            $table->string('brand')->nullable()->after('manufacturer');
            $table->text('specifications')->nullable()->after('brand');
            $table->integer('warranty_period')->nullable()->after('specifications'); // in months
            $table->decimal('weight', 10, 2)->nullable()->after('warranty_period');
            $table->string('weight_unit')->nullable()->after('weight'); // kg, g, lb
            $table->decimal('dimensions_length', 10, 2)->nullable()->after('weight_unit');
            $table->decimal('dimensions_width', 10, 2)->nullable()->after('dimensions_length');
            $table->decimal('dimensions_height', 10, 2)->nullable()->after('dimensions_width');
            $table->string('dimensions_unit')->nullable()->after('dimensions_height'); // cm, m, inch
            
            // Tax and discount
            $table->decimal('tax_rate', 5, 2)->default(0)->after('selling_price');
            $table->boolean('is_taxable')->default(true)->after('tax_rate');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('is_taxable');
            
            // Alerts
            $table->boolean('low_stock_alert')->default(true)->after('minimum_stock_level');
            $table->boolean('expiry_alert')->default(false)->after('low_stock_alert');
            $table->integer('expiry_alert_days')->default(30)->after('expiry_alert'); // Alert before X days
            
            // Remove opening_stock as we now have opening_stocks table
            $table->dropColumn('opening_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('opening_stock')->default(0);
            
            $table->dropForeign(['sub_category_id']);
            $table->dropColumn([
                'sub_category_id',
                'track_batch',
                'track_imei',
                'has_variations',
                'manufacturer',
                'brand',
                'specifications',
                'warranty_period',
                'weight',
                'weight_unit',
                'dimensions_length',
                'dimensions_width',
                'dimensions_height',
                'dimensions_unit',
                'tax_rate',
                'is_taxable',
                'discount_percentage',
                'low_stock_alert',
                'expiry_alert',
                'expiry_alert_days'
            ]);
        });
    }
};

