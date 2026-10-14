<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class DeployCheckCommand extends Command
{
    protected $signature = 'deploy:check';
    protected $description = 'Perform deep pre-flight verification of production server deployment readiness';

    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>===============================================================</>');
        $this->line('<fg=cyan;options=bold>   NumoniX - Production Server Pre-Flight Deep Check           </>');
        $this->line('<fg=cyan;options=bold>===============================================================</>');
        $this->newLine();

        $errors = 0;
        $warnings = 0;

        // -- 1. PHP Runtime & Extensions ------------------------------------------
        $this->info('--> 1. Checking PHP Runtime & Extensions...');
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.2.0', '>=')) {
            $this->line("  [<fg=green>PASS</>] PHP Version: {$phpVersion} (>= 8.2.0 required)");
        } else {
            $this->line("  [<fg=red>FAIL</>] PHP Version: {$phpVersion} (Minimum required is 8.2.0)");
            $errors++;
        }

        $requiredExtensions = [
            'bcmath', 'ctype', 'curl', 'dom', 'fileinfo',
            'gd', 'intl', 'json', 'mbstring', 'openssl',
            'pcre', 'pdo', 'pdo_mysql', 'tokenizer', 'xml', 'zip'
        ];

        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->line("  [<fg=green>PASS</>] Extension '{$ext}' loaded.");
            } else {
                $this->line("  [<fg=red>FAIL</>] Required extension '{$ext}' is MISSING!");
                $errors++;
            }
        }

        // -- 2. Storage Directory Permissions -------------------------------------
        $this->newLine();
        $this->info('--> 2. Checking Storage & Cache Directory Permissions...');

        $directories = [
            'storage/app'                => storage_path('app'),
            'storage/app/public'         => storage_path('app/public'),
            'storage/framework/cache'    => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views'    => storage_path('framework/views'),
            'storage/logs'               => storage_path('logs'),
            'bootstrap/cache'            => base_path('bootstrap/cache'),
        ];

        foreach ($directories as $label => $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0775, true, true);
            }

            if (is_writable($path)) {
                $this->line("  [<fg=green>PASS</>] '{$label}' is writable.");
            } else {
                $this->line("  [<fg=red>FAIL</>] '{$label}' is NOT writable by current user! (chmod 775 / chown www-data required)");
                $errors++;
            }
        }

        // -- 3. Public Storage Symlink --------------------------------------------
        $this->newLine();
        $this->info('--> 3. Checking Public Storage Symlink...');
        $publicStorage = public_path('storage');
        if (File::exists($publicStorage) || is_link($publicStorage)) {
            $this->line("  [<fg=green>PASS</>] Public storage symlink exists.");
        } else {
            $this->line("  [<fg=yellow>WARN</>] Public storage symlink missing. Run: php artisan storage:link");
            $warnings++;
        }

        // -- 4. Environment Variables ---------------------------------------------
        $this->newLine();
        $this->info('--> 4. Checking Environment Variables...');

        if (empty(config('app.key'))) {
            $this->line("  [<fg=red>FAIL</>] APP_KEY is empty! Run: php artisan key:generate");
            $errors++;
        } else {
            $this->line("  [<fg=green>PASS</>] APP_KEY is properly set.");
        }

        $appUrl = config('app.url');
        if (str_starts_with($appUrl, 'https://')) {
            $this->line("  [<fg=green>PASS</>] APP_URL is using HTTPS: {$appUrl}");
        } elseif (app()->environment('production')) {
            $this->line("  [<fg=yellow>WARN</>] APP_URL is not HTTPS in production: {$appUrl}");
            $warnings++;
        } else {
            $this->line("  [<fg=green>PASS</>] APP_URL: {$appUrl}");
        }

        $firebaseCreds = config('services.firebase.credentials_file') ?? config('firebase.projects.app.credentials');
        if (!empty($firebaseCreds) && file_exists($firebaseCreds)) {
            $this->line("  [<fg=green>PASS</>] Firebase credentials file found at: {$firebaseCreds}");
        } else {
            $this->line("  [<fg=yellow>WARN</>] Firebase credentials not configured yet. (Upload via Admin Dashboard: Settings > Firebase Settings, or configure in .env)");
            $warnings++;
        }

        // -- 5. Database Connection & Essential Tables ----------------------------
        $this->newLine();
        $this->info('--> 5. Checking Database Connectivity & Schema...');

        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $this->line("  [<fg=green>PASS</>] Successfully connected to database: '{$dbName}'");

            $essentialTables = [
                'users', 'jobs', 'personal_access_tokens',
                'learnings', 'quizzes', 'system_settings'
            ];

            foreach ($essentialTables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $this->line("  [<fg=green>PASS</>] Table '{$table}' exists.");
                } else {
                    $this->line("  [<fg=red>FAIL</>] Table '{$table}' is missing! Run: php artisan migrate");
                    $errors++;
                }
            }
        } catch (\Throwable $e) {
            $this->line("  [<fg=red>FAIL</>] Database connection failed: " . $e->getMessage());
            $errors++;
        }

        // -- 6. Routes & Route Cache ----------------------------------------------
        $this->newLine();
        $this->info('--> 6. Checking Routing & Health Endpoint...');

        $routeCount = count(Route::getRoutes());
        $this->line("  [<fg=green>PASS</>] {$routeCount} routes successfully registered.");

        $hasHealthRoute = Route::has('up') || collect(Route::getRoutes())->contains(fn($r) => $r->uri() === 'up');
        if ($hasHealthRoute) {
            $this->line("  [<fg=green>PASS</>] Health check route '/up' is active.");
        } else {
            $this->line("  [<fg=yellow>WARN</>] Health check route '/up' not detected.");
            $warnings++;
        }

        // -- Summary --------------------------------------------------------------
        $this->newLine();
        $this->line('<fg=cyan;options=bold>===============================================================</>');
        if ($errors === 0 && $warnings === 0) {
            $this->line('  <fg=green;options=bold>[SUCCESS] ALL CHECKS PASSED! The server is 100% production ready.</>');
        } elseif ($errors === 0) {
            $this->line("  <fg=green;options=bold>[WARNING] CHECKS PASSED WITH {$warnings} WARNING(S).</> (No critical blockers)");
        } else {
            $this->line("  <fg=red;options=bold>[ERROR] {$errors} CRITICAL ERROR(S) DETECTED.</> Please resolve before live traffic.");
        }
        $this->line('<fg=cyan;options=bold>===============================================================</>');
        $this->newLine();

        return $errors === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
