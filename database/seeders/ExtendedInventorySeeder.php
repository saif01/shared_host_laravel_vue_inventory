<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductBarcode;
use App\Models\ProductWarehouseSetting;
use App\Models\ProductVariation;
use App\Models\OpeningStock;
use App\Models\ProductBatch;
use App\Models\ProductSerial;
use App\Models\WarehouseUser;
use App\Models\BinLocation;
use App\Models\ProductBinLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ExtendedInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        
        // 1. Update products with subcategories (if they exist)
        $this->updateProductsWithSubCategories();
        
        // 2. Create Sub-categories (kept for backward compatibility, but should use SubCategorySeeder)
        $electronics = Category::where('slug', 'electronics')->first();
        if ($electronics) {
            SubCategory::updateOrCreate(
                ['slug' => 'laptops'],
                [
                    'name' => 'Laptops',
                    'category_id' => $electronics->id,
                    'order' => 1,
                    'description' => 'Laptop computers and accessories',
                ]
            );
            
            SubCategory::updateOrCreate(
                ['slug' => 'accessories'],
                [
                    'name' => 'Accessories',
                    'category_id' => $electronics->id,
                    'order' => 2,
                    'description' => 'Computer accessories',
                ]
            );
        }
        
        $clothing = Category::where('slug', 'clothing')->first();
        if ($clothing) {
            SubCategory::updateOrCreate(
                ['slug' => 'mens-clothing'],
                [
                    'name' => 'Men\'s Clothing',
                    'category_id' => $clothing->id,
                    'order' => 1,
                ]
            );
            
            SubCategory::updateOrCreate(
                ['slug' => 'womens-clothing'],
                [
                    'name' => 'Women\'s Clothing',
                    'category_id' => $clothing->id,
                    'order' => 2,
                ]
            );
        }

        // 2. Create Unit Conversions
        $pcs = Unit::where('code', 'PCS')->first();
        $box = Unit::where('code', 'BOX')->first();
        $carton = Unit::where('code', 'CTN')->first();

        if ($pcs && $box) {
            UnitConversion::updateOrCreate(
                [
                    'from_unit_id' => $box->id,
                    'to_unit_id' => $pcs->id,
                ],
                [
                    'conversion_factor' => 12,
                    'operation' => 'multiply',
                    'description' => '1 Box = 12 Pieces',
                ]
            );
        }

        if ($box && $carton) {
            UnitConversion::updateOrCreate(
                [
                    'from_unit_id' => $carton->id,
                    'to_unit_id' => $box->id,
                ],
                [
                    'conversion_factor' => 6,
                    'operation' => 'multiply',
                    'description' => '1 Carton = 6 Boxes',
                ]
            );
        }

        if ($pcs && $carton) {
            UnitConversion::updateOrCreate(
                [
                    'from_unit_id' => $carton->id,
                    'to_unit_id' => $pcs->id,
                ],
                [
                    'conversion_factor' => 72,
                    'operation' => 'multiply',
                    'description' => '1 Carton = 72 Pieces (6 boxes × 12 pieces)',
                ]
            );
        }

        // 3. Create Warehouse Users
        $warehouses = Warehouse::all();
        if ($admin && $warehouses->count() > 0) {
            foreach ($warehouses as $warehouse) {
                WarehouseUser::updateOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'user_id' => $admin->id,
                    ],
                    [
                        'is_manager' => true,
                        'can_view' => true,
                        'can_add' => true,
                        'can_edit' => true,
                        'can_delete' => true,
                        'can_approve' => true,
                        'assigned_date' => now(),
                    ]
                );
            }
        }

        // 4. Create Bin Locations
        foreach ($warehouses as $warehouse) {
            // Create various bin locations
            $binTypes = [
                ['name' => 'A-1-01', 'aisle' => 'A', 'rack' => '1', 'shelf' => '01', 'type' => 'storage'],
                ['name' => 'A-1-02', 'aisle' => 'A', 'rack' => '1', 'shelf' => '02', 'type' => 'storage'],
                ['name' => 'A-2-01', 'aisle' => 'A', 'rack' => '2', 'shelf' => '01', 'type' => 'storage'],
                ['name' => 'B-1-01', 'aisle' => 'B', 'rack' => '1', 'shelf' => '01', 'type' => 'storage'],
                ['name' => 'RECV-01', 'type' => 'receiving', 'description' => 'Receiving area 1'],
                ['name' => 'RECV-02', 'type' => 'receiving', 'description' => 'Receiving area 2'],
                ['name' => 'DISP-01', 'type' => 'dispatch', 'description' => 'Dispatch area 1'],
                ['name' => 'QUAR-01', 'type' => 'quarantine', 'description' => 'Quarantine zone'],
                ['name' => 'DMGD-01', 'type' => 'damaged', 'description' => 'Damaged goods area'],
            ];

            foreach ($binTypes as $binData) {
                BinLocation::updateOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'name' => $binData['name'],
                    ],
                    array_merge([
                        'capacity' => 100,
                        'capacity_unit' => 'm3',
                    ], $binData)
                );
            }
        }

        // 5. Add Product Images, Barcodes, and Enhanced Data
        $products = Product::all();
        
        foreach ($products as $product) {
            // Add product barcodes (including the existing one)
            if ($product->barcode) {
                ProductBarcode::create([
                    'product_id' => $product->id,
                    'barcode' => $product->barcode,
                    'barcode_type' => 'EAN13',
                    'is_primary' => true,
                ]);
            }
            
            // Add additional barcode
            ProductBarcode::create([
                'product_id' => $product->id,
                'barcode' => 'QR-' . $product->sku,
                'barcode_type' => 'QR',
                'is_primary' => false,
            ]);

            // Add warehouse-specific settings
            foreach ($warehouses as $warehouse) {
                ProductWarehouseSetting::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'minimum_stock_level' => $product->minimum_stock_level,
                    'maximum_stock_level' => $product->minimum_stock_level * 10,
                    'reorder_level' => $product->minimum_stock_level + 5,
                    'reorder_quantity' => $product->minimum_stock_level * 2,
                    'is_available' => true,
                ]);
            }

            // Add to bin locations
            $firstWarehouse = $warehouses->first();
            $storageBin = BinLocation::where('warehouse_id', $firstWarehouse->id)
                ->where('type', 'storage')
                ->first();
            
            if ($storageBin) {
                ProductBinLocation::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $firstWarehouse->id,
                    'bin_location_id' => $storageBin->id,
                    'quantity' => 100,
                    'is_primary' => true,
                ]);
            }
        }

        // 6. Create Product Variations (for T-Shirt)
        $tshirt = Product::where('sku', 'TSH-001')->first();
        if ($tshirt) {
            $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
            $colors = ['Red', 'Blue', 'Black', 'White'];
            
            foreach ($sizes as $size) {
                ProductVariation::create([
                    'product_id' => $tshirt->id,
                    'variation_type' => 'Size',
                    'variation_value' => $size,
                    'sku' => $tshirt->sku . '-' . $size,
                    'additional_cost' => 0,
                    'additional_price' => 0,
                    'stock_quantity' => 50,
                    'is_available' => true,
                ]);
            }
            
            foreach ($colors as $color) {
                ProductVariation::create([
                    'product_id' => $tshirt->id,
                    'variation_type' => 'Color',
                    'variation_value' => $color,
                    'sku' => $tshirt->sku . '-' . strtoupper(substr($color, 0, 3)),
                    'additional_cost' => 0,
                    'additional_price' => 0,
                    'stock_quantity' => 50,
                    'is_available' => true,
                ]);
            }
        }

        // 7. Create Product Batches (for Coffee Beans - perishable item)
        $coffee = Product::where('sku', 'COF-001')->first();
        if ($coffee && $warehouses->count() > 0) {
            $warehouse = $warehouses->first();
            
            ProductBatch::create([
                'product_id' => $coffee->id,
                'warehouse_id' => $warehouse->id,
                'batch_number' => 'BATCH-COF-001',
                'manufacturing_date' => now()->subMonths(1),
                'expiry_date' => now()->addMonths(11),
                'quantity' => 50,
                'available_quantity' => 50,
                'unit_cost' => 2200.00,
                'selling_price' => 3500.00,
                'status' => 'active',
            ]);
            
            ProductBatch::create([
                'product_id' => $coffee->id,
                'warehouse_id' => $warehouse->id,
                'batch_number' => 'BATCH-COF-002',
                'manufacturing_date' => now()->subWeeks(2),
                'expiry_date' => now()->addYear(),
                'quantity' => 30,
                'available_quantity' => 30,
                'unit_cost' => 2200.00,
                'selling_price' => 3500.00,
                'status' => 'active',
            ]);
        }

        // 8. Create Product Serials (for Laptop - serialized item)
        $laptop = Product::where('sku', 'LAP-001')->first();
        if ($laptop && $warehouses->count() > 0) {
            $warehouse = $warehouses->first();
            
            // Create some in-stock serials
            for ($i = 1; $i <= 10; $i++) {
                ProductSerial::create([
                    'product_id' => $laptop->id,
                    'warehouse_id' => $warehouse->id,
                    'serial_number' => 'SN-LAP-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'imei_number' => null,
                    'status' => 'in_stock',
                ]);
            }
        }

        // 9. Create Opening Stocks (warehouse-wise)
        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                OpeningStock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => 50,
                    'unit_cost' => $product->cost_price,
                    'total_cost' => 50 * $product->cost_price,
                    'opening_date' => Carbon::now()->subMonth(),
                    'notes' => 'Initial stock for ' . $warehouse->name,
                    'created_by' => $admin?->id,
                ]);
            }
        }

        $this->command->info('Extended inventory data seeded successfully!');
    }

    /**
     * Update products with subcategories if they exist
     */
    private function updateProductsWithSubCategories(): void
    {
        // Update Laptop with Laptops subcategory
        $laptopsSubCat = \App\Models\SubCategory::where('slug', 'laptops')->first();
        if ($laptopsSubCat) {
            Product::where('sku', 'LAP-001')->update(['sub_category_id' => $laptopsSubCat->id]);
        }

        // Update Mouse with Accessories subcategory
        $accessoriesSubCat = \App\Models\SubCategory::where('slug', 'accessories')->first();
        if ($accessoriesSubCat) {
            Product::where('sku', 'MOU-001')->update(['sub_category_id' => $accessoriesSubCat->id]);
        }

        // Update T-Shirt with Men's Clothing subcategory
        $mensClothingSubCat = \App\Models\SubCategory::where('slug', 'mens-clothing')->first();
        if ($mensClothingSubCat) {
            Product::where('sku', 'TSH-001')->update(['sub_category_id' => $mensClothingSubCat->id]);
        }

        // Update Office Chair with Office Furniture subcategory
        $officeFurnitureSubCat = \App\Models\SubCategory::where('slug', 'office-furniture')->first();
        if ($officeFurnitureSubCat) {
            Product::where('sku', 'CHA-001')->update(['sub_category_id' => $officeFurnitureSubCat->id]);
        }

        // Update Paper with Paper Products subcategory
        $paperProductsSubCat = \App\Models\SubCategory::where('slug', 'paper-products')->first();
        if ($paperProductsSubCat) {
            Product::where('sku', 'PAP-001')->update(['sub_category_id' => $paperProductsSubCat->id]);
        }

        // Update Coffee with Beverages subcategory
        $beveragesSubCat = \App\Models\SubCategory::where('slug', 'beverages')->first();
        if ($beveragesSubCat) {
            Product::where('sku', 'COF-001')->update(['sub_category_id' => $beveragesSubCat->id]);
        }
    }
}

