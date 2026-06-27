<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Batch */
class BatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'storage_id' => $this->storage_id,
            'purchased_at' => $this->purchased_at?->toIso8601String(),
            'items' => BatchItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
