<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeds realistic Founder Programme test records.
 *
 * Creates 3 founders to cover all possible states:
 *   1. Active trial (in-progress, day 10 of 30)
 *   2. Expired trial (unconverted - trial ended, no payment)
 *   3. Converted founder (paid the lifetime rate)
 *
 * These records let developers and testers exercise the full
 * Founder journey without running the import command manually.
 */
class FounderSeeder extends Seeder
{
    public function run(): void
    {
        $founders = [
            // Founder 1 - Active trial (day 10 of 30)
            [
                'name'                      => 'Alice Founder',
                'email'                     => 'founder.active@example.com',
                'email_verified_at'         => now(),
                'password'                  => bcrypt('12345678'),
                'role'                      => 'User',
                'is_age_verified'           => true,
                'is_subscribed'             => true,
                'subscription_tier'         => 'tier_2',
                'plan_name'                 => 'Founder Trial',
                'is_founder'                => true,
                'founder_trial_ends_at'     => Carbon::now()->addDays(20),
                'founder_review_completed'  => false,
                'founder_converted_at'      => null,
                'founder_checkout_link'     => null,
                'founder_day25_sent_at'     => null,
                'founder_day30_sent_at'     => null,
            ],
            // Founder 2 - Expired trial (unconverted)
            [
                'name'                      => 'Bob Expired',
                'email'                     => 'founder.expired@example.com',
                'email_verified_at'         => now(),
                'password'                  => bcrypt('12345678'),
                'role'                      => 'User',
                'is_age_verified'           => true,
                'is_subscribed'             => false,
                'subscription_tier'         => 'tier_1',
                'plan_name'                 => 'Foundation (Free)',
                'is_founder'                => true,
                'founder_trial_ends_at'     => Carbon::now()->subDays(2),
                'founder_review_completed'  => true,
                'founder_converted_at'      => null,
                'founder_checkout_link'     => 'https://checkout.paddle.example.com/test',
                'founder_day25_sent_at'     => Carbon::now()->subDays(7),
                'founder_day30_sent_at'     => Carbon::now()->subDays(2),
            ],
            // Founder 3 - Converted (paid £24.99)
            [
                'name'                      => 'Carol Converted',
                'email'                     => 'founder.converted@example.com',
                'email_verified_at'         => now(),
                'password'                  => bcrypt('12345678'),
                'role'                      => 'User',
                'is_age_verified'           => true,
                'is_subscribed'             => true,
                'subscription_tier'         => 'tier_2',
                'plan_name'                 => 'Founder',
                'is_founder'                => true,
                'founder_trial_ends_at'     => Carbon::now()->subDays(1),
                'founder_review_completed'  => true,
                'founder_converted_at'      => Carbon::now()->subDays(1),
                'founder_checkout_link'     => 'https://checkout.paddle.example.com/test2',
                'founder_day25_sent_at'     => Carbon::now()->subDays(6),
                'founder_day30_sent_at'     => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($founders as $data) {
            // updateOrCreate so seeder is safely re-runnable
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }

        $this->command->info('Seeded 3 Founder Programme test records.');
    }
}