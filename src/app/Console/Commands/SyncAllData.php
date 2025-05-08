<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SyncAllData extends BaseCommand
{
    protected $signature = 'sync:all 
                            {--date-from= : Start date for sync (format: Y-m-d)} 
                            {--date-to= : End date for sync (format: Y-m-d)}';

    protected $description = 'Orchestrate all sync and replicate commands in proper order';

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

    protected function handleValidated(array $validatedArguments): int
    {
        $dateFrom = $validatedArguments['date-from'] ?? null;
        $dateTo = $validatedArguments['date-to'] ?? null;

        // Call api:sync command (from external server)
        $this->info('Starting API sync...');
        $syncExitCode = Artisan::call('api:sync', [
            '--date-from' => $dateFrom,
            '--date-to' => $dateTo,
        ]);

        if ($syncExitCode !== 0) {
            $this->error('API sync failed. Stopping further execution.');
            return $syncExitCode;
        }

        $replicateCommands = [
            'replicate:orders',
            'replicate:sales',
            'replicate:incomes',
            'replicate:stocks',
        ];

        foreach ($replicateCommands as $command) {
            $this->info("Starting $command...");

            // Call replications commands
            $params = ($command === 'replicate:stocks')
                ? []
                : ['--date-from' => $dateFrom, '--date-to' => $dateTo];

            $exitCode = Artisan::call($command, $params);

            if ($exitCode !== 0) {
                $this->error("[ERROR] Command '$command' failed with exit code $exitCode. ");
                Log::error("Command $command failed", [
                    'exit_code' => $exitCode,
                    'options' => compact('dateFrom', 'dateTo')
                ]);
            }
        }

        $this->info('All sync and replicate operations completed.');
        return 0;
    }
}