<?php

namespace App\Console\Commands\SyncServices;

use App\Console\Commands\BaseCommand;
use App\Services\Sync\IncomesSyncService;
use App\Services\Sync\OrdersSyncService;
use App\Services\Sync\SalesSyncService;
use App\Services\Sync\StocksSyncService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncApiData extends BaseCommand
{
    protected $signature = 'api:sync 
                            {--date-from= : Start date for sync (format: Y-m-d)} 
                            {--date-to= : End date for sync (format: Y-m-d)}';

    protected $description = 'Sync data from API';

    protected function rules(): array
    {
        return [
            'date-from' => 'nullable|date_format:Y-m-d|before_or_equal:date-to',
            'date-to'   => 'nullable|date_format:Y-m-d|after_or_equal:date-from',
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'date-from.date_format' => 'The --date-from must be in format Y-m-d.',
            'date-from.before_or_equal' => 'The --date-from must be before or equal to --date-to.',
            'date-to.date_format' => 'The --date-to must be in format Y-m-d.',
            'date-to.after_or_equal' => 'The --date-to must be after or equal to --date-from.',
        ];
    }

    protected function prepareInput(array $arguments): array
    {
        return array_merge($arguments, $this->options());
    }

    protected function handleValidated(array $validatedArguments)
    {
        $today = Carbon::today();
        $dateFrom = $validatedArguments['date-from']
            ? Carbon::parse($validatedArguments['date-from'])
            : $today;
        $dateTo = $validatedArguments['date-to']
            ? Carbon::parse($validatedArguments['date-to'])
            : $today;

        if ($dateFrom->gt($today)) {
            $this->error('The --date-from cannot be in the future.');
            return 1;
        }

        if ($dateTo->gt($today)) {
            $this->error('The --date-to cannot be in the future.');
            return 1;
        }

        $this->info(sprintf(
            'Starting API sync from %s to %s...',
            $dateFrom->format('Y-m-d'),
            $dateTo->format('Y-m-d')
        ));

        $syncOperations = [
            'sales' => app(SalesSyncService::class),
            'orders' => app(OrdersSyncService::class),
            'stocks' => app(StocksSyncService::class),
            'incomes' => app(IncomesSyncService::class),
        ];

        foreach ($syncOperations as $type => $service) {
            try {
                $this->info("Syncing $type...");
                // For stocks we use the current date for fromDate and toDate
                if ($type === 'stocks') {
                    $service->sync($today, $today);
                    $this->info("$type synced for today: " . $today->format('Y-m-d'));
                } else {
                    $service->sync($dateFrom, $dateTo);
                    $this->info("$type synced successfully.");
                }
            } catch (\Throwable $e) {
                Log::error("$type sync failed", ['error' => $e->getMessage()]);
                $this->error("$type sync failed: " . $e->getMessage());
            }
        }

        $this->info('API sync completed.');
        return 0;
    }
}