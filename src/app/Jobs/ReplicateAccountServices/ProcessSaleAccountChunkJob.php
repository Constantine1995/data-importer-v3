<?php

namespace App\Jobs\ReplicateAccountServices;

use App\Models\SaleAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSaleAccountChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $saleIds;
    protected int $accountId;

    public function __construct(array $saleIds, int $accountId)
    {
        $this->saleIds = $saleIds;
        $this->accountId = $accountId;
    }

    public function handle()
    {
        try {
            $data = array_map(function ($saleId) {
                return [
                    'sale_id' => $saleId,
                    'account_id' => $this->accountId,
                ];
            }, $this->saleIds);

            SaleAccount::insertOrIgnore($data);

        } catch (\Throwable $e) {
            Log::error('Sale account chunk processing error', [
                'account_id' => $this->accountId,
                'error' => $e->getMessage(),
                'sample_sale_id' => $this->saleIds[0] ?? null,
            ]);
            throw $e;
        }
    }
}