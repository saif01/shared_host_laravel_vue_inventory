# Migration Guide - Inventory Management System

## 📋 Prerequisites

Before running migrations, ensure:
1. Database backup is taken
2. Laravel environment is properly configured
3. Database connection is working
4. All dependencies are installed (`composer install`)

---

## 🔄 Migration Steps

### Step 1: Check Current Database Status
```bash
php artisan migrate:status
```

### Step 2: Run New Migrations
```bash
php artisan migrate
```

This will:
- Add parent_id to categories table
- Create 13 new tables
- Update products table with enhanced fields
- Update stock_ledgers table for batch tracking

### Step 3: Verify Migrations
```bash
php artisan migrate:status
```

All migrations should show "Ran" status.

---

## 🌱 Seeding Data

### Option 1: Seed All Data (Recommended for Fresh Install)
```bash
php artisan db:seed
```

This will seed:
1. Roles & Permissions
2. Admin user
3. Demo data
4. Inventory data (categories, units, warehouses, products, etc.)
5. Extended inventory data (bin locations, batches, serials, etc.)

### Option 2: Seed Specific Seeders
```bash
# Seed only roles and permissions
php artisan db:seed --class=RolePermissionSeeder

# Seed only extended inventory features
php artisan db:seed --class=ExtendedInventorySeeder
```

### Option 3: Fresh Migration with Seed (⚠️ Will Delete All Data)
```bash
php artisan migrate:fresh --seed
```

---

## 🔍 Verification Checklist

After migration, verify the following:

### 1. Tables Created
```sql
SHOW TABLES LIKE '%product_%';
SHOW TABLES LIKE '%warehouse%';
SHOW TABLES LIKE '%audit%';
SHOW TABLES LIKE '%payment%';
```

Expected new tables:
- product_images
- product_barcodes
- product_batches
- product_serials
- product_variations
- product_warehouse_settings
- product_bin_locations
- warehouse_users
- bin_locations
- opening_stocks
- unit_conversions
- audit_logs
- payments

### 2. Categories Table Updated
```sql
DESC categories;
```
Should show `parent_id` and `order` columns.

### 3. Products Table Updated
```sql
DESC products;
```
Should show new columns:
- sub_category_id
- track_batch
- track_imei
- has_variations
- manufacturer
- brand
- specifications
- warranty_period
- weight, dimensions
- tax_rate, discount_percentage
- expiry_alert fields

### 4. Stock Ledgers Table Updated
```sql
DESC stock_ledgers;
```
Should show:
- batch_id
- serial_id
- weighted_avg_cost
- cost_method
- value_before, value_after

---

## 🗂️ Data Migration (For Existing Data)

If you have existing data, you may need to:

### 1. Migrate Existing Product Opening Stocks
Since `opening_stock` column is removed from products:

```php
// Run this in tinker or create a migration
php artisan tinker

$products = \App\Models\Product::all();
$warehouses = \App\Models\Warehouse::all();

foreach ($products as $product) {
    foreach ($warehouses as $warehouse) {
        // If you had opening_stock data, migrate it
        \App\Models\OpeningStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0, // Set actual opening stock
            'unit_cost' => $product->cost_price,
            'total_cost' => 0,
            'opening_date' => now(),
            'created_by' => 1,
        ]);
    }
}
```

### 2. Migrate Existing Barcodes
```php
$products = \App\Models\Product::whereNotNull('barcode')->get();

foreach ($products as $product) {
    \App\Models\ProductBarcode::create([
        'product_id' => $product->id,
        'barcode' => $product->barcode,
        'barcode_type' => 'CODE128',
        'is_primary' => true,
    ]);
}
```

### 3. Set Product-Warehouse Settings
```php
$products = \App\Models\Product::all();
$warehouses = \App\Models\Warehouse::all();

foreach ($products as $product) {
    foreach ($warehouses as $warehouse) {
        \App\Models\ProductWarehouseSetting::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'minimum_stock_level' => $product->minimum_stock_level,
            'maximum_stock_level' => $product->minimum_stock_level * 10,
            'reorder_level' => $product->minimum_stock_level + 5,
            'reorder_quantity' => $product->minimum_stock_level * 2,
            'is_available' => true,
        ]);
    }
}
```

