@extends('layouts.app')

@section('title', 'Detail Cabang')
@section('page-title', 'Detail Cabang')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('admin.branches.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-blue-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Cabang
        </a>
        <h2 class="mt-3 text-xl font-bold text-gray-900">{{ $branch->name }}</h2>
        <p class="text-sm text-gray-500">{{ $branch->branch_code }} · {{ $branch->timezone ?: 'Asia/Jakarta' }}</p>
    </div>
    @can('update', $branch)
        <a href="{{ route('admin.branches.edit', $branch) }}"
           class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500">
            Edit Cabang
        </a>
    @endcan
</div>

<div class="mb-6 grid gap-4 md:grid-cols-3">
    <div class="rounded-2xl border border-slate-300 bg-white p-5 shadow-xl shadow-indigo-500/10">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Status</p>
        <p class="mt-2 text-lg font-semibold text-gray-900">{{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}</p>
        <p class="mt-1 text-xs text-gray-500">Telepon: {{ $branch->phone ?: '-' }}</p>
    </div>
    <div class="rounded-2xl border border-slate-300 bg-white p-5 shadow-xl shadow-indigo-500/10">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">User Cabang</p>
        <p class="mt-2 text-lg font-semibold text-gray-900">{{ $branch->users->count() }}</p>
        <p class="mt-1 text-xs text-gray-500">User aktif dan nonaktif yang terhubung</p>
    </div>
    <div class="rounded-2xl border border-slate-300 bg-white p-5 shadow-xl shadow-indigo-500/10">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Gudang</p>
        <p class="mt-2 text-lg font-semibold text-gray-900">{{ $branch->warehouses->count() }}</p>
        <p class="mt-1 text-xs text-gray-500">Termasuk gudang utama otomatis</p>
    </div>
</div>

<div class="mb-6 rounded-2xl border border-slate-300 bg-white p-6 shadow-xl shadow-indigo-500/10">
    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-600">Informasi Cabang</h3>
    <dl class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <dt class="text-xs font-medium text-gray-500">Alamat</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $branch->address ?: '-' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Catatan</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $branch->notes ?: '-' }}</dd>
        </div>
    </dl>
</div>

<div class="mb-6 rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
    <div class="border-b border-gray-100 px-6 py-4">
        <h3 class="text-sm font-semibold text-gray-900">Gudang Cabang</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Gudang</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($branch->warehouses as $warehouse)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $warehouse->name }}</div>
                            <div class="text-xs font-mono text-gray-500">{{ $warehouse->warehouse_code }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $warehouse->type }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada gudang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
    <div class="border-b border-gray-100 px-6 py-4">
        <h3 class="text-sm font-semibold text-gray-900">User Terhubung</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($branch->users as $user)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ '@' . $user->username }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->role->name->label() }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada user pada cabang ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
