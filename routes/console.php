<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:check-sla-breaches')->everyFifteenMinutes();
Schedule::command('backup:database')->dailyAt('23:59');
Schedule::command('app:prune-location-history --days=7')->dailyAt('03:00');