---

## 🚨 Common Issues & Solutions

### Issue 1: Foreign Key Constraint Error
**Error**: `SQLSTATE[23000]: Integrity constraint violation`

**Solution**: 
- Ensure parent tables are populated first
- Check that referenced IDs exist
- Run migrations in order

### Issue 2: Column Already Exists
**Error**: `Column 'column_name' already exists`

**Solution**:
```bash
# Rollback the specific migration
php artisan migrate:rollback --step=1

# Re-run migrations
php artisan migrate
```

### Issue 3: Class Not Found in Seeder
**Error**: `Class "App\Models\ModelName" not found`

**Solution**:
- Ensure all model files are created
- Run `composer dump-autoload`
- Check namespace in model files

### Issue 4: Memory Limit Exceeded
**Error**: `Allowed memory size exhausted`

**Solution**:
```bash
php -d memory_limit=512M artisan db:seed
```

---

## 🧪 Testing After Migration

### 1. Test Product Creation
```php
php artisan tinker

$product = \App\Models\Product::create([
    'name' => 'Test Product',
    'sku' => 'TEST-001',
    'category_id' => 1,
    'unit_id' => 1,
    'cost_price' => 100,
    'selling_price' => 150,
    'minimum_stock_level' => 10,
]);

// Add barcode
\App\Models\ProductBarcode::create([
    'product_id' => $product->id,
    'barcode' => '1234567890',
    'barcode_type' => 'CODE128',
    'is_primary' => true,
]);

// Add image
\App\Models\ProductImage::create([
    'product_id' => $product->id,
    'image_path' => 'products/test.jpg',
    'is_primary' => true,
]);
```

### 2. Test Batch Creation
```php
$batch = \App\Models\ProductBatch::create([
    'product_id' => $product->id,
    'warehouse_id' => 1,
    'batch_number' => 'BATCH-001',
    'manufacturing_date' => now(),
    'expiry_date' => now()->addYear(),
    'quantity' => 100,
    'available_quantity' => 100,
    'unit_cost' => 100,
    'selling_price' => 150,
    'status' => 'active',
]);
```

### 3. Test Serial Creation
```php
$serial = \App\Models\ProductSerial::create([
    'product_id' => $product->id,
    'warehouse_id' => 1,
    'serial_number' => 'SN-001',
    'status' => 'in_stock',
]);
```

### 4. Test Warehouse User Assignment
```php
\App\Models\WarehouseUser::create([
    'warehouse_id' => 1,
    'user_id' => 1,
    'is_manager' => true,
    'can_view' => true,
    'can_add' => true,
    'can_edit' => true,
    'can_delete' => true,
    'can_approve' => true,
]);
```

---

## 📊 Performance Optimization

After migration, optimize for performance:

### 1. Add Indexes (Already included in migrations)
All necessary indexes are already added, but verify:
```sql
SHOW INDEXES FROM products;
SHOW INDEXES FROM stock_ledgers;
SHOW INDEXES FROM product_batches;
```

### 2. Optimize Tables
```bash
php artisan optimize
```

### 3. Cache Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔐 Security Checklist

After migration:

1. ✅ Verify user permissions are correctly set
2. ✅ Check warehouse user assignments
3. ✅ Test role-based access control
4. ✅ Enable audit logging on critical models
5. ✅ Review payment access permissions

---

## 📞 Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode temporarily: `APP_DEBUG=true`
3. Check database connection: `php artisan tinker` then `DB::connection()->getPdo()`

---

## ✅ Post-Migration Checklist

- [ ] All migrations ran successfully
- [ ] Seeders completed without errors
- [ ] Can create products with variations
- [ ] Can assign barcodes to products
- [ ] Can create batches for perishable items
- [ ] Can create serials for electronics
- [ ] Can assign users to warehouses
- [ ] Can create bin locations
- [ ] Audit logs are working
- [ ] Payment records can be created
- [ ] Roles and permissions are properly set
- [ ] Opening stocks are created for all products

---

## 🎉 Success!

Once all steps are completed and verified, your inventory management system is fully upgraded to enterprise-level specifications as per the SRS document!

---

**Last Updated**: December 4, 2025
**Version**: 2.0.0

