<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create(
            $request->integer('client_id'),
            $request->validated('products'),
        );

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }
}
