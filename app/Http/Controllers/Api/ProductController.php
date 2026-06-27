<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductAvailabilityResource;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    /**
     * List of available products
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function available(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return ProductAvailabilityResource::collection(
            $this->reportService->availableProducts($perPage)
        );
    }
}
