# Database Schema - Inventory Management System

## 📊 Complete Database Structure

---

## Core Tables

### 1. users
Primary authentication and user management table.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | User full name |
| email | varchar | Unique email |
| phone | varchar | Contact number |
| avatar | varchar | Profile image |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 2. roles
User role definitions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Role name |
| slug | varchar | Unique slug |
| description | text | Role description |
| is_system | boolean | System role flag |
| is_active | boolean | Active status |
| order | int | Display order |

---

### 3. permissions
System permission definitions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Permission name |
| slug | varchar | Unique slug |
| group | varchar | Permission group |
| description | text | Description |

---

### 4. role_permission
Pivot table linking roles and permissions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| role_id | bigint | FK to roles |
| permission_id | bigint | FK to permissions |

---

### 5. user_role
Pivot table linking users and roles.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users |
| role_id | bigint | FK to roles |

---

## Product Management

### 6. categories
Product categories with hierarchical support.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| parent_id | bigint | FK to categories (self) |
| name | varchar | Category name |
| slug | varchar | Unique slug |
| order | int | Display order |
| description | text | Description |
| image | varchar | Category image |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 7. units
Product measurement units.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Unit name (Piece, Box, etc.) |
| code | varchar | Unique code (PCS, BOX) |
| description | text | Description |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 8. unit_conversions
Unit conversion rules (1 Box = 12 Pieces).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| from_unit_id | bigint | FK to units |
| to_unit_id | bigint | FK to units |
| conversion_factor | decimal(15,4) | Conversion multiplier |
| operation | varchar | multiply/divide |
| description | text | Human-readable description |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 9. products
Main product table.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Product name |
| sku | varchar | Unique SKU |
| barcode | varchar | Unique barcode (legacy) |
| category_id | bigint | FK to categories |
| sub_category_id | bigint | FK to categories |
| unit_id | bigint | FK to units |
| description | text | Product description |
| manufacturer | varchar | Manufacturer name |
| brand | varchar | Brand name |
| specifications | text | Technical specs |
| warranty_period | int | Warranty in months |
| weight | decimal(10,2) | Product weight |
| weight_unit | varchar | kg, g, lb |
| dimensions_length | decimal(10,2) | Length |
| dimensions_width | decimal(10,2) | Width |
| dimensions_height | decimal(10,2) | Height |
| dimensions_unit | varchar | cm, m, inch |
| image | varchar | Primary image |
| cost_price | decimal(15,2) | Purchase cost |
| selling_price | decimal(15,2) | Sale price |
| tax_rate | decimal(5,2) | Tax percentage |
| is_taxable | boolean | Taxable flag |
| discount_percentage | decimal(5,2) | Discount % |
| minimum_stock_level | int | Global min stock |
| low_stock_alert | boolean | Enable alerts |
| expiry_alert | boolean | Enable expiry alerts |
| expiry_alert_days | int | Alert X days before |
| track_serial | boolean | Track serial numbers |
| track_batch | boolean | Track batches |
| track_imei | boolean | Track IMEI |
| has_variations | boolean | Has variations |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 10. product_images
Product gallery images.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| image_path | varchar | Image path |
| image_name | varchar | Image filename |
| order | int | Display order |
| is_primary | boolean | Primary image flag |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 11. product_barcodes
Multiple barcodes per product.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| barcode | varchar | Unique barcode |
| barcode_type | enum | EAN13, UPC, CODE128, QR, CUSTOM |
| is_primary | boolean | Primary barcode flag |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 12. product_variations
Product variations (Size, Color, etc.).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| variation_type | varchar | Size, Color, Material |
| variation_value | varchar | Large, Red, Cotton |
| sku | varchar | Unique SKU for variation |
| barcode | varchar | Variation barcode |
| additional_cost | decimal(15,2) | Extra cost |
| additional_price | decimal(15,2) | Extra price |
| stock_quantity | int | Stock for this variation |
| is_available | boolean | Available flag |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Warehouse Management

