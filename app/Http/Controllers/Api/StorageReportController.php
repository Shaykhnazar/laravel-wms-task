<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RemainingQuantityRequest;
use App\Http\Resources\RemainingQuantityResource;
use App\Services\ReportService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class StorageReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function remaining(RemainingQuantityRequest $request): AnonymousResourceCollection
    {
        $date = Carbon::parse($request->validated('date'));

        return RemainingQuantityResource::collection(
            $this->reportService->remainingQuantities($date)
        );
    }
}
