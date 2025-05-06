<?php

namespace App\Services\Sync;

use App\Models\Stock;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StocksSyncService extends LogSyncService
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Synchronizes stocks data from the API for a given date.
     *
     * @param Carbon $date Date for the sync period.
     * @return void
     */
    public function sync(Carbon $date): void
    {
        // Prepare API request parameters
        $params = [
            'dateFrom' => $date->format('Y-m-d'),
        ];

        // Track the number of processed records
        $processed = 0;

        // Record time for logging
        $startTime = microtime(true);

        DB::transaction(function () use ($params) {
            // Clear the stocks table before inserting new data
            Stock::whereBetween('date', [$params['dateFrom'], $params['dateFrom']])->delete();
        });

        // Iterate through paginated API data
        foreach ($this->apiService->fetchPaginatedData('stocks', $params) as $pageIndex => $pageData) {
            try {
                // Prepare data for database insertion
                $preparedData = $this->prepareStocksData($pageData);

                DB::transaction(function () use ($preparedData) {
                    // Insert new data
                    Stock::insert($preparedData);
                });

                $processed += count($preparedData);

                // Log progress every 10 pages for large imports
                if ($pageIndex % 10 === 0) {
                    $this->logProgress($pageIndex, $processed, $startTime);
                }

            } catch (\Throwable $e) {
                Log::error('Stocks sync error', [
                    'page' => $pageIndex,
                    'error' => $e->getMessage(),
                    'sample_data' => $pageData[0] ?? null,
                ]);
                throw $e;
            }

            // Clear memory by unsetting temporary variables
            unset($preparedData, $pageData);
        }

        $this->logFinalResults($processed, $startTime, 'Stocks');
    }

    /**
     * Prepares raw API stocks data for database insertion.
     *
     * @param array $pageData Array of sales data from the API.
     * @return array Formatted data ready for upsert.
     */
    protected function prepareStocksData(array $pageData): array
    {
        // Get current timestamp
        $now = now();
        $preparedData = [];

        foreach ($pageData as $item) {
            $preparedData[] = [
                'nm_id' => $item['nm_id'] ?? null,
                'warehouse_name' => $item['warehouse_name'] ?? null,
                'date' => $item['date'] ?? null,
                'last_change_date' => $item['last_change_date'] ?? null,
                'supplier_article' => $item['supplier_article'] ?? null,
                'tech_size' => $item['tech_size'] ?? null,
                'barcode' => $item['barcode'] ?? null,
                'quantity' => $item['quantity'] ?? 0,
                'is_supply' => $item['is_supply'] ?? false,
                'is_realization' => $item['is_realization'] ?? false,
                'quantity_full' => $item['quantity_full'] ?? 0,
                'in_way_to_client' => $item['in_way_to_client'] ?? 0,
                'in_way_from_client' => $item['in_way_from_client'] ?? 0,
                'subject' => $item['subject'] ?? null,
                'category' => $item['category'] ?? null,
                'brand' => $item['brand'] ?? null,
                'sc_code' => $item['sc_code'] ?? null,
                'price' => $item['price'] ?? 0,
                'discount' => $item['discount'] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $preparedData;
    }
}