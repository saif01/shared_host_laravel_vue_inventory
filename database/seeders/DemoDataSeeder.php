<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\User;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Settings
        $this->seedSettings();

        $this->command->info('Demo data seeded successfully!');
    }

    private function seedSettings(): void
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'MICRO CONTROL TECHNOLOGY', 'type' => 'text', 'group' => 'general'],
            
            // Branding Settings
            ['key' => 'logo', 'value' => '', 'type' => 'image', 'group' => 'branding'],
            
            // Footer Settings
            ['key' => 'powered_by_text', 'value' => 'Powered By CPB-IT', 'type' => 'text', 'group' => 'footer'],
            ['key' => 'version', 'value' => 'v1.0', 'type' => 'text', 'group' => 'footer'],
            ['key' => 'copyright_text', 'value' => 'All Rights Reserved', 'type' => 'text', 'group' => 'footer'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
