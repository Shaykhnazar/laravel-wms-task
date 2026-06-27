<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BatchProfitResource;
use App\Services\ReportService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BatchProfitController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function index(): AnonymousResourceCollection
    {
        return BatchProfitResource::collection(
            $this->reportService->profitPerBatch()
        );
    }
}
