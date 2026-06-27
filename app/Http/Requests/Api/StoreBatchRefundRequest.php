<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'refunded_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.batch_item_id' => ['required', 'integer', 'exists:batch_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
