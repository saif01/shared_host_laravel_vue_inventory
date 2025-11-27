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
            Unit::create($unit);
        }

        // 2. Create Categories
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Clothing', 'slug' => 'clothing'],
            ['name' => 'Food & Beverages', 'slug' => 'food-beverages'],
            ['name' => 'Office Supplies', 'slug' => 'office-supplies'],
            ['name' => 'Furniture', 'slug' => 'furniture'],
            ['name' => 'Tools & Hardware', 'slug' => 'tools-hardware'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
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
            $w = Warehouse::create($warehouse);
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
            $s = Supplier::create($supplier);
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
            $c = Customer::create($customer);
            $customerIds[] = $c->id;
        }

        // 6. Create Products
        $products = [
            [
                'name' => 'Laptop Computer',
                'sku' => 'LAP-001',
                'barcode' => '1234567890123',
                'category_id' => 1, // Electronics
                'unit_id' => 1, // PCS
                'description' => 'High-performance laptop computer',
                'cost_price' => 800.00,
                'selling_price' => 1200.00,
                'minimum_stock_level' => 10,
                'opening_stock' => 50,
                'track_serial' => true,
            ],
            [
                'name' => 'Wireless Mouse',
                'sku' => 'MOU-001',
                'barcode' => '1234567890124',
                'category_id' => 1, // Electronics
                'unit_id' => 1, // PCS
                'description' => 'Ergonomic wireless mouse',
                'cost_price' => 15.00,
                'selling_price' => 25.00,
                'minimum_stock_level' => 50,
                'opening_stock' => 200,
                'track_serial' => false,
            ],
            [
                'name' => 'Office Chair',
                'sku' => 'CHA-001',
                'barcode' => '1234567890125',
                'category_id' => 5, // Furniture
                'unit_id' => 1, // PCS
                'description' => 'Comfortable office chair',
                'cost_price' => 150.00,
                'selling_price' => 250.00,
                'minimum_stock_level' => 5,
                'opening_stock' => 30,
                'track_serial' => false,
            ],
            [
                'name' => 'Printer Paper A4',
                'sku' => 'PAP-001',
                'barcode' => '1234567890126',
                'category_id' => 4, // Office Supplies
                'unit_id' => 3, // BOX
                'description' => 'Premium A4 printer paper',
                'cost_price' => 20.00,
                'selling_price' => 35.00,
                'minimum_stock_level' => 20,
                'opening_stock' => 100,
                'track_serial' => false,
            ],
            [
                'name' => 'Coffee Beans',
                'sku' => 'COF-001',
                'barcode' => '1234567890127',
                'category_id' => 3, // Food & Beverages
                'unit_id' => 2, // KG
                'description' => 'Premium arabica coffee beans',
                'cost_price' => 25.00,
                'selling_price' => 45.00,
                'minimum_stock_level' => 10,
                'opening_stock' => 50,
                'track_serial' => false,
            ],
            [
                'name' => 'T-Shirt',
                'sku' => 'TSH-001',
                'barcode' => '1234567890128',
                'category_id' => 2, // Clothing
                'unit_id' => 1, // PCS
                'description' => 'Cotton t-shirt',
                'cost_price' => 10.00,
                'selling_price' => 20.00,
                'minimum_stock_level' => 100,
                'opening_stock' => 500,
                'track_serial' => false,
            ],
        ];

        $productIds = [];
        foreach ($products as $product) {
            $p = Product::create($product);
            $productIds[] = $p->id;

            // Create opening stock for each warehouse
            foreach ($warehouseIds as $warehouseId) {
                $stock = Stock::create([
                    'product_id' => $p->id,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $product['opening_stock'],
                    'average_cost' => $product['cost_price'],
                    'total_value' => $product['opening_stock'] * $product['cost_price'],
                ]);

                // Create stock ledger entry for opening stock
                StockLedger::create([
                    'product_id' => $p->id,
                    'warehouse_id' => $warehouseId,
                    'type' => 'in',
                    'reference_type' => 'opening_stock',
                    'reference_id' => null,
                    'reference_number' => 'OPEN-' . $p->sku,
                    'quantity' => $product['opening_stock'],
                    'unit_cost' => $product['cost_price'],
                    'total_cost' => $product['opening_stock'] * $product['cost_price'],
                    'balance_after' => $product['opening_stock'],
                    'created_by' => $admin?->id,
                    'transaction_date' => now()->subDays(30),
                ]);
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

        // 8. Create Purchase Order
        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . strtoupper(Str::random(6)),
            'purchase_request_id' => $pr->id,
            'supplier_id' => $supplierIds[0],
            'warehouse_id' => $warehouseIds[0],
            'order_date' => now()->subDays(18),
            'expected_delivery_date' => now()->subDays(10),
            'status' => 'completed',
            'subtotal' => 5000.00,
            'tax_amount' => 500.00,
            'discount_amount' => 100.00,
            'total_amount' => 5400.00,
            'notes' => 'Bulk order for restocking',
            'created_by' => $admin?->id,
        ]);

        // Create PO Items
        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $productIds[0],
            'quantity' => 20,
            'unit_price' => 800.00,
            'discount' => 0,
            'tax' => 160.00,
            'total' => 16000.00,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $productIds[1],
            'quantity' => 100,
            'unit_price' => 15.00,
            'discount' => 50.00,
            'tax' => 145.00,
            'total' => 1500.00,
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
            'unit_price' => 800.00,
            'total' => 16000.00,
        ]);

        GrnItem::create([
            'grn_id' => $grn->id,
            'product_id' => $productIds[1],
            'ordered_quantity' => 100,
            'received_quantity' => 100,
            'unit_price' => 15.00,
            'total' => 1500.00,
        ]);

        // 10. Create Purchase (Invoice)
        $purchase = Purchase::create([
            'invoice_number' => 'INV-P-' . strtoupper(Str::random(6)),
            'supplier_id' => $supplierIds[0],
            'warehouse_id' => $warehouseIds[0],
            'grn_id' => $grn->id,
            'invoice_date' => now()->subDays(10),
            'due_date' => now()->addDays(20),
            'status' => 'partial',
            'subtotal' => 5000.00,
            'tax_amount' => 500.00,
            'discount_amount' => 100.00,
            'shipping_cost' => 0,
            'total_amount' => 5400.00,
            'paid_amount' => 2700.00,
            'balance_amount' => 2700.00,
            'notes' => 'Payment terms: Net 30',
            'created_by' => $admin?->id,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $productIds[0],
            'quantity' => 20,
            'unit_price' => 800.00,
            'discount' => 0,
            'tax' => 160.00,
            'total' => 16000.00,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $productIds[1],
            'quantity' => 100,
            'unit_price' => 15.00,
            'discount' => 50.00,
            'tax' => 145.00,
            'total' => 1500.00,
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

        // 11. Create Sales Order
        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-' . strtoupper(Str::random(6)),
            'customer_id' => $customerIds[0],
            'warehouse_id' => $warehouseIds[0],
            'order_date' => now()->subDays(5),
            'delivery_date' => now()->subDays(2),
            'status' => 'completed',
            'subtotal' => 1500.00,
            'tax_amount' => 150.00,
            'discount_amount' => 50.00,
            'total_amount' => 1600.00,
            'notes' => 'Regular customer order',
            'created_by' => $admin?->id,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $productIds[0],
            'quantity' => 1,
            'unit_price' => 1200.00,
            'discount' => 0,
            'tax' => 120.00,
            'total' => 1200.00,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'product_id' => $productIds[1],
            'quantity' => 10,
            'unit_price' => 25.00,
            'discount' => 50.00,
            'tax' => 30.00,
            'total' => 250.00,
        ]);

        // 12. Create Sale (Invoice)
        $sale = Sale::create([
            'invoice_number' => 'INV-S-' . strtoupper(Str::random(6)),
            'customer_id' => $customerIds[0],
            'warehouse_id' => $warehouseIds[0],
            'sales_order_id' => $salesOrder->id,
            'invoice_date' => now()->subDays(2),
            'due_date' => now()->addDays(28),
            'status' => 'pending',
            'subtotal' => 1500.00,
            'tax_amount' => 150.00,
            'discount_amount' => 50.00,
            'shipping_cost' => 0,
            'total_amount' => 1600.00,
            'paid_amount' => 0,
            'balance_amount' => 1600.00,
            'notes' => 'Payment pending',
            'created_by' => $admin?->id,
        ]);

        SalesItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productIds[0],
            'quantity' => 1,
            'unit_price' => 1200.00,
            'discount' => 0,
            'tax' => 120.00,
            'total' => 1200.00,
        ]);

        SalesItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productIds[1],
            'quantity' => 10,
            'unit_price' => 25.00,
            'discount' => 50.00,
            'tax' => 30.00,
            'total' => 250.00,
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
