<?php

namespace App\Http\Requests\Admin;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('branches.create');
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('tenant_id') && ! $this->user()->isPlatformAdmin()) {
            $tenantId = $this->user()->tenant_id;

            if ($tenantId) {
                $this->merge(['tenant_id' => $tenantId]);
            }
        }

        if ($this->filled('branch_code')) {
            $this->merge([
                'branch_code' => strtoupper(trim((string) $this->input('branch_code'))),
            ]);
        }
    }

    public function rules(): array
    {
        $tenantId = $this->integer('tenant_id');

        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'branch_code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('branches', 'branch_code')->where(fn ($query) => $query->where('tenant_id', $tenantId))->withoutTrashed(),
            ],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $tenant = Tenant::find($this->integer('tenant_id'));
            if (! $tenant) {
                return;
            }

            if (! $tenant->is_active) {
                $validator->errors()->add('tenant_id', 'Tenant yang dipilih tidak aktif.');
            }

            if (! $this->user()->isPlatformAdmin() && ! $this->user()->canAccessTenant($tenant->id)) {
                $validator->errors()->add('tenant_id', 'Anda hanya dapat memilih tenant Anda sendiri.');
            }
        });
    }
}
