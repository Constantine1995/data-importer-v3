<?php

namespace App\Services\Sync;

use App\Models\Order;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdersSyncService extends LogSyncService
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Synchronizes orders data from the API for a given date range.
     *
     * @param Carbon $dateFrom Start date for the sync period.
     * @param Carbon $dateTo End date for the sync period.
     * @return void
     */
    public function sync(Carbon $dateFrom, Carbon $dateTo): void
    {
        $originalDateTo = $dateTo->format('Y-m-d');

        // For API add +1 day
        $apiDateTo = $dateTo->copy()->addDay()->format('Y-m-d');

        Log::info("Starting orders sync", [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $originalDateTo,
        ]);

        // Prepare API request parameters
        $params = [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $apiDateTo,
        ];

        // Track the number of processed records
        $processed = 0;

        // Record time for logging
        $startTime = microtime(true);

        DB::transaction(function () use ($params, $dateFrom, $dateTo) {
            // Clear the orders table for the specified date range before inserting new data
            Order::whereBetween('date', [$dateFrom, $dateTo])->delete(); // The table doesn't have any unique composite fields, so I use full delete by date and full insert to ensure data integrity
        });

        // Iterate through paginated API data
        foreach ($this->apiService->fetchPaginatedData('orders', $params) as $pageIndex => $pageData) {
            try {
                // Prepare data for database insertion
                $preparedData = $this->prepareOrdersData($pageData);

                DB::transaction(function () use ($preparedData) {
                    // Insert new data
                    Order::insert($preparedData);
                });

                $processed += count($preparedData);

                // Log progress every 10 pages for large imports
                if ($pageIndex % 10 === 0) {
                    $this->logProgress($pageIndex, $processed, $startTime);
                }
            } catch (\Throwable $e) {
                Log::error('Orders sync error', [
                    'page' => $pageIndex,
                    'error' => $e->getMessage(),
                    'sample_data' => $pageData[0] ?? null,
                ]);
                throw $e;
            }

            // Clear memory by unsetting temporary variables
            unset($preparedData, $pageData);
        }

        $this->logFinalResults($processed, $startTime, 'Orders');
    }

    /**
     * Prepares raw API orders data for database insertion.
     *
     * @param array $pageData Array of orders data from the API.
     * @return array Formatted data ready for insertion.
     */
    protected function prepareOrdersData(array $pageData): array
    {
        $now = now();
        $preparedData = [];

        foreach ($pageData as $item) {
            $preparedData[] = [
                'g_number' => $item['g_number'],
                'date' => $item['date'],
                'last_change_date' => $item['last_change_date'] ?? null,
                'supplier_article' => $item['supplier_article'] ?? null,
                'tech_size' => $item['tech_size'] ?? null,
                'barcode' => $item['barcode'] ?? null,
                'total_price' => $item['total_price'] ?? 0,
                'discount_percent' => $item['discount_percent'] ?? 0,
                'warehouse_name' => $item['warehouse_name'],
                'oblast' => $item['oblast'] ?? null,
                'income_id' => $item['income_id'] ?? null,
                'odid' => $item['odid'] ?? null,
                'nm_id' => $item['nm_id'] ?? null,
                'subject' => $item['subject'] ?? null,
                'category' => $item['category'] ?? null,
                'brand' => $item['brand'] ?? null,
                'is_cancel' => $item['is_cancel'] ?? false,
                'cancel_dt' => $item['cancel_dt'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $preparedData;
    }
}