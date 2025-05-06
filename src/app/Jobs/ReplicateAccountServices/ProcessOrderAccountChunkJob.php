<?php

namespace App\Jobs\ReplicateAccountServices;

use App\Models\OrderAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrderAccountChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $orderIds;
    protected int $accountId;

    public function __construct(array $orderIds, int $accountId)
    {
        $this->orderIds = $orderIds;
        $this->accountId = $accountId;
    }

    public function handle()
    {
        try {
            $data = array_map(function ($orderId) {
                return [
                    'order_id' => $orderId,
                    'account_id' => $this->accountId,
                ];
            }, $this->orderIds);

            OrderAccount::insertOrIgnore($data);

        } catch (\Throwable $e) {
            Log::error('Order account chunk processing error', [
                'account_id' => $this->accountId,
                'error' => $e->getMessage(),
                'sample_order_id' => $this->orderIds[0] ?? null,
            ]);
            throw $e;
        }
    }
}