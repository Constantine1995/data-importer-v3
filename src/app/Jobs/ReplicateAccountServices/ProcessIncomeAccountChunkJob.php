<?php

namespace App\Jobs\ReplicateAccountServices;

use App\Models\IncomeAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIncomeAccountChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $incomeIds;
    protected int $accountId;

    public function __construct(array $incomeIds, int $accountId)
    {
        $this->incomeIds = $incomeIds;
        $this->accountId = $accountId;
    }

    public function handle()
    {
        try {
            $data = array_map(function ($incomeId) {
                return [
                    'income_id' => $incomeId,
                    'account_id' => $this->accountId,
                ];
            }, $this->incomeIds);

            IncomeAccount::insertOrIgnore($data);

        } catch (\Throwable $e) {
            Log::error('Income account chunk processing error', [
                'account_id' => $this->accountId,
                'error' => $e->getMessage(),
                'sample_income_id' => $this->incomeIds[0] ?? null,
            ]);
            throw $e;
        }
    }
}