<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::create([
            'title' => 'Numonix',
            'system_name' => 'Numonix',
            'email' => 'info@example.com',
            'number' => '+1-555-0100',
            'logo' => null,
            'favicon' => null,
            'address' => null,
            'copyright_text' => 'Copyright 2025. All Rights Reserved. Powered by Numonix.',
            'description' => null,
            'soft_update_alert' => false,
            'force_update_alert' => false,
            'latest_version' => null,
            'app_store_url' => null,
            'play_store_url' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }
}
