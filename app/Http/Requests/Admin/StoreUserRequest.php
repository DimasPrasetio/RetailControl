<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('users.create');
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'username'  => [
                'required', 'string', 'max:50', 'min:3',
                'regex:/^[a-z0-9_]+$/',
                'unique:users,username',
            ],
            'email'     => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password'  => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role_id'   => ['required', 'exists:roles,id'],
            'branch_id' => ['nullable', 'integer'],
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
            'role_id'   => 'role',
            'branch_id' => 'cabang',
        ];
    }

    /**
     * Validasi tambahan setelah rules dasar lolos:
     * - Role yang membutuhkan cabang harus ada branch_id
     * - branch_id tidak boleh diisi jika role adalah global (super_admin/owner)
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $roleId = $this->input('role_id');
            if (! $roleId) {
                return;
            }

            $role = \App\Models\Role::find($roleId);
            if (! $role) {
                return;
            }

            $roleEnum = $role->name;

            if ($roleEnum->requiresBranch() && ! $this->input('branch_id')) {
                $validator->errors()->add('branch_id', "Role {$roleEnum->label()} wajib memilih cabang.");
            }

            if ($roleEnum->isGlobal() && $this->input('branch_id')) {
                $validator->errors()->add('branch_id', "Role {$roleEnum->label()} tidak perlu memilih cabang.");
            }
        });
    }
}
