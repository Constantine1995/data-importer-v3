<?php

namespace App\Jobs\ReplicateAccountServices;

use App\Models\StockAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStockAccountChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $stockIds;
    protected int $accountId;

    public function __construct(array $stockIds, int $accountId)
    {
        $this->stockIds = $stockIds;
        $this->accountId = $accountId;
    }
    public function handle()
    {
        try {
            $data = array_map(function ($stockId) {
                return [
                    'stock_id' => $stockId,
                    'account_id' => $this->accountId,
                ];
            }, $this->stockIds);

            StockAccount::insertOrIgnore($data);

        } catch (\Throwable $e) {
            Log::error('Stock account chunk processing error', [
                'account_id' => $this->accountId,
                'error' => $e->getMessage(),
                'sample_stock_id' => $this->stockIds[0] ?? null,
            ]);
            throw $e;
        }
    }
}