<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductAvailabilityResource;
use App\Services\ReportService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    /**
     * List of available products
     *
     * @return AnonymousResourceCollection
     */
    public function available(): AnonymousResourceCollection
    {
        return ProductAvailabilityResource::collection(
            $this->reportService->availableProducts()
        );
    }
}
