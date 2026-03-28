<?php

namespace App\Http\Requests\Admin;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('users.create');
    }

    public function rules(): array
    {
        $tenantId = $this->integer('tenant_id') ?: Branch::whereKey($this->integer('branch_id'))->value('tenant_id');

        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required', 'string', 'max:50', 'min:3',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('users', 'username')->where(fn ($query) => $query->where('tenant_id', $tenantId))->withoutTrashed(),
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('tenant_id', $tenantId))->withoutTrashed(),
            ],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role_id' => ['required', 'exists:roles,id'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, dan underscore (_).',
        ];
    }

    public function attributes(): array
    {
        return [
            'role_id' => 'role',
            'tenant_id' => 'tenant',
            'branch_id' => 'cabang',
        ];
    }

    protected function prepareForValidation(): void
    {
        $branchId = $this->integer('branch_id');
        if ($branchId && ! $this->filled('tenant_id')) {
            $tenantId = Branch::whereKey($branchId)->value('tenant_id');
            if ($tenantId) {
                $this->merge(['tenant_id' => $tenantId]);
            }
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $roleId = $this->input('role_id');
            if (! $roleId) {
                return;
            }

            $role = Role::find($roleId);
            if (! $role) {
                return;
            }

            $roleEnum = $role->name;
            $tenantId = $this->integer('tenant_id');
            $branchId = $this->integer('branch_id');

            if ($roleEnum === \App\Enums\RoleEnum::SuperAdmin && ($tenantId || $branchId)) {
                $validator->errors()->add('tenant_id', 'Role Super Admin tidak terikat tenant atau cabang.');
            }

            if ($roleEnum !== \App\Enums\RoleEnum::SuperAdmin && ! $tenantId) {
                $validator->errors()->add('tenant_id', "Role {$roleEnum->label()} wajib memilih tenant.");
            }

            if ($roleEnum->requiresBranch() && ! $branchId) {
                $validator->errors()->add('branch_id', "Role {$roleEnum->label()} wajib memilih cabang.");
            }

            if ($roleEnum->isGlobal() && $roleEnum !== \App\Enums\RoleEnum::SuperAdmin && $branchId) {
                $validator->errors()->add('branch_id', "Role {$roleEnum->label()} tidak perlu memilih cabang.");
            }

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant && ! $tenant->is_active) {
                    $validator->errors()->add('tenant_id', 'Tenant yang dipilih tidak aktif.');
                }

                if (! $this->user()->isPlatformAdmin() && ! $this->user()->canAccessTenant($tenantId)) {
                    $validator->errors()->add('tenant_id', 'Anda hanya dapat memilih tenant Anda sendiri.');
                }
            }

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

            if ($tenantId && $branch->tenant_id !== $tenantId) {
                $validator->errors()->add('branch_id', 'Cabang yang dipilih tidak berada di tenant yang sama.');
            }

            if (! $this->user()->isGlobal() && ! $this->user()->canAccessBranch($branchId)) {
                $validator->errors()->add('branch_id', 'Anda hanya dapat memilih cabang Anda sendiri.');
            }
        });
    }
}