### 13. warehouses
Warehouse/store locations.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Warehouse name |
| code | varchar | Unique code |
| address | text | Address |
| city | varchar | City |
| state | varchar | State |
| country | varchar | Country |
| postal_code | varchar | Postal code |
| phone | varchar | Phone |
| email | varchar | Email |
| manager_id | bigint | FK to users |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 14. warehouse_users
User-warehouse assignments with permissions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| warehouse_id | bigint | FK to warehouses |
| user_id | bigint | FK to users |
| is_manager | boolean | Manager flag |
| can_view | boolean | View permission |
| can_add | boolean | Add permission |
| can_edit | boolean | Edit permission |
| can_delete | boolean | Delete permission |
| can_approve | boolean | Approve permission |
| assigned_date | date | Assignment date |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 15. bin_locations
Bin locations within warehouses.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| warehouse_id | bigint | FK to warehouses |
| name | varchar | Location code (A-1-01) |
| aisle | varchar | Aisle identifier |
| rack | varchar | Rack identifier |
| shelf | varchar | Shelf identifier |
| bin | varchar | Bin identifier |
| type | enum | storage, receiving, dispatch, quarantine, damaged |
| capacity | decimal(15,2) | Storage capacity |
| capacity_unit | varchar | m3, kg |
| description | text | Description |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 16. product_bin_locations
Product storage in bin locations.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| warehouse_id | bigint | FK to warehouses |
| bin_location_id | bigint | FK to bin_locations |
| quantity | int | Quantity in bin |
| is_primary | boolean | Primary location flag |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 17. product_warehouse_settings
Product settings per warehouse.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| warehouse_id | bigint | FK to warehouses |
| minimum_stock_level | int | Min stock for this warehouse |
| maximum_stock_level | int | Max stock |
| reorder_level | int | Reorder trigger level |
| reorder_quantity | int | Quantity to reorder |
| custom_selling_price | decimal(15,2) | Custom price for warehouse |
| is_available | boolean | Available in warehouse |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Stock Management

### 18. stocks
Current stock levels per product-warehouse.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| warehouse_id | bigint | FK to warehouses |
| quantity | int | Current quantity |
| average_cost | decimal(15,2) | Average cost |
| total_value | decimal(15,2) | Total inventory value |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 19. stock_ledgers
Complete stock movement history.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| batch_id | bigint | FK to product_batches |
| serial_id | bigint | FK to product_serials |
| warehouse_id | bigint | FK to warehouses |
| type | enum | in, out |
| reference_type | enum | purchase, opening_stock, adjustment, transfer_in, transfer_out, sales, return, damage, lost, purchase_return |
| reference_id | bigint | ID of reference record |
| reference_number | varchar | Invoice/PO number |
| quantity | int | Quantity moved |
| unit_cost | decimal(15,2) | Unit cost |
| weighted_avg_cost | decimal(15,2) | Weighted average cost |
| cost_method | enum | fifo, lifo, wavg, specific |
| total_cost | decimal(15,2) | Total cost |
| balance_after | int | Stock balance after |
| value_before | decimal(15,2) | Inventory value before |
| value_after | decimal(15,2) | Inventory value after |
| notes | text | Notes |
| created_by | bigint | FK to users |
| transaction_date | date | Transaction date |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 20. opening_stocks
Opening stock records per warehouse.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| warehouse_id | bigint | FK to warehouses |
| quantity | int | Opening quantity |
| unit_cost | decimal(15,2) | Unit cost |
| total_cost | decimal(15,2) | Total cost |
| opening_date | date | Opening date |
| notes | text | Notes |
| created_by | bigint | FK to users |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 21. product_batches
Batch tracking for FIFO/LIFO and expiry.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| warehouse_id | bigint | FK to warehouses |
| batch_number | varchar | Unique batch number |
| manufacturing_date | date | Manufacturing date |
| expiry_date | date | Expiry date |
| quantity | int | Total quantity |
| available_quantity | int | Available quantity |
| unit_cost | decimal(15,2) | Unit cost |
| selling_price | decimal(15,2) | Selling price |
| status | enum | active, expired, recalled, depleted |
| notes | text | Notes |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 22. product_serials
Serial/IMEI tracking for electronics.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| product_id | bigint | FK to products |
| warehouse_id | bigint | FK to warehouses |
| serial_number | varchar | Unique serial number |
| imei_number | varchar | Unique IMEI number |
| status | enum | in_stock, sold, damaged, returned, transferred |
| batch_id | bigint | FK to product_batches |
| notes | text | Notes |
| sold_to_customer_id | bigint | FK to customers |
| sold_date | date | Sale date |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Purchase Management

