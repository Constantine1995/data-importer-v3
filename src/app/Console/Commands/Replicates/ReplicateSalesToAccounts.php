<?php

namespace App\Console\Commands\Replicates;

use App\Jobs\ReplicateServices\ReplicateSalesJob;
use App\Models\Account;
use Carbon\Carbon;
use App\Console\Commands\BaseCommand;

class ReplicateSalesToAccounts extends BaseCommand
{
    protected $signature = 'replicate:sales {--date-from=} {--date-to=}';
    protected $description = 'Duplicates data from Sales to SalesAccounts for all accounts';

    protected function rules(): array
    {
        return [
            'date-from' => ['nullable', 'date', 'before_or_equal:date-to'],
            'date-to' => ['nullable', 'date', 'after_or_equal:date-from'],
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'date-from.date' => 'The --date-from parameter must be a valid date.',
            'date-from.before_or_equal' => 'The --date-from cannot be later than --date-to.',
            'date-to.date' => 'The --date-to parameter must be a valid date.',
            'date-to.after_or_equal' => 'The --date-to cannot be earlier than --date-from.',
        ];
    }

    protected function prepareInput(array $arguments): array
    {
        // Retrieve command options
        $input = [
            'date-from' => $this->option('date-from'),
            'date-to' => $this->option('date-to'),
        ];

        return array_merge($arguments, $input);
    }

    protected function handleValidated(array $validatedArguments)
    {
        // Parse dates with default value (today)
        $dateFrom = $this->option('date-from')
            ? Carbon::parse($this->option('date-from'))
            : Carbon::today();
        $dateTo = $this->option('date-to')
            ? Carbon::parse($this->option('date-to'))
            : Carbon::today();

        // Check if accounts exist
        if (!Account::exists()) {
            $this->warn('No accounts to process!');
            return 1;
        }

        // Process each account using cursor for memory efficiency
        foreach (Account::cursor() as $account) {
            // Dispatch replication job for each account
            ReplicateSalesJob::dispatch($account->id, $dateFrom, $dateTo);
            $this->info("Task dispatched to Job for account: $account->name, Service: Sales");
        }

        return 0;
    }
}