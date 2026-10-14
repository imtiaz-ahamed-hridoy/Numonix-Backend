<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Founder Programme Automation (Spec 4.2)
|--------------------------------------------------------------------------
| Runs every night at midnight UTC.
|  - Day 25: send reminder email + optional review form link
|  - Day 30: generate Paddle checkout link, email it to founder
|  - Day 31: revoke Tier 2 access for unconverted founders
|
| withoutOverlapping() prevents a second instance from starting if
| a large batch is still processing from the previous run.
*/
Schedule::command('founders:process-trials')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/founders-cron.log'));

