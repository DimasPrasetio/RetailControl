@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')

{{-- ── Back link ────────────────────────────────────────────────────────── --}}
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-blue-600">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Daftar User
    </a>
</div>

{{-- ── Form card ────────────────────────────────────────────────────────── --}}
<div class="max-w-2xl overflow-hidden rounded-2xl bg-white shadow-xl shadow-indigo-500/10 border border-slate-300">

    {{-- Card header with user avatar --}}
    <div class="border-b border-gray-100 px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-sm font-bold text-white">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ '@' . $user->username }} · {{ $user->role->name->label() }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="divide-y divide-gray-100">
        @csrf
        @method('PUT')

        {{-- Section: Identitas ────────────────────────────────────────── --}}
        <div class="px-6 py-5 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Identitas</p>

            {{-- Nama --}}
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900
                              transition placeholder-gray-400
                              focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20
                              @error('name') border-red-400 bg-red-50 @enderror"
                       placeholder="Nama lengkap pengguna">
                @error('name')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Username --}}
            <div>
                <label for="username" class="mb-1.5 block text-sm font-medium text-gray-700">Username</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 text-sm">@</span>
                    <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required
                           pattern="^[a-z0-9_]+" minlength="3" maxlength="50"
                           class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-8 pr-4 text-sm text-gray-900
                                  font-mono transition placeholder-gray-400
                                  focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20
                                  @error('username') border-red-400 bg-red-50 @enderror">
                </div>
                @error('username')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email <span class="text-xs font-normal text-gray-500">(opsional)</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                       class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900
                              transition placeholder-gray-400
                              focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20
                              @error('email') border-red-400 bg-red-50 @enderror"
                       placeholder="email@contoh.com">
                @error('email')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        {{-- Section: Ganti Password ───────────────────────────────────── --}}
        <div class="px-6 py-5 space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Ganti Password</p>
                <span class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</span>
            </div>

            {{-- Password baru --}}
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700">Password Baru</label>
                <div class="relative">
                    <input type="password" id="password" name="password"
                           class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 pr-11 text-sm text-gray-900
                                  transition placeholder-gray-400
                                  focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20
                                  @error('password') border-red-400 bg-red-50 @enderror"
                           placeholder="Min. 8 karakter (huruf + angka)">
                    <button type="button" onclick="togglePwd('password', 'eye-edit')"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-500 transition hover:text-gray-700">
                        <svg id="eye-edit" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Konfirmasi --}}
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900
                              transition placeholder-gray-400
                              focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20"
                       placeholder="Ulangi password baru">
            </div>
        </div>

        {{-- Section: Role & Akses ─────────────────────────────────────── --}}
        <div class="px-6 py-5 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Role & Akses</p>

            {{-- Role --}}
            <div>
                <label for="role_id" class="mb-1.5 block text-sm font-medium text-gray-700">Role</label>
                <select id="role_id" name="role_id" required
                        onchange="toggleBranchField(this)"
                        class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900
                               transition
                               focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20
                               @error('role_id') border-red-400 bg-red-50 @enderror">
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
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Branch ID (conditional) --}}
            <div id="branch-field" class="hidden">
                <label for="branch_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                    ID Cabang
                    <span class="ml-1 text-xs font-normal text-orange-500">* wajib untuk role ini</span>
                </label>
                <input type="number" id="branch_id" name="branch_id"
                       value="{{ old('branch_id', $user->branch_id) }}" min="1"
                       class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900
                              transition placeholder-gray-400
                              focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20
                              @error('branch_id') border-red-400 bg-red-50 @enderror"
                       placeholder="ID cabang">
                @error('branch_id')
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Status aktif --}}
            <div class="flex items-start gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                <div class="flex h-5 items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-400/30">
                </div>
                <div>
                    <label for="is_active" class="cursor-pointer text-sm font-medium text-gray-700">Akun aktif</label>
                    <p class="text-xs text-gray-500">Nonaktifkan untuk memblokir akses login pengguna</p>
                </div>
            </div>
        </div>

        {{-- Footer actions ────────────────────────────────────────────── --}}
        <div class="flex items-center justify-end gap-3 bg-gray-50/70 px-6 py-4">
            <a href="{{ route('admin.users.index') }}"
               class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:text-gray-900">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Perbarui User
            </button>
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

function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
    } else {
        input.type = 'password';
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('role_id');
    if (select) toggleBranchField(select);
});
</script>

@endsection