### 23. suppliers
Supplier information.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Supplier name |
| code | varchar | Unique code |
| company_name | varchar | Company name |
| email | varchar | Email |
| phone | varchar | Phone |
| mobile | varchar | Mobile |
| address | text | Address |
| city | varchar | City |
| state | varchar | State |
| country | varchar | Country |
| postal_code | varchar | Postal code |
| tax_id | varchar | Tax ID |
| opening_balance | decimal(15,2) | Opening balance |
| current_balance | decimal(15,2) | Current balance |
| notes | text | Notes |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 24. purchase_requests
Purchase requests (PR).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| pr_number | varchar | Unique PR number |
| request_date | date | Request date |
| warehouse_id | bigint | FK to warehouses |
| status | enum | pending, approved, rejected, converted |
| notes | text | Notes |
| requested_by | bigint | FK to users |
| approved_by | bigint | FK to users |
| approved_at | timestamp | Approval timestamp |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 25. purchase_orders
Purchase orders (PO).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| po_number | varchar | Unique PO number |
| purchase_request_id | bigint | FK to purchase_requests |
| supplier_id | bigint | FK to suppliers |
| warehouse_id | bigint | FK to warehouses |
| order_date | date | Order date |
| expected_delivery_date | date | Expected delivery |
| status | enum | draft, sent, partial, completed, cancelled |
| subtotal | decimal(15,2) | Subtotal |
| tax_amount | decimal(15,2) | Tax amount |
| discount_amount | decimal(15,2) | Discount |
| total_amount | decimal(15,2) | Total |
| notes | text | Notes |
| created_by | bigint | FK to users |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 26. purchase_order_items
PO line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| purchase_order_id | bigint | FK to purchase_orders |
| product_id | bigint | FK to products |
| quantity | int | Ordered quantity |
| unit_price | decimal(15,2) | Unit price |
| discount | decimal(15,2) | Discount |
| tax | decimal(15,2) | Tax |
| total | decimal(15,2) | Line total |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 27. grns (Goods Received Notes)
Goods receipt records.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| grn_number | varchar | Unique GRN number |
| purchase_order_id | bigint | FK to purchase_orders |
| warehouse_id | bigint | FK to warehouses |
| grn_date | date | Receipt date |
| status | enum | pending, verified, rejected |
| notes | text | Notes |
| received_by | bigint | FK to users |
| verified_by | bigint | FK to users |
| verified_at | timestamp | Verification timestamp |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 28. grn_items
GRN line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| grn_id | bigint | FK to grns |
| product_id | bigint | FK to products |
| ordered_quantity | int | Ordered quantity |
| received_quantity | int | Received quantity |
| unit_price | decimal(15,2) | Unit price |
| total | decimal(15,2) | Line total |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 29. purchases
Supplier invoices.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| invoice_number | varchar | Unique invoice number |
| supplier_id | bigint | FK to suppliers |
| warehouse_id | bigint | FK to warehouses |
| grn_id | bigint | FK to grns |
| invoice_date | date | Invoice date |
| due_date | date | Payment due date |
| status | enum | draft, pending, partial, paid, cancelled |
| subtotal | decimal(15,2) | Subtotal |
| tax_amount | decimal(15,2) | Tax |
| discount_amount | decimal(15,2) | Discount |
| shipping_cost | decimal(15,2) | Shipping |
| total_amount | decimal(15,2) | Total |
| paid_amount | decimal(15,2) | Paid amount |
| balance_amount | decimal(15,2) | Balance |
| notes | text | Notes |
| created_by | bigint | FK to users |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 30. purchase_items
Purchase invoice line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| purchase_id | bigint | FK to purchases |
| product_id | bigint | FK to products |
| quantity | int | Quantity |
| unit_price | decimal(15,2) | Unit price |
| discount | decimal(15,2) | Discount |
| tax | decimal(15,2) | Tax |
| total | decimal(15,2) | Line total |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 31. purchase_returns
Purchase return records.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| return_number | varchar | Unique return number |
| purchase_id | bigint | FK to purchases |
| supplier_id | bigint | FK to suppliers |
| warehouse_id | bigint | FK to warehouses |
| return_date | date | Return date |
| status | enum | draft, approved, completed, cancelled |
| subtotal | decimal(15,2) | Subtotal |
| tax_amount | decimal(15,2) | Tax |
| total_amount | decimal(15,2) | Total |
| notes | text | Notes |
| created_by | bigint | FK to users |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 32. purchase_return_items
Purchase return line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| purchase_return_id | bigint | FK to purchase_returns |
| product_id | bigint | FK to products |
| quantity | int | Return quantity |
| unit_price | decimal(15,2) | Unit price |
| tax | decimal(15,2) | Tax |
| total | decimal(15,2) | Line total |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Sales Management

