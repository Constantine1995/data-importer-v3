<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SaleApiRequest;
use App\Http\Resources\SaleCollection;
use App\Repositories\SaleRepository;

class SaleApiController extends Controller
{
    public function __construct(private SaleRepository $repository)
    {
    }

    /**
     *  Handle the sale API request for sale data
     * @param SaleApiRequest $request
     * @return SaleCollection
     */
    public function __invoke(SaleApiRequest $request): SaleCollection
    {
        // Prepare query parameters from the request
        $params = [
            'account_id' => $request->input('account_id'),
            'dateFrom' => $request->validated('dateFrom'),
            'dateTo' => $request->validated('dateTo'),
            'limit' => (int)($request->validated('limit') ?? 100),
            'offset' => (int)($request->validated('offset') ?? 0),
        ];

        // Get paginated sale data from repository
        $data = $this->repository->getSalesByAccount($params);

        // Return formatted JSON response using SaleCollection resource
        return new SaleCollection($data);
    }
}
