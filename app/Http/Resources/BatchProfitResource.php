<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchProfitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'batch_id' => $this->batch_id,
            'cost' => number_format((float) $this->cost, 2, '.', ''),
            'revenue' => number_format((float) $this->revenue, 2, '.', ''),
            'profit' => number_format((float) $this->profit, 2, '.', ''),
        ];
    }
}
