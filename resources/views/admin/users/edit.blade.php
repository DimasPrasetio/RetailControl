@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline">
        ← Kembali ke Daftar User
    </a>
    <h2 class="text-xl font-semibold text-gray-800 mt-2">Edit User: {{ $user->name }}</h2>
</div>

<div class="bg-white rounded-lg border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        {{-- Nama --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Username --}}
        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required
                pattern="^[a-z0-9_]+" minlength="3" maxlength="50"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('username') border-red-500 @enderror">
            @error('username')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password (opsional) --}}
        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                Password Baru
                <span class="text-xs text-gray-400 font-normal ml-1">(kosongkan jika tidak diubah)</span>
            </label>
            <input type="password" id="password" name="password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                placeholder="Min. 8 karakter (huruf + angka)">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Konfirmasi Password --}}
        <div class="mb-4">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Role --}}
        <div class="mb-4">
            <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select id="role_id" name="role_id" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('role_id') border-red-500 @enderror"
                onchange="toggleBranchField(this)">
                <option value="">— Pilih role —</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}"
                        data-requires-branch="{{ $role->name->requiresBranch() ? 'true' : 'false' }}"
                        {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                        {{ $role->name->label() }}
                    </option>
                @endforeach
            </select>
            @error('role_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Branch ID --}}
        <div id="branch-field" class="mb-4 hidden">
            <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">
                ID Cabang
                <span class="text-xs text-gray-400 font-normal ml-1">(wajib untuk role ini)</span>
            </label>
            <input type="number" id="branch_id" name="branch_id"
                value="{{ old('branch_id', $user->branch_id) }}"
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('branch_id') border-red-500 @enderror">
            @error('branch_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Status --}}
        <div class="mb-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <span class="text-sm text-gray-700">Akun aktif</span>
            </label>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition">
                Perbarui
            </button>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function toggleBranchField(select) {
    const option = select.options[select.selectedIndex];
    const requiresBranch = option && option.dataset.requiresBranch === 'true';
    const branchField = document.getElementById('branch-field');
    const branchInput = document.getElementById('branch_id');

    if (requiresBranch) {
        branchField.classList.remove('hidden');
        branchInput.required = true;
    } else {
        branchField.classList.add('hidden');
        branchInput.required = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('role_id');
    if (select) toggleBranchField(select);
});
</script>
@endsection
