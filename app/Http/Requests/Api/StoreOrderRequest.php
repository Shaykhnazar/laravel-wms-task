<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'integer', 'exists:products,id'],
            'products.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }
}