### 33. customers
Customer information.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Customer name |
| code | varchar | Unique code |
| company_name | varchar | Company name |
| email | varchar | Email |
| phone | varchar | Phone |
| mobile | varchar | Mobile |
| address | text | Address |
| city | varchar | City |
| state | varchar | State |
| country | varchar | Country |
| postal_code | varchar | Postal code |
| tax_id | varchar | Tax ID |
| opening_balance | decimal(15,2) | Opening balance |
| current_balance | decimal(15,2) | Current balance |
| notes | text | Notes |
| is_active | boolean | Active status |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 34. sales_orders
Sales orders.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| order_number | varchar | Unique order number |
| customer_id | bigint | FK to customers |
| warehouse_id | bigint | FK to warehouses |
| order_date | date | Order date |
| delivery_date | date | Expected delivery |
| status | enum | draft, confirmed, processing, completed, cancelled |
| subtotal | decimal(15,2) | Subtotal |
| tax_amount | decimal(15,2) | Tax |
| discount_amount | decimal(15,2) | Discount |
| total_amount | decimal(15,2) | Total |
| notes | text | Notes |
| created_by | bigint | FK to users |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 35. sales_order_items
Sales order line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| sales_order_id | bigint | FK to sales_orders |
| product_id | bigint | FK to products |
| quantity | int | Quantity |
| unit_price | decimal(15,2) | Unit price |
| discount | decimal(15,2) | Discount |
| tax | decimal(15,2) | Tax |
| total | decimal(15,2) | Line total |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 36. delivery_challans
Delivery challan/packing slip.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| challan_number | varchar | Unique challan number |
| sales_order_id | bigint | FK to sales_orders |
| customer_id | bigint | FK to customers |
| warehouse_id | bigint | FK to warehouses |
| challan_date | date | Challan date |
| delivery_date | date | Delivery date |
| status | enum | pending, dispatched, delivered, cancelled |
| notes | text | Notes |
| created_by | bigint | FK to users |
| delivered_by | bigint | FK to users |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 37. delivery_challan_items
Delivery challan line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| delivery_challan_id | bigint | FK to delivery_challans |
| product_id | bigint | FK to products |
| quantity | int | Quantity |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 38. sales
Sales invoices.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| invoice_number | varchar | Unique invoice number |
| customer_id | bigint | FK to customers |
| warehouse_id | bigint | FK to warehouses |
| sales_order_id | bigint | FK to sales_orders |
| delivery_challan_id | bigint | FK to delivery_challans |
| invoice_date | date | Invoice date |
| due_date | date | Payment due date |
| status | enum | draft, pending, partial, paid, cancelled |
| subtotal | decimal(15,2) | Subtotal |
| tax_amount | decimal(15,2) | Tax |
| discount_amount | decimal(15,2) | Discount |
| shipping_cost | decimal(15,2) | Shipping |
| total_amount | decimal(15,2) | Total |
| paid_amount | decimal(15,2) | Paid amount |
| balance_amount | decimal(15,2) | Balance |
| notes | text | Notes |
| created_by | bigint | FK to users |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 39. sales_items
Sales invoice line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| sale_id | bigint | FK to sales |
| product_id | bigint | FK to products |
| quantity | int | Quantity |
| unit_price | decimal(15,2) | Unit price |
| discount | decimal(15,2) | Discount |
| tax | decimal(15,2) | Tax |
| total | decimal(15,2) | Line total |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 40. sales_returns
Sales return records.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| return_number | varchar | Unique return number |
| sale_id | bigint | FK to sales |
| customer_id | bigint | FK to customers |
| warehouse_id | bigint | FK to warehouses |
| return_date | date | Return date |
| status | enum | draft, approved, completed, cancelled |
| subtotal | decimal(15,2) | Subtotal |
| tax_amount | decimal(15,2) | Tax |
| total_amount | decimal(15,2) | Total |
| notes | text | Notes |
| created_by | bigint | FK to users |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 41. sales_return_items
Sales return line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| sales_return_id | bigint | FK to sales_returns |
| product_id | bigint | FK to products |
| quantity | int | Return quantity |
| unit_price | decimal(15,2) | Unit price |
| tax | decimal(15,2) | Tax |
| total | decimal(15,2) | Line total |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Transfer & Adjustment

