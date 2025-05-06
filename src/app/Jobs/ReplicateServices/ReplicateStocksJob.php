<?php

namespace App\Jobs\ReplicateServices;

use App\Jobs\ReplicateAccountServices\ProcessStockAccountChunkJob;
use App\Models\Stock;
use App\Models\StockAccount;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReplicateStocksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $accountId;
    protected string $dateFrom;
    protected string $dateTo;

    public function __construct(int $accountId, Carbon $dateFrom, Carbon $dateTo)
    {
        $this->accountId = $accountId;
        $this->dateFrom = $dateFrom->toDateString();
        $this->dateTo = $dateTo->toDateString();
    }

    public function handle()
    {
        Log::info('Task started for account ID: ' . $this->accountId);

        // Clear the stocks account table before inserting new data
        StockAccount::where('account_id', $this->accountId)
            ->whereHas('stock', function ($query) {
                $query->whereBetween('date', [
                    Carbon::parse($this->dateFrom)->startOfDay(),
                    Carbon::parse($this->dateTo)->endOfDay()
                ]);
            })
            ->delete();

        // Processing orders in chunks of 1000 records
        $chunkSize = 1000;

        $query = Stock::select('id')->whereBetween('date', [
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay()
        ]);

        if (!$query->exists()) {
            Log::error("No data to replicate between dates: $this->dateFrom - $this->dateTo for Stocks");
            return;
        }

        $query->chunkById($chunkSize, function ($stocks) {
            ProcessStockAccountChunkJob::dispatch(
                $stocks->pluck('id')->all(),
                $this->accountId
            );
        }, 'id');

    }
}
