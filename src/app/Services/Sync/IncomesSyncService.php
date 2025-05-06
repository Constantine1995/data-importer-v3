<?php

namespace App\Services\Sync;

use App\Models\Income;
use App\Models\Sale;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncomesSyncService extends LogSyncService
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Synchronizes incomes data from the API for a given date range.
     *
     * @param Carbon $dateFrom Start date for the sync period.
     * @param Carbon $dateTo End date for the sync period.
     * @return void
     */
    public function sync(Carbon $dateFrom, Carbon $dateTo): void
    {
        // Prepare API request parameters
        $params = [
            'dateFrom' => $dateFrom->format('Y-m-d'),
            'dateTo' => $dateTo->format('Y-m-d'),
        ];

        // Track the number of processed records
        $processed = 0;

        // Record time for logging
        $startTime = microtime(true);

        DB::transaction(function () use ($params) {
            // Clear the incomes table before inserting new data
            Income::whereBetween('date', [$params['dateFrom'], $params['dateTo']])->delete();
        });

        // Iterate through paginated API data
        foreach ($this->apiService->fetchPaginatedData('incomes', $params) as $pageIndex => $pageData) {
            try {
                // Prepare data for database insertion
                $preparedData = $this->prepareIncomesData($pageData);

                DB::transaction(function () use ($preparedData) {
                    // Insert new data
                    Income::insert($preparedData);
                });

                $processed += count($preparedData);

                // Log progress every 10 pages for large imports
                if ($pageIndex % 10 === 0) {
                    $this->logProgress($pageIndex, $processed, $startTime);
                }
            } catch (\Throwable $e) {
                Log::error('Incomes sync error', [
                    'page' => $pageIndex,
                    'error' => $e->getMessage(),
                    'sample_data' => $pageData[0] ?? null,
                ]);
                throw $e;
            }

            // Clear memory by unsetting temporary variables
            unset($preparedData, $pageData);
        }

        $this->logFinalResults($processed, $startTime, 'Incomes');
    }

    /**
     * Prepares raw API incomes data for database insertion.
     *
     * @param array $pageData Array of incomes data from the API.
     * @return array Formatted data ready for insertion.
     */
    protected function prepareIncomesData(array $pageData): array
    {
        $now = now();
        $preparedData = [];

        foreach ($pageData as $item) {
            $preparedData[] = [
                'income_id' => $item['income_id'],
                'number' => $item['number'] ?? null,
                'date' => $item['date'],
                'last_change_date' => $item['last_change_date'] ?? null,
                'supplier_article' => $item['supplier_article'] ?? null,
                'tech_size' => $item['tech_size'] ?? null,
                'barcode' => $item['barcode'] ?? null,
                'quantity' => $item['quantity'] ?? 0,
                'total_price' => $item['total_price'] ?? 0,
                'date_close' => $item['date_close'] ?? null,
                'warehouse_name' => $item['warehouse_name'],
                'nm_id' => $item['nm_id'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        return $preparedData;
    }
}