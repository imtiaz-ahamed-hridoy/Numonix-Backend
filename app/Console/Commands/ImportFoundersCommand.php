<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\EmailLog;
use App\Mail\FounderWelcomeEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Import accepted founders from a Tally CSV export.
 *
 * Per spec 3.11: Import accepted applicants from Tally (CSV export/import),
 * CREATE user records, grant automatic Tier 2 access, and set 30-day trial
 * from import time. Repeatable as a periodic import for post-launch stragglers.
 *
 * CSV format: must have "email" column, "name" column is optional.
 *
 * Usage: php artisan founders:import /path/to/tally-export.csv
 */
class ImportFoundersCommand extends Command
{
    protected $signature = 'founders:import {file : Path to the Tally CSV export file}';
    protected $description = 'Import accepted founders from a Tally CSV file, create accounts, and activate their 30-day trial';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $file = fopen($filePath, 'r');
        $header = array_map('trim', array_map('strtolower', fgetcsv($file) ?: []));

        if (!$header || !in_array('email', $header)) {
            $this->error('CSV must have an "email" column.');
            fclose($file);
            return 1;
        }

        $emailIndex = array_search('email', $header);
        $nameIndex = array_search('name', $header); // Optional

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        while (($row = fgetcsv($file)) !== false) {
            $email = trim($row[$emailIndex] ?? '');
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors++;
                continue;
            }

            $name = ($nameIndex !== false) ? trim($row[$nameIndex] ?? '') : '';
            if (!$name) {
                $name = ucfirst(explode('@', $email)[0]); // Fallback name from email
            }

            try {
                // Per spec 3.11: CREATE user records (firstOrCreate - idempotent for re-runs)
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => Hash::make(Str::random(16)), // Temp password - user will reset
                        'role' => 'User',
                        'status' => 'active',
                    ]
                );

                if ($user->is_founder) {
                    $this->warn("Skipped (already founder): {$email}");
                    $skipped++;
                    continue;
                }

                // Set founder rolling state (Launch Roadmap 7.4):
                // 30-day trial starts when user clicks activation link, not at CSV import.
                $user->is_founder = true;
                $user->imported_at = Carbon::now();
                $user->activated_at = null;
                $user->founder_trial_ends_at = null; // Set upon activation link click
                $user->activation_token = Str::random(64);
                $user->subscription_tier = 'tier_1'; // Tier 1 until activated
                $user->is_subscribed = false;
                $user->plan_name = 'Founder Pending Activation';
                $user->save();

                // Send founder welcome/activation email with single-use link
                try {
                    $activationUrl = url('/api/founder/activate/' . $user->activation_token);
                    $mailable = new FounderWelcomeEmail($user, $activationUrl);
                    Mail::to($user->email)->send($mailable);

                    EmailLog::create([
                        'user_id' => $user->id,
                        'type' => 'founder_welcome',
                        'status' => 'sent',
                        'sent_at' => now(),
                        'subject' => 'Welcome to the NumoniX Founder\'s Programme!',
                        'body' => $mailable->render(),
                    ]);
                } catch (\Exception $emailEx) {
                    Log::error("FounderWelcomeEmail failed for {$email}: " . $emailEx->getMessage());
                    $this->warn("Founder imported but email failed: {$email}");
                }

                $this->info("Imported: {$email}");
                $imported++;
            } catch (\Exception $e) {
                Log::error("ImportFounders error for {$email}: " . $e->getMessage());
                $this->error("Error processing {$email}: " . $e->getMessage());
                $errors++;
            }
        }

        fclose($file);
        $this->info("Done. Imported: {$imported}, Skipped: {$skipped}, Errors: {$errors}");
        return 0;
    }
}
