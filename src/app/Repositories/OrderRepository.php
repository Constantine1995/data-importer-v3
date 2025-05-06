<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository
{
    /**
     *  Get paginated order records filtered by account and optional date range
     *  @param array $params
     * @return LengthAwarePaginator
     */
    public function getOrdersByAccount(array $params): LengthAwarePaginator
    {
        // Filter orders that belong to the specified account
        $query = Order::whereHas('accounts', fn($q) => $q->where('account_id', $params['account_id']));

        if ($params['dateFrom'] ?? null) {
            $query->whereDate('date', '>=', $params['dateFrom']);
        }

        if ($params['dateTo'] ?? null) {
            $query->whereDate('date', '<=', $params['dateTo']);
        }

        // Calculate current page number based on offset and limit
        $page = (int)($params['offset'] / $params['limit']) + 1;

        // Execute the query with pagination
        return $query->paginate(
            $params['limit'],     // Number of items per page
            ['*'],                // Columns to select (all columns)
            'page',               // Query string parameter name for page number
            $page                 // Current page number
        );
    }
}
