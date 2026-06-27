<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\OrderItem */
class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'batch_item_id' => $this->batch_item_id,
            'quantity' => $this->quantity,
            'unit_price' => number_format((float) $this->unit_price, 2, '.', ''),
        ];
    }
}
