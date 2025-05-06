<?php

namespace App\Console\Commands\Replicates;

use App\Jobs\ReplicateServices\ReplicateStocksJob;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReplicateStocksToAccounts extends Command
{
    protected $signature = 'replicate:stocks';
    protected $description = 'Duplicates data from Stocks to StocksAccounts for all accounts';

    public function handle()
    {
        // Check if there are any accounts to process
        if (!Account::exists()) {
            $this->warn('No Accounts to process!');
            return;
        }

        // Process each account using cursor for memory efficiency
        foreach (Account::cursor() as $account) {
            // Dispatch replication job for each account
            ReplicateStocksJob::dispatch($account->id, Carbon::today(), Carbon::today());
            $this->info("Task sent to Job for account: $account->name, Services: Stocks");
        }
    }
}
