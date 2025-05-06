<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IncomeApiRequest;
use App\Http\Resources\IncomeCollection;
use App\Repositories\IncomeRepository;

class IncomeApiController extends Controller
{
    public function __construct(private IncomeRepository $repository)
    {
    }

    /**
     *  Handle the incoming API request for income data
     * @param IncomeApiRequest $request
     * @return IncomeCollection
     */
    public function __invoke(IncomeApiRequest $request): IncomeCollection
    {
        // Prepare query parameters from the request
        $params = [
            'account_id' => $request->input('account_id'),
            'dateFrom' => $request->validated('dateFrom'),
            'dateTo' => $request->validated('dateTo'),
            'limit' => (int)($request->validated('limit') ?? 100),
            'offset' => (int)($request->validated('offset') ?? 0),
        ];

        // Get paginated income data from repository
        $data = $this->repository->getIncomesByAccount($params);

        // Return formatted JSON response using IncomeCollection resource
        return new IncomeCollection($data);
    }
}
