<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    

    public function run(): void
    {
    
    
        $this->call([
            UserSeeder::class,
            SystemSettingSeeder::class,
            MailSettingSeeder::class,
            FaqSeeder::class,
            LearningSeeder::class,
            QuizSeeder::class,
            EarTrainingSeeder::class,
            CmsSeeder::class,
            HomeModuleSeeder::class,
            GuestAccessSettingSeeder::class,
            FounderSeeder::class,
        ]);
    }
}
