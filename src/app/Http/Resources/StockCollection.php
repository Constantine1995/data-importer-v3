<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class StockCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'limit' => $this->perPage(),
                'offset' => ($this->currentPage() - 1) * $this->perPage(),
                'total' => $this->total(),
            ]
        ];
    }
}