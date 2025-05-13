<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

//Schedule::command('sync:all')->twiceDaily(7, 19);

// fixed data loss when running cron
Schedule::command('sync:all')->dailyAt('6:00');
Schedule::command('sync:all')->dailyAt('23:59');