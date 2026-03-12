<?php

namespace App\Http\Requests\Admin;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('warehouses.create');
    }

    protected function prepareForValidation(): void
    {
        $branchId = $this->integer('branch_id');
        if ($branchId) {
            $tenantId = Branch::whereKey($branchId)->value('tenant_id');
            if ($tenantId) {
                $this->merge(['tenant_id' => $tenantId]);
            }
        }

        if ($this->filled('warehouse_code')) {
            $this->merge([
                'warehouse_code' => strtoupper(trim((string) $this->input('warehouse_code'))),
            ]);
        }
    }

    public function rules(): array
    {
        $tenantId = $this->integer('tenant_id');

        return [
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'warehouse_code' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('warehouses', 'warehouse_code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['MAIN', 'SECONDARY', 'RETURNS'])],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $branchId = $this->integer('branch_id');
            if (! $branchId) {
                return;
            }

            $branch = Branch::find($branchId);
            if (! $branch) {
                return;
            }

            if (! $branch->is_active) {
                $validator->errors()->add('branch_id', 'Cabang yang dipilih tidak aktif.');
            }

            if ($this->integer('tenant_id') !== $branch->tenant_id) {
                $validator->errors()->add('tenant_id', 'Tenant gudang harus sama dengan tenant cabang.');
            }

            if (! $this->user()->isGlobal() && ! $this->user()->canAccessBranch($branchId)) {
                $validator->errors()->add('branch_id', 'Anda hanya dapat memilih cabang Anda sendiri.');
            }
        });
    }
}
