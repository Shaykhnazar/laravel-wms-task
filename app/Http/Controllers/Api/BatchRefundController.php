<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBatchRefundRequest;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use App\Services\BatchRefundService;
use Illuminate\Http\JsonResponse;

class BatchRefundController extends Controller
{
    public function __construct(private readonly BatchRefundService $batchRefundService) {}

    /**
     * Refund purchased and unsold products on batches
     *
     * @param StoreBatchRefundRequest $request
     * @param Batch $batch
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(StoreBatchRefundRequest $request, Batch $batch): JsonResponse
    {
        $batch = $this->batchRefundService->refund($batch, $request->validated());

        return (new BatchResource($batch))->response();
    }
}
