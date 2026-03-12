@extends('layouts.app')

@section('title', 'Detail Gudang')
@section('page-title', 'Detail Gudang')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('admin.warehouses.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-blue-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Gudang
        </a>
        <h2 class="mt-3 text-xl font-bold text-gray-900">{{ $warehouse->name }}</h2>
        <p class="text-sm text-gray-500">{{ $warehouse->warehouse_code }} · {{ $warehouse->type }}</p>
    </div>
    @can('update', $warehouse)
        <a href="{{ route('admin.warehouses.edit', $warehouse) }}"
           class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500">
            Edit Gudang
        </a>
    @endcan
</div>

<div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-2xl border border-slate-300 bg-white p-5 shadow-xl shadow-indigo-500/10">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Cabang</p>
        <p class="mt-2 text-lg font-semibold text-gray-900">{{ $warehouse->branch?->name }}</p>
        <p class="mt-1 text-xs text-gray-500">{{ $warehouse->branch?->branch_code }}</p>
    </div>
    <div class="rounded-2xl border border-slate-300 bg-white p-5 shadow-xl shadow-indigo-500/10">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Tipe</p>
        <p class="mt-2 text-lg font-semibold text-gray-900">{{ $warehouse->type }}</p>
        <p class="mt-1 text-xs text-gray-500">Gudang utama, sekunder, atau retur</p>
    </div>
    <div class="rounded-2xl border border-slate-300 bg-white p-5 shadow-xl shadow-indigo-500/10">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Status</p>
        <p class="mt-2 text-lg font-semibold text-gray-900">{{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}</p>
        <p class="mt-1 text-xs text-gray-500">{{ $warehouse->type === 'MAIN' ? 'Gudang utama tidak bisa dinonaktifkan.' : 'Status dapat diubah dari daftar gudang.' }}</p>
    </div>
</div>

<div class="mt-6 rounded-2xl border border-slate-300 bg-white p-6 shadow-xl shadow-indigo-500/10">
    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-600">Informasi Gudang</h3>
    <dl class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <dt class="text-xs font-medium text-gray-500">Kode Gudang</dt>
            <dd class="mt-1 text-sm font-mono text-gray-900">{{ $warehouse->warehouse_code }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Cabang</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $warehouse->branch?->name }}</dd>
        </div>
        <div class="md:col-span-2">
            <dt class="text-xs font-medium text-gray-500">Catatan</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $warehouse->notes ?: '-' }}</dd>
        </div>
    </dl>
</div>

@endsection