### 42. transfers
Stock transfer between warehouses.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| transfer_number | varchar | Unique transfer number |
| from_warehouse_id | bigint | FK to warehouses |
| to_warehouse_id | bigint | FK to warehouses |
| transfer_date | date | Transfer date |
| status | enum | pending, approved, in_transit, completed, cancelled |
| notes | text | Notes |
| requested_by | bigint | FK to users |
| approved_by | bigint | FK to users |
| approved_at | timestamp | Approval timestamp |
| received_by | bigint | FK to users |
| received_at | timestamp | Receipt timestamp |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 43. transfer_items
Transfer line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| transfer_id | bigint | FK to transfers |
| product_id | bigint | FK to products |
| quantity | int | Transfer quantity |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 44. adjustments
Inventory adjustments.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| adjustment_number | varchar | Unique adjustment number |
| warehouse_id | bigint | FK to warehouses |
| adjustment_date | date | Adjustment date |
| status | enum | draft, approved, completed, cancelled |
| type | enum | increase, decrease |
| reason | text | Adjustment reason |
| notes | text | Notes |
| created_by | bigint | FK to users |
| approved_by | bigint | FK to users |
| approved_at | timestamp | Approval timestamp |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

### 45. adjustment_items
Adjustment line items.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| adjustment_id | bigint | FK to adjustments |
| product_id | bigint | FK to products |
| quantity | int | Adjustment quantity |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Financial Management

### 46. payments
Payment records for suppliers and customers.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| payment_number | varchar | Unique payment number |
| payment_type | enum | purchase, sale, expense, refund |
| party_type | enum | supplier, customer |
| party_id | bigint | ID of supplier/customer |
| reference_type | varchar | Purchase, Sale, etc. |
| reference_id | bigint | ID of reference record |
| reference_number | varchar | Invoice/PO number |
| payment_date | date | Payment date |
| amount | decimal(15,2) | Payment amount |
| payment_method | enum | cash, bank_transfer, cheque, card, mobile_banking, other |
| transaction_reference | varchar | Cheque no/Transaction ID |
| bank_name | varchar | Bank name |
| account_number | varchar | Account number |
| status | enum | pending, completed, failed, cancelled |
| notes | text | Notes |
| created_by | bigint | FK to users |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

## System & Audit

### 47. audit_logs
Complete system audit trail.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users |
| user_name | varchar | User name (cached) |
| event | varchar | created, updated, deleted, viewed, approved, rejected |
| auditable_type | varchar | Model name |
| auditable_id | bigint | Model ID |
| old_values | text | JSON of old values |
| new_values | text | JSON of new values |
| url | varchar | Request URL |
| ip_address | varchar | IP address |
| user_agent | varchar | User agent |
| description | text | Human-readable description |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 48. login_logs
User login tracking.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | FK to users |
| ip_address | varchar | IP address |
| user_agent | varchar | User agent |
| login_at | timestamp | Login timestamp |
| logout_at | timestamp | Logout timestamp |
| created_at | timestamp | |
| updated_at | timestamp | |

---

### 49. settings
System settings (key-value pairs).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| key | varchar | Unique setting key |
| value | text | Setting value |
| type | varchar | Data type |
| group | varchar | Setting group |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 📈 Total Database Summary

- **Total Tables**: 49
- **Core Tables**: 5 (users, roles, permissions, etc.)
- **Product Management**: 8 tables
- **Warehouse Management**: 5 tables
- **Stock Management**: 5 tables
- **Purchase Management**: 10 tables
- **Sales Management**: 9 tables
- **Transfer & Adjustment**: 4 tables
- **Financial**: 1 table
- **System & Audit**: 2 tables

---

## 🔗 Key Relationships

### One-to-Many
- Category → Products
- Category → Sub-categories (self-referential)
- Unit → Products
- Warehouse → Stocks
- Supplier → Purchases
- Customer → Sales
- Product → Product Images
- Product → Product Barcodes
- Product → Product Batches
- Product → Product Serials
- Product → Product Variations

### Many-to-Many
- Users ↔ Roles (via user_role)
- Roles ↔ Permissions (via role_permission)
- Users ↔ Warehouses (via warehouse_users)
- Products ↔ Bin Locations (via product_bin_locations)

### Hierarchical
- Categories (parent-child relationship)

---

**Last Updated**: December 4, 2025
**Schema Version**: 2.0.0

