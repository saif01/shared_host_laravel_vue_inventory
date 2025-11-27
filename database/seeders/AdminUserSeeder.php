<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Define demo users for each role
        $demoUsers = [
            [
                'name' => 'Administrator',
                'email' => 'admin@mail.com',
                'password' => Hash::make('password'),
                'phone' => '+8801707080401',
                'gender' => 'male',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'bio' => 'System Administrator with full access to all features and settings.',
                'role_slug' => 'administrator',
            ],
            [
                'name' => 'Content Manager',
                'email' => 'content@mail.com',
                'password' => Hash::make('password'),
                'phone' => '+8801707080402',
                'gender' => 'female',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'bio' => 'Content Manager responsible for managing site content including about, services, products, and blog posts.',
                'role_slug' => 'content-manager',
            ],
            [
                'name' => 'Marketing Manager',
                'email' => 'marketing@mail.com',
                'password' => Hash::make('password'),
                'phone' => '+8801707080403',
                'gender' => 'male',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'bio' => 'Marketing Manager handling inbound leads, newsletters, and marketing content.',
                'role_slug' => 'marketing-manager',
            ],
            [
                'name' => 'HR Manager',
                'email' => 'hr@mail.com',
                'password' => Hash::make('password'),
                'phone' => '+8801707080404',
                'gender' => 'female',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'bio' => 'HR Manager responsible for managing careers and job applications.',
                'role_slug' => 'hr-manager',
            ],
            [
                'name' => 'Support Staff',
                'email' => 'support@mail.com',
                'password' => Hash::make('password'),
                'phone' => '+8801707080405',
                'gender' => 'male',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'country' => 'Bangladesh',
                'bio' => 'Support Staff with read-only access for monitoring leads.',
                'role_slug' => 'support-staff',
            ],
        ];

        // Create or update users and assign roles
        foreach ($demoUsers as $userData) {
            $roleSlug = $userData['role_slug'];
            unset($userData['role_slug']);

            // Create or update user
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assign role to the user
            $role = Role::where('slug', $roleSlug)->first();
            if ($role && !$user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }
        }

        $this->command->info('Demo users created successfully for all roles!');
    }
}
