<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePurchaseRequest;
use App\Http\Resources\BatchResource;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseService $purchaseService) {}

    /**
     * Make purchase from providers and keep it into storage
     *
     * @param StorePurchaseRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(StorePurchaseRequest $request): JsonResponse
    {
        $batch = $this->purchaseService->purchase($request->validated());

        return (new BatchResource($batch))
            ->response()
            ->setStatusCode(201);
    }
}
