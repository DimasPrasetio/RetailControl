<?php

namespace App\Http\Requests\Admin;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('branches.update');
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('branch')) {
            $this->merge([
                '_submitted_tenant_id' => $this->input('tenant_id'),
                'tenant_id' => $this->route('branch')->tenant_id,
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $branch = $this->route('branch');
            $submittedTenantId = $this->input('_submitted_tenant_id');
            if ($branch && $submittedTenantId !== null && (int) $submittedTenantId !== (int) $branch->tenant_id) {
                $validator->errors()->add('tenant_id', 'Tenant cabang tidak dapat diubah setelah cabang dibuat.');
                return;
            }

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
