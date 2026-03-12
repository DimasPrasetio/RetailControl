<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('warehouses.update');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['MAIN', 'SECONDARY', 'RETURNS'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
