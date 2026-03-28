@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-600">Kelola akun dan hak akses pengguna sistem</p>
    @can('create', \App\Models\User::class)
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/25 transition hover:from-blue-500 hover:to-indigo-500 hover:shadow-lg hover:shadow-blue-500/30">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah User
        </a>
    @endcan
</div>

<div class="mb-5 rounded-2xl border border-slate-300 bg-white p-4 shadow-xl shadow-indigo-500/10">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <label class="mb-1 block text-xs font-medium text-gray-600">Pencarian</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / username..."
                   class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition placeholder:text-gray-500 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
        </div>
        <div class="min-w-[160px]">
            <label class="mb-1 block text-xs font-medium text-gray-600">Role</label>
            <select name="role_id"
                    class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->name->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        @if(auth()->user()->isGlobal())
            <div class="min-w-[190px]">
                <label class="mb-1 block text-xs font-medium text-gray-600">Cabang</label>
                <select name="branch_id"
                        class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->branch_code }} - {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="min-w-[130px]">
            <label class="mb-1 block text-xs font-medium text-gray-600">Status</label>
            <select name="active"
                    class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                <option value="">Semua</option>
                <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-gray-800 to-gray-700 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-gray-400/25 transition hover:from-gray-700 hover:to-gray-600 hover:shadow-lg">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Filter
            </button>
            @if(request()->hasAny(['q', 'role_id', 'branch_id', 'active']))
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-800">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead>
                <tr class="bg-gradient-to-r from-gray-50 to-slate-50">
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Pengguna</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Username</th>
                    <th class="hidden px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 md:table-cell">Email</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Role</th>
                    <th class="hidden px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 lg:table-cell">Cabang</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                    <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($users as $user)
                    <tr class="transition-colors duration-150 hover:bg-blue-50/40">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-xs font-semibold text-white">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-gray-700">{{ $user->username }}</td>
                        <td class="hidden px-5 py-4 text-gray-600 md:table-cell">{{ $user->email ?? '-' }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200/60">
                                {{ $user->role->name->label() }}
                            </span>
                        </td>
                        <td class="hidden px-5 py-4 text-gray-600 lg:table-cell">
                            @if ($user->branch)
                                <div class="font-medium text-gray-800">{{ $user->branch->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->branch->branch_code }}</div>
                            @else
                                <span class="text-gray-400">Global</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($user->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/60">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-red-200/60">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                @can('update', $user)
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm shadow-amber-100/50 transition hover:bg-amber-100 hover:shadow-md hover:shadow-amber-200/50">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                @endcan
                                @if($user->is_active)
                                    @can('deactivate', $user)
                                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}"
                                              onsubmit="confirmAction(event, this, 'Konfirmasi Status', 'Nonaktifkan user {{ addslashes($user->name) }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 shadow-sm shadow-orange-100/50 transition hover:bg-orange-100 hover:shadow-md hover:shadow-orange-200/50">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                                @can('delete', $user)
                                    <form id="delete-user-{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:none;">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button type="button"
                                        onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { formId: 'delete-user-{{ $user->id }}', itemName: '{{ addslashes($user->name) }}' } }))"
                                        class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-600">Belum ada pengguna terdaftar</p>
                            @can('create', \App\Models\User::class)
                                <a href="{{ route('admin.users.create') }}"
                                   class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-500">
                                    + Tambah pengguna pertama
                                </a>
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="border-t border-gray-100 px-5 py-3">{{ $users->links() }}</div>
    @endif
</div>

@endsection
