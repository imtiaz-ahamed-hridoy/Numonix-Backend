<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name'               => 'Admin',
                'email'              => 'admin@admin.com',
                'email_verified_at'  => now(),
                'password'           => '12345678',
                'is_age_verified'    => true,
                'is_subscribed'      => true,
                'plan_name'          => 'Tier 2',
                'subscription_tier'  => 'tier_3',
                'role'               => 'Admin',
            ],
            [
                'name'               => 'User',
                'email'              => 'user@gmail.com',
                'email_verified_at'  => now(),
                'password'           => '12345678',
                'is_age_verified'    => true,
                'is_subscribed'      => false,
                'plan_name'          => 'Foundation (Free)',
                'subscription_tier'  => 'tier_1',
                'role'               => 'User',
            ],
            [
                'name'               => 'User 2',
                'email'              => 'user2@gmail.com',
                'email_verified_at'  => now(),
                'password'           => '12345678',
                'is_age_verified'    => true,
                'is_subscribed'      => true,
                'plan_name'          => 'Tier 1',
                'subscription_tier'  => 'tier_2',
                'role'               => 'User',
            ],
            [
                'name'               => 'Founder User',
                'email'              => 'founder@gmail.com',
                'email_verified_at'  => now(),
                'password'           => '12345678',
                'is_age_verified'    => true,
                'is_subscribed'      => true,
                'plan_name'          => 'Founder Trial',
                'subscription_tier'  => 'tier_2',
                'role'               => 'User',
                'is_founder'         => true,
                'founder_trial_ends_at' => now()->addDays(20),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name'                  => $user['name'],
                    'email'                 => $user['email'],
                    'email_verified_at'     => $user['email_verified_at'],
                    'password'              => Hash::make($user['password']),
                    'is_age_verified'       => $user['is_age_verified'],
                    'is_subscribed'         => $user['is_subscribed'],
                    'plan_name'             => $user['plan_name'],
                    'subscription_tier'     => $user['subscription_tier'],
                    'role'                  => $user['role'],
                    'is_founder'            => $user['is_founder'] ?? false,
                    'founder_trial_ends_at' => $user['founder_trial_ends_at'] ?? null,
                ]
            );
        }
    }
}
