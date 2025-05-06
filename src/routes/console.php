<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('api:sync')->twiceDaily(7, 19);

Schedule::command('replicate:orders')->twiceDaily(8, 20);
Schedule::command('replicate:sales')->twiceDaily(8, 20);
Schedule::command('replicate:incomes')->twiceDaily(8, 20);
Schedule::command('replicate:stocks')->twiceDaily(8, 20);

// Schedule::command('api:sync --date-from=2025-04-01 --date-to=2025-05-01')
//     ->everyMinute()
//     ->withoutOverlapping()
//     ->then(function () {
//         Artisan::call('replicate:orders --date-from=2025-04-01 --date-to=2025-05-01');
//         Artisan::call('replicate:sales --date-from=2025-04-01 --date-to=2025-05-01');
//         Artisan::call('replicate:incomes --date-from=2025-04-01 --date-to=2025-05-01');
//         Artisan::call('replicate:stocks');
//     });