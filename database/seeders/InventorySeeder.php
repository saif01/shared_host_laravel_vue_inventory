<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockLedger;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create admin user
        $admin = User::first();

        // 1. Create Units
        $units = [
            ['name' => 'Piece', 'code' => 'PCS'],
            ['name' => 'Kilogram', 'code' => 'KG'],
            ['name' => 'Box', 'code' => 'BOX'],
            ['name' => 'Carton', 'code' => 'CTN'],
            ['name' => 'Liter', 'code' => 'LTR'],
            ['name' => 'Meter', 'code' => 'MTR'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }

        // 2. Create Categories
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'order' => 1],
            ['name' => 'Clothing', 'slug' => 'clothing', 'order' => 2],
            ['name' => 'Food & Beverages', 'slug' => 'food-beverages', 'order' => 3],
            ['name' => 'Office Supplies', 'slug' => 'office-supplies', 'order' => 4],
            ['name' => 'Furniture', 'slug' => 'furniture', 'order' => 5],
            ['name' => 'Tools & Hardware', 'slug' => 'tools-hardware', 'order' => 6],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        // 3. Create Warehouses
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'code' => 'WH-001',
                'address' => '123 Industrial Street',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '10001',
                'phone' => '+1-555-0101',
                'email' => 'warehouse1@example.com',
                'manager_id' => $admin?->id,
            ],
            [
                'name' => 'Secondary Warehouse',
                'code' => 'WH-002',
                'address' => '456 Commerce Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'country' => 'USA',
                'postal_code' => '90001',
                'phone' => '+1-555-0102',
                'email' => 'warehouse2@example.com',
                'manager_id' => $admin?->id,
            ],
        ];

        $warehouseIds = [];
        foreach ($warehouses as $warehouse) {
            $w = Warehouse::updateOrCreate(
                ['code' => $warehouse['code']],
                $warehouse
            );
            $warehouseIds[] = $w->id;
        }

        // 4. Create Suppliers
        $suppliers = [
            [
                'name' => 'John Smith',
                'code' => 'SUP-001',
                'company_name' => 'Global Supplies Inc.',
                'email' => 'john@globalsupplies.com',
                'phone' => '+1-555-1001',
                'mobile' => '+1-555-2001',
                'address' => '789 Supplier Road',
                'city' => 'Chicago',
                'state' => 'IL',
                'country' => 'USA',
                'postal_code' => '60601',
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'name' => 'Sarah Johnson',
                'code' => 'SUP-002',
                'company_name' => 'Premium Products Ltd.',
                'email' => 'sarah@premiumproducts.com',
                'phone' => '+1-555-1002',
                'mobile' => '+1-555-2002',
                'address' => '321 Vendor Street',
                'city' => 'Houston',
                'state' => 'TX',
                'country' => 'USA',
                'postal_code' => '77001',
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
        ];

        $supplierIds = [];
        foreach ($suppliers as $supplier) {
            $s = Supplier::updateOrCreate(
                ['code' => $supplier['code']],
                $supplier
            );
            $supplierIds[] = $s->id;
        }

        // 5. Create Customers
        $customers = [
            [
                'name' => 'Michael Brown',
                'code' => 'CUS-001',
                'company_name' => 'Retail Solutions LLC',
                'email' => 'michael@retailsolutions.com',
                'phone' => '+1-555-3001',
                'mobile' => '+1-555-4001',
                'address' => '654 Customer Avenue',
                'city' => 'Miami',
                'state' => 'FL',
                'country' => 'USA',
                'postal_code' => '33101',
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
            [
                'name' => 'Emily Davis',
                'code' => 'CUS-002',
                'company_name' => 'Business Partners Inc.',
                'email' => 'emily@businesspartners.com',
                'phone' => '+1-555-3002',
                'mobile' => '+1-555-4002',
                'address' => '987 Client Boulevard',
                'city' => 'Seattle',
                'state' => 'WA',
                'country' => 'USA',
                'postal_code' => '98101',
                'opening_balance' => 0,
                'current_balance' => 0,
            ],
        ];

        $customerIds = [];
        foreach ($customers as $customer) {
            $c = Customer::updateOrCreate(
                ['code' => $customer['code']],
                $customer
            );
            $customerIds[] = $c->id;
        }

        // 6. Create Products (Amounts in Bangladeshi Taka ৳)
        // Note: We use category names to find IDs dynamically
        $electronicsCategory = Category::where('slug', 'electronics')->first();
        $clothingCategory = Category::where('slug', 'clothing')->first();
        $foodCategory = Category::where('slug', 'food-beverages')->first();
        $officeCategory = Category::where('slug', 'office-supplies')->first();
        $furnitureCategory = Category::where('slug', 'furniture')->first();

        $productsData = [
            [
                'name' => 'Laptop Computer',
                'sku' => 'LAP-001',
                'barcode' => '1234567890123',
                'category_id' => $electronicsCategory?->id,
                'sub_category_id' => null, // Will be set later if subcategories exist
                'unit_id' => 1, // PCS
                'description' => 'High-performance laptop computer',
                'cost_price' => 75000.00,
                'selling_price' => 95000.00,
                'minimum_stock_level' => 10,
                'track_serial' => true,
                'opening_stock' => 50,
            ],
            [
                'name' => 'Wireless Mouse',
                'sku' => 'MOU-001',
                'barcode' => '1234567890124',
                'category_id' => $electronicsCategory?->id,
                'sub_category_id' => null,
                'unit_id' => 1, // PCS
                'description' => 'Ergonomic wireless mouse',
                'cost_price' => 1200.00,
                'selling_price' => 1800.00,
                'minimum_stock_level' => 50,
                'track_serial' => false,
                'opening_stock' => 200,
            ],
            [
                'name' => 'Office Chair',
                'sku' => 'CHA-001',
                'barcode' => '1234567890125',
                'category_id' => $furnitureCategory?->id,
                'sub_category_id' => null,
                'unit_id' => 1, // PCS
                'description' => 'Comfortable office chair',
                'cost_price' => 12000.00,
                'selling_price' => 18000.00,
                'minimum_stock_level' => 5,
                'track_serial' => false,
                'opening_stock' => 30,
            ],
            [
                'name' => 'Printer Paper A4',
                'sku' => 'PAP-001',
                'barcode' => '1234567890126',
                'category_id' => $officeCategory?->id,
                'sub_category_id' => null,
                'unit_id' => 3, // BOX
                'description' => 'Premium A4 printer paper',
                'cost_price' => 1800.00,
                'selling_price' => 2500.00,
                'minimum_stock_level' => 20,
                'track_serial' => false,
                'opening_stock' => 100,
            ],
            [
                'name' => 'Coffee Beans',
                'sku' => 'COF-001',
                'barcode' => '1234567890127',
                'category_id' => $foodCategory?->id,
                'sub_category_id' => null,
                'unit_id' => 2, // KG
                'description' => 'Premium arabica coffee beans',
                'cost_price' => 2200.00,
                'selling_price' => 3500.00,
                'minimum_stock_level' => 10,
                'track_serial' => false,
                'opening_stock' => 50,
            ],
            [
                'name' => 'T-Shirt',
                'sku' => 'TSH-001',
                'barcode' => '1234567890128',
                'category_id' => $clothingCategory?->id,
                'sub_category_id' => null,
                'unit_id' => 1, // PCS
                'description' => 'Cotton t-shirt',
                'cost_price' => 800.00,
                'selling_price' => 1500.00,
                'minimum_stock_level' => 100,
                'track_serial' => false,
                'opening_stock' => 500,
            ],
        ];

        $productIds = [];
        foreach ($productsData as $productData) {
            $openingStock = $productData['opening_stock'];
            unset($productData['opening_stock']); // Remove opening_stock before creating product
            
            $p = Product::updateOrCreate(
                ['sku' => $productData['sku']],
                $productData
            );
            $productIds[] = $p->id;

            // Create opening stock for each warehouse
            foreach ($warehouseIds as $warehouseId) {
                $stock = Stock::updateOrCreate(
                    [
                    'product_id' => $p->id,
                    'warehouse_id' => $warehouseId,
                    ],
                    [
                        'quantity' => $openingStock,
                        'average_cost' => $productData['cost_price'],
                        'total_value' => $openingStock * $productData['cost_price'],
                    ]
                );

                // Create stock ledger entry for opening stock (only if it doesn't exist)
                $existingLedger = StockLedger::where('product_id', $p->id)
                    ->where('warehouse_id', $warehouseId)
                    ->where('reference_type', 'opening_stock')
                    ->where('reference_number', 'OPEN-' . $p->sku)
                    ->first();
                
                if (!$existingLedger) {
                StockLedger::create([
                    'product_id' => $p->id,
                    'warehouse_id' => $warehouseId,
                    'type' => 'in',
                    'reference_type' => 'opening_stock',
                    'reference_id' => null,
                    'reference_number' => 'OPEN-' . $p->sku,
                        'quantity' => $openingStock,
                        'unit_cost' => $productData['cost_price'],
                        'total_cost' => $openingStock * $productData['cost_price'],
                        'balance_after' => $openingStock,
                    'created_by' => $admin?->id,
                    'transaction_date' => now()->subDays(30),
                ]);
                }
            }
        }

        // 7. Create Purchase Request
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-' . strtoupper(Str::random(6)),
            'request_date' => now()->subDays(20),
            'warehouse_id' => $warehouseIds[0],
            'status' => 'approved',
            'notes' => 'Monthly restocking request',
            'requested_by' => $admin?->id,
            'approved_by' => $admin?->id,
            'approved_at' => now()->subDays(19),
        ]);

        // 8. Create Purchase Order (Amounts in BDT ৳)
        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . strtoupper(Str::random(6)),
            'purchase_request_id' => $pr->id,
            'supplier_id' => $supplierIds[0],
            'warehouse_id' => $warehouseIds[0],
            'order_date' => now()->subDays(18),
            'expected_delivery_date' => now()->subDays(10),
            'status' => 'completed',
            'subtotal' => 1620000.00,
            'tax_amount' => 40500.00,
            'discount_amount' => 5000.00,
            'total_amount' => 1655500.00,
            'notes' => 'Bulk order for restocking',
            'created_by' => $admin?->id,
        ]);

        // Create PO Items
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $productIds[0],
            'quantity' => 20,
            'unit_price' => 75000.00,
            'discount' => 0,
            'tax' => 37500.00,
            'total' => 1500000.00,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $productIds[1],
            'quantity' => 100,
            'unit_price' => 1200.00,
            'discount' => 3000.00,
            'tax' => 3000.00,
            'total' => 120000.00,
        ]);

        // 9. Create GRN
        $grn = Grn::create([
            'grn_number' => 'GRN-' . strtoupper(Str::random(6)),
            'purchase_order_id' => $po->id,
            'warehouse_id' => $warehouseIds[0],
            'grn_date' => now()->subDays(10),
            'status' => 'verified',
            'notes' => 'All items received in good condition',
            'received_by' => $admin?->id,
            'verified_by' => $admin?->id,
            'verified_at' => now()->subDays(10),
        ]);

        GrnItem::create([
            'grn_id' => $grn->id,
            'product_id' => $productIds[0],
            'ordered_quantity' => 20,
            'received_quantity' => 20,
            'unit_price' => 75000.00,
            'total' => 1500000.00,
        ]);

        GrnItem::create([
            'grn_id' => $grn->id,
            'product_id' => $productIds[1],
            'ordered_quantity' => 100,
            'received_quantity' => 100,
            'unit_price' => 1200.00,
            'total' => 120000.00,
        ]);

        // 10. Create Purchase (Invoice) (Amounts in BDT ৳)
        $purchase = Purchase::create([
            'invoice_number' => 'INV-P-' . strtoupper(Str::random(6)),
            'supplier_id' => $supplierIds[0],
            'warehouse_id' => $warehouseIds[0],
            'grn_id' => $grn->id,
            'invoice_date' => now()->subDays(10),
            'due_date' => now()->addDays(20),
            'status' => 'partial',
            'subtotal' => 1620000.00,
            'tax_amount' => 40500.00,
            'discount_amount' => 5000.00,
            'shipping_cost' => 0,
            'total_amount' => 1655500.00,
            'paid_amount' => 827750.00,
            'balance_amount' => 827750.00,
            'notes' => 'Payment terms: Net 30',
            'created_by' => $admin?->id,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $productIds[0],
            'quantity' => 20,
            'unit_price' => 75000.00,
            'discount' => 0,
            'tax' => 37500.00,
            'total' => 1500000.00,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $productIds[1],
            'quantity' => 100,
            'unit_price' => 1200.00,
            'discount' => 3000.00,
            'tax' => 3000.00,
            'total' => 120000.00,
        ]);

        // Update stock and ledger for purchase
        foreach ($grn->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $warehouseIds[0])
                ->first();
            
            if ($stock) {
                $oldQty = $stock->quantity;
                $newQty = $oldQty + $item->received_quantity;
                $oldValue = $stock->total_value;
                $newValue = $oldValue + ($item->received_quantity * $item->unit_price);
                $avgCost = $newValue / $newQty;

                $stock->update([
                    'quantity' => $newQty,
                    'average_cost' => $avgCost,
                    'total_value' => $newValue,
                ]);

                StockLedger::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $warehouseIds[0],
                    'type' => 'in',
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'reference_number' => $purchase->invoice_number,
                    'quantity' => $item->received_quantity,
                    'unit_cost' => $item->unit_price,
                    'total_cost' => $item->total,
                    'balance_after' => $newQty,
                    'created_by' => $admin?->id,
                    'transaction_date' => $purchase->invoice_date,
                ]);
            }
        }

        // 11. Create Sales Order (Amounts in BDT ৳)
        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-' . strtoupper(Str::random(6)),
            'customer_id' => $customerIds[0],
            'warehouse_id' => $warehouseIds[0],
            'order_date' => now()->subDays(5),
            'delivery_date' => now()->subDays(2),
            'status' => 'completed',
            'subtotal' => 113000.00,
            'tax_amount' => 5650.00,
            'discount_amount' => 2000.00,
            'total_amount' => 116650.00,
            'notes' => 'Regular customer order',
            'created_by' => $admin?->id,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $productIds[0],
            'quantity' => 1,
            'unit_price' => 95000.00,
            'discount' => 0,
            'tax' => 4750.00,
            'total' => 95000.00,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $productIds[1],
            'quantity' => 10,
            'unit_price' => 1800.00,
            'discount' => 2000.00,
            'tax' => 900.00,
            'total' => 18000.00,
        ]);

        // 12. Create Sale (Invoice) (Amounts in BDT ৳)
        $sale = Sale::create([
            'invoice_number' => 'INV-S-' . strtoupper(Str::random(6)),
            'customer_id' => $customerIds[0],
            'warehouse_id' => $warehouseIds[0],
            'sales_order_id' => $salesOrder->id,
            'invoice_date' => now()->subDays(2),
            'due_date' => now()->addDays(28),
            'status' => 'pending',
            'subtotal' => 113000.00,
            'tax_amount' => 5650.00,
            'discount_amount' => 2000.00,
            'shipping_cost' => 0,
            'total_amount' => 116650.00,
            'paid_amount' => 0,
            'balance_amount' => 116650.00,
            'notes' => 'Payment pending',
            'created_by' => $admin?->id,
        ]);

        SalesItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productIds[0],
            'quantity' => 1,
            'unit_price' => 95000.00,
            'discount' => 0,
            'tax' => 4750.00,
            'total' => 95000.00,
        ]);

        SalesItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productIds[1],
            'quantity' => 10,
            'unit_price' => 1800.00,
            'discount' => 2000.00,
            'tax' => 900.00,
            'total' => 18000.00,
        ]);

        // Update stock and ledger for sales
        foreach ($sale->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('warehouse_id', $warehouseIds[0])
                ->first();
            
            if ($stock && $stock->quantity >= $item->quantity) {
                $oldQty = $stock->quantity;
                $newQty = $oldQty - $item->quantity;
                $unitCost = $stock->average_cost;
                $totalCost = $item->quantity * $unitCost;
                $newValue = $stock->total_value - $totalCost;

                $stock->update([
                    'quantity' => $newQty,
                    'total_value' => $newValue,
                ]);

                StockLedger::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $warehouseIds[0],
                    'type' => 'out',
                    'reference_type' => 'sales',
                    'reference_id' => $sale->id,
                    'reference_number' => $sale->invoice_number,
                    'quantity' => $item->quantity,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'balance_after' => $newQty,
                    'created_by' => $admin?->id,
                    'transaction_date' => $sale->invoice_date,
                ]);
            }
        }

        $this->command->info('Inventory demo data seeded successfully!');
    }
}
