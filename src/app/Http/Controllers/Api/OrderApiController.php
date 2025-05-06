<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderApiRequest;
use App\Http\Resources\OrderCollection;
use App\Repositories\OrderRepository;

class OrderApiController extends Controller
{
    public function __construct(private OrderRepository $repository)
    {
    }

    /**
     *  Handle the ordering API request for order data
     * @param OrderApiRequest $request
     * @return OrderCollection
     */
    public function __invoke(OrderApiRequest $request): OrderCollection
    {
        // Prepare query parameters from the request
        $params = [
            'account_id' => $request->input('account_id'),
            'dateFrom' => $request->validated('dateFrom'),
            'dateTo' => $request->validated('dateTo'),
            'limit' => (int)($request->validated('limit') ?? 100),
            'offset' => (int)($request->validated('offset') ?? 0),
        ];

        // Get paginated order data from repository
        $data = $this->repository->getOrdersByAccount($params);

        // Return formatted JSON response using OrderCollection resource
        return new OrderCollection($data);
    }
}