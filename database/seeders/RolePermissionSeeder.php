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
            
            // Content Permissions
            ['name' => 'Manage About Page', 'slug' => 'manage-about', 'group' => 'content', 'description' => 'Create, edit, delete the About page'],
            ['name' => 'Manage Services', 'slug' => 'manage-services', 'group' => 'content', 'description' => 'Create, edit, delete services'],
            ['name' => 'Manage Products', 'slug' => 'manage-products', 'group' => 'content', 'description' => 'Create, edit, delete products, categories, and tags'],
            ['name' => 'Manage Blog Posts', 'slug' => 'manage-blog', 'group' => 'content', 'description' => 'Create, edit, delete blog posts and blog categories'],
            ['name' => 'Manage Media', 'slug' => 'manage-media', 'group' => 'content', 'description' => 'Upload and manage media files'],
            
            // User & Role Permissions
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'group' => 'users', 'description' => 'Create, edit, delete users'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'group' => 'users', 'description' => 'Create, edit, delete roles and permissions'],
            
            // Lead Permissions
            ['name' => 'View Leads', 'slug' => 'view-leads', 'group' => 'leads', 'description' => 'View all leads'],
            ['name' => 'Manage Leads', 'slug' => 'manage-leads', 'group' => 'leads', 'description' => 'Update and delete leads'],
            ['name' => 'Export Leads', 'slug' => 'export-leads', 'group' => 'leads', 'description' => 'Export leads to CSV'],
            
            // Newsletter Permissions
            ['name' => 'Manage Newsletters', 'slug' => 'manage-newsletters', 'group' => 'newsletters', 'description' => 'View, manage, and export newsletter subscriptions'],
            
            // Career Permissions
            ['name' => 'Manage Careers', 'slug' => 'manage-careers', 'group' => 'careers', 'description' => 'Manage job postings'],
            ['name' => 'Manage Applications', 'slug' => 'manage-applications', 'group' => 'careers', 'description' => 'View and manage job applications'],
            
            // Settings Permissions
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'group' => 'settings', 'description' => 'Manage site settings'],
            
            // System Permissions
            ['name' => 'View Login Logs', 'slug' => 'view-login-logs', 'group' => 'system', 'description' => 'View and manage login logs'],
            ['name' => 'View Visitor Logs', 'slug' => 'view-visitor-logs', 'group' => 'system', 'description' => 'View and manage visitor logs'],
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
                'description' => 'Full access to all features and settings',
                'is_system' => true,
                'is_active' => true,
                'order' => 1,
                'permissions' => array_keys($createdPermissions), // All permissions
            ],
            [
                'name' => 'Content Manager',
                'slug' => 'content-manager',
                'description' => 'Manage site content (about, services, products, blog) and media assets',
                'is_system' => true,
                'is_active' => true,
                'order' => 2,
                'permissions' => [
                    'access-dashboard',
                    'manage-about',
                    'manage-services',
                    'manage-products',
                    'manage-blog',
                    'manage-media',
                ],
            ],
            [
                'name' => 'Marketing Manager',
                'slug' => 'marketing-manager',
                'description' => 'Handle inbound leads, newsletters, and marketing content',
                'is_system' => true,
                'is_active' => true,
                'order' => 3,
                'permissions' => [
                    'access-dashboard',
                    'manage-blog',
                    'manage-media',
                    'view-leads',
                    'manage-leads',
                    'export-leads',
                    'manage-newsletters',
                ],
            ],
            [
                'name' => 'HR Manager',
                'slug' => 'hr-manager',
                'description' => 'Manage careers and job applications',
                'is_system' => true,
                'is_active' => true,
                'order' => 4,
                'permissions' => [
                    'access-dashboard',
                    'manage-careers',
                    'manage-applications',
                ],
            ],
            [
                'name' => 'Support Staff',
                'slug' => 'support-staff',
                'description' => 'Read-only operational access for monitoring leads',
                'is_system' => true,
                'is_active' => true,
                'order' => 5,
                'permissions' => [
                    'access-dashboard',
                    'view-leads',
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
