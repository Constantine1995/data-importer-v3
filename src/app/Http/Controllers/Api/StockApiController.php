<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StockApiRequest;
use App\Http\Resources\StockCollection;
use App\Repositories\StockRepository;

class StockApiController extends Controller
{
    public function __construct(private StockRepository $repository)
    {
    }

    /**
     *  Handle the stocking API request for stock data
     * @param StockApiRequest $request
     * @return StockCollection
     */
    public function __invoke(StockApiRequest $request): StockCollection
    {
        // Prepare query parameters from the request
        $params = [
            'account_id' => $request->input('account_id'),
            'dateFrom' => $request->validated('dateFrom'),
            'dateTo' => $request->validated('dateTo'),
            'limit' => (int)($request->validated('limit') ?? 100),
            'offset' => (int)($request->validated('offset') ?? 0),
        ];

        // Get paginated stock data from repository
        $data = $this->repository->getStocksByAccount($params);

        // Return formatted JSON response using StockCollection resource
        return new StockCollection($data);
    }
}