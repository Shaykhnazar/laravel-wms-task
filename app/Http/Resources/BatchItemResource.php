<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\BatchItem */
class BatchItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'purchase_price' => number_format((float) $this->purchase_price, 2, '.', ''),
            'quantity' => $this->quantity,
            'quantity_remaining' => $this->quantity_remaining,
        ];
    }
}
