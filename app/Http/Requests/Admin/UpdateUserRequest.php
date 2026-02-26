<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('users.update');
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'      => ['required', 'string', 'max:100'],
            'username'  => [
                'required', 'string', 'max:50', 'min:3',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($userId)->whereNull('deleted_at'),
            ],
            'email'     => [
                'nullable', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at'),
            ],
            'password'  => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'role_id'   => ['required', 'exists:roles,id'],
            'branch_id' => ['nullable', 'integer'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username hanya boleh berisi huruf kecil, angka, dan underscore (_).',
        ];
    }

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
