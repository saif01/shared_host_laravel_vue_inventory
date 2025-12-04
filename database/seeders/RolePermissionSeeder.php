<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // General Permissions
            ['name' => 'Access Dashboard', 'slug' => 'access-dashboard', 'group' => 'general', 'description' => 'Access to admin dashboard'],
            
            // Product Management Permissions
            ['name' => 'View Products', 'slug' => 'view-products', 'group' => 'products', 'description' => 'View product list'],
            ['name' => 'Create Products', 'slug' => 'create-products', 'group' => 'products', 'description' => 'Create new products'],
            ['name' => 'Edit Products', 'slug' => 'edit-products', 'group' => 'products', 'description' => 'Edit existing products'],
            ['name' => 'Delete Products', 'slug' => 'delete-products', 'group' => 'products', 'description' => 'Delete products'],
            ['name' => 'Manage Categories', 'slug' => 'manage-categories', 'group' => 'products', 'description' => 'Manage product categories'],
            ['name' => 'Manage Units', 'slug' => 'manage-units', 'group' => 'products', 'description' => 'Manage product units'],
            
            // Warehouse Management Permissions
            ['name' => 'View Warehouses', 'slug' => 'view-warehouses', 'group' => 'warehouses', 'description' => 'View warehouse list'],
            ['name' => 'Manage Warehouses', 'slug' => 'manage-warehouses', 'group' => 'warehouses', 'description' => 'Create, edit, delete warehouses'],
            ['name' => 'Manage Warehouse Users', 'slug' => 'manage-warehouse-users', 'group' => 'warehouses', 'description' => 'Assign users to warehouses'],
            ['name' => 'View Stock Levels', 'slug' => 'view-stock-levels', 'group' => 'warehouses', 'description' => 'View current stock levels'],
            
            // Purchase Management Permissions
            ['name' => 'View Purchases', 'slug' => 'view-purchases', 'group' => 'purchases', 'description' => 'View purchase records'],
            ['name' => 'Create Purchase Request', 'slug' => 'create-purchase-request', 'group' => 'purchases', 'description' => 'Create purchase requests (PR)'],
            ['name' => 'Create Purchase Order', 'slug' => 'create-purchase-order', 'group' => 'purchases', 'description' => 'Create purchase orders (PO)'],
            ['name' => 'Create GRN', 'slug' => 'create-grn', 'group' => 'purchases', 'description' => 'Create goods received notes'],
            ['name' => 'Create Purchase Invoice', 'slug' => 'create-purchase-invoice', 'group' => 'purchases', 'description' => 'Create purchase invoices'],
            ['name' => 'Manage Purchase Returns', 'slug' => 'manage-purchase-returns', 'group' => 'purchases', 'description' => 'Process purchase returns'],
            ['name' => 'Approve Purchase Requests', 'slug' => 'approve-purchase-requests', 'group' => 'purchases', 'description' => 'Approve or reject purchase requests'],
            ['name' => 'Manage Suppliers', 'slug' => 'manage-suppliers', 'group' => 'purchases', 'description' => 'Manage supplier information'],
            
            // Sales Management Permissions
            ['name' => 'View Sales', 'slug' => 'view-sales', 'group' => 'sales', 'description' => 'View sales records'],
            ['name' => 'Create Sales Order', 'slug' => 'create-sales-order', 'group' => 'sales', 'description' => 'Create sales orders'],
            ['name' => 'Create Delivery Challan', 'slug' => 'create-delivery-challan', 'group' => 'sales', 'description' => 'Create delivery challans'],
            ['name' => 'Create Sales Invoice', 'slug' => 'create-sales-invoice', 'group' => 'sales', 'description' => 'Create sales invoices'],
            ['name' => 'Manage Sales Returns', 'slug' => 'manage-sales-returns', 'group' => 'sales', 'description' => 'Process sales returns'],
            ['name' => 'Manage Customers', 'slug' => 'manage-customers', 'group' => 'sales', 'description' => 'Manage customer information'],
            
            // Stock Transfer Permissions
            ['name' => 'View Stock Transfers', 'slug' => 'view-stock-transfers', 'group' => 'transfers', 'description' => 'View stock transfer records'],
            ['name' => 'Create Stock Transfer', 'slug' => 'create-stock-transfer', 'group' => 'transfers', 'description' => 'Create stock transfer requests'],
            ['name' => 'Approve Stock Transfer', 'slug' => 'approve-stock-transfer', 'group' => 'transfers', 'description' => 'Approve or reject transfer requests'],
            ['name' => 'Receive Stock Transfer', 'slug' => 'receive-stock-transfer', 'group' => 'transfers', 'description' => 'Receive transferred stock'],
            
            // Inventory Adjustment Permissions
            ['name' => 'View Adjustments', 'slug' => 'view-adjustments', 'group' => 'adjustments', 'description' => 'View inventory adjustments'],
            ['name' => 'Create Adjustment', 'slug' => 'create-adjustment', 'group' => 'adjustments', 'description' => 'Create inventory adjustment requests'],
            ['name' => 'Approve Adjustment', 'slug' => 'approve-adjustment', 'group' => 'adjustments', 'description' => 'Approve or reject adjustments'],
            
            // Payment Permissions
            ['name' => 'View Payments', 'slug' => 'view-payments', 'group' => 'payments', 'description' => 'View payment records'],
            ['name' => 'Create Payment', 'slug' => 'create-payment', 'group' => 'payments', 'description' => 'Record payments'],
            ['name' => 'Manage Payments', 'slug' => 'manage-payments', 'group' => 'payments', 'description' => 'Edit and delete payments'],
            
            // Report Permissions
            ['name' => 'View Reports', 'slug' => 'view-reports', 'group' => 'reports', 'description' => 'View inventory reports'],
            ['name' => 'Export Reports', 'slug' => 'export-reports', 'group' => 'reports', 'description' => 'Export reports to various formats'],
            ['name' => 'View Stock Ledger', 'slug' => 'view-stock-ledger', 'group' => 'reports', 'description' => 'View complete stock movement history'],
            ['name' => 'View Audit Logs', 'slug' => 'view-audit-logs', 'group' => 'reports', 'description' => 'View system audit logs'],
            
            // User & Role Permissions
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'group' => 'users', 'description' => 'Create, edit, delete users'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'group' => 'users', 'description' => 'Create, edit, delete roles and permissions'],
            
            // Settings Permissions
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'group' => 'settings', 'description' => 'Manage system settings'],
            
            // System Permissions
            ['name' => 'View Login Logs', 'slug' => 'view-login-logs', 'group' => 'system', 'description' => 'View and manage login logs'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[$permission['slug']] = Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create Roles
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Full system access - manage users, roles, warehouse access, and configure master data',
                'is_system' => true,
                'is_active' => true,
                'order' => 1,
                'permissions' => array_keys($createdPermissions), // All permissions
            ],
            [
                'name' => 'Store/Warehouse Manager',
                'slug' => 'warehouse-manager',
                'description' => 'Manage stock in/out, transfer requests, and physical stock count',
                'is_system' => true,
                'is_active' => true,
                'order' => 2,
                'permissions' => [
                    'access-dashboard',
                    'view-products',
                    'view-warehouses',
                    'view-stock-levels',
                    'view-stock-ledger',
                    'create-stock-transfer',
                    'approve-stock-transfer',
                    'receive-stock-transfer',
                    'view-stock-transfers',
                    'create-adjustment',
                    'view-adjustments',
                    'view-purchases',
                    'create-grn',
                    'view-sales',
                    'create-delivery-challan',
                    'view-reports',
                ],
            ],
            [
                'name' => 'Purchase Officer',
                'slug' => 'purchase-officer',
                'description' => 'Handle PR, PO, and supplier invoice management',
                'is_system' => true,
                'is_active' => true,
                'order' => 3,
                'permissions' => [
                    'access-dashboard',
                    'view-products',
                    'view-warehouses',
                    'view-stock-levels',
                    'view-purchases',
                    'create-purchase-request',
                    'create-purchase-order',
                    'create-grn',
                    'create-purchase-invoice',
                    'manage-purchase-returns',
                    'manage-suppliers',
                    'view-payments',
                    'create-payment',
                    'view-reports',
                ],
            ],
            [
                'name' => 'Sales Officer',
                'slug' => 'sales-officer',
                'description' => 'Handle sales orders, delivery, and invoicing',
                'is_system' => true,
                'is_active' => true,
                'order' => 4,
                'permissions' => [
                    'access-dashboard',
                    'view-products',
                    'view-warehouses',
                    'view-stock-levels',
                    'view-sales',
                    'create-sales-order',
                    'create-delivery-challan',
                    'create-sales-invoice',
                    'manage-sales-returns',
                    'manage-customers',
                    'view-payments',
                    'view-reports',
                ],
            ],
            [
                'name' => 'Accounts Officer',
                'slug' => 'accounts-officer',
                'description' => 'Handle payments, receipts, and supplier/customer ledger',
                'is_system' => true,
                'is_active' => true,
                'order' => 5,
                'permissions' => [
                    'access-dashboard',
                    'view-purchases',
                    'view-sales',
                    'view-payments',
                    'create-payment',
                    'manage-payments',
                    'manage-suppliers',
                    'manage-customers',
                    'view-reports',
                    'export-reports',
                ],
            ],
            [
                'name' => 'Auditor',
                'slug' => 'auditor',
                'description' => 'View-only access for stock and financial audits',
                'is_system' => true,
                'is_active' => true,
                'order' => 6,
                'permissions' => [
                    'access-dashboard',
                    'view-products',
                    'view-warehouses',
                    'view-stock-levels',
                    'view-stock-ledger',
                    'view-purchases',
                    'view-sales',
                    'view-stock-transfers',
                    'view-adjustments',
                    'view-payments',
                    'view-reports',
                    'export-reports',
                    'view-audit-logs',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            // Attach permissions
            $permissionIds = [];
            foreach ($permissions as $permissionSlug) {
                if (isset($createdPermissions[$permissionSlug])) {
                    $permissionIds[] = $createdPermissions[$permissionSlug]->id;
                }
            }
            $role->permissions()->sync($permissionIds);
        }

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
