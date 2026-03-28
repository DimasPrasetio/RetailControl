@extends('layouts.app')

@section('title', 'Gudang')
@section('page-title', 'Master Gudang')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-600">Kelola gudang utama, gudang sekunder, dan gudang retur per cabang.</p>
    @can('create', \App\Models\Warehouse::class)
        <a href="{{ route('admin.warehouses.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/25 transition hover:from-blue-500 hover:to-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Gudang
        </a>
    @endcan
</div>

<div class="mb-5 rounded-2xl border border-slate-300 bg-white p-4 shadow-xl shadow-indigo-500/10">
    <form method="GET" action="{{ route('admin.warehouses.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="min-w-[220px] flex-1">
            <label class="mb-1 block text-xs font-medium text-gray-600">Pencarian</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode gudang..."
                   class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
        </div>
        @if(auth()->user()->isGlobal())
            <div class="min-w-[190px]">
                <label class="mb-1 block text-xs font-medium text-gray-600">Cabang</label>
                <select name="branch_id"
                        class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->branch_code }} - {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="min-w-[140px]">
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
                    class="rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">
                Filter
            </button>
            @if(request()->hasAny(['q', 'branch_id', 'active']))
                <a href="{{ route('admin.warehouses.index') }}"
                   class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-800">
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
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Gudang</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Cabang</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Tipe</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                    <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($warehouses as $warehouse)
                    <tr class="hover:bg-blue-50/40">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $warehouse->name }}</div>
                            <div class="text-xs font-mono text-gray-500">{{ $warehouse->warehouse_code }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $warehouse->notes ?: '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            <div class="font-medium text-gray-800">{{ $warehouse->branch?->name }}</div>
                            <div class="text-xs text-gray-500">{{ $warehouse->branch?->branch_code }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $warehouse->type }}</td>
                        <td class="px-5 py-4">
                            @if ($warehouse->is_active)
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
                                @can('view', $warehouse)
                                    <a href="{{ route('admin.warehouses.show', $warehouse) }}"
                                       class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-blue-300 hover:text-blue-600">
                                        Detail
                                    </a>
                                @endcan
                                @can('update', $warehouse)
                                    <a href="{{ route('admin.warehouses.edit', $warehouse) }}"
                                       class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                        Edit
                                    </a>
                                @endcan
                                @can('deactivate', $warehouse)
                                    @if ($warehouse->is_active)
                                        <form method="POST" action="{{ route('admin.warehouses.deactivate', $warehouse) }}"
                                              onsubmit="confirmAction(event, this, 'Konfirmasi Status', 'Nonaktifkan gudang {{ addslashes($warehouse->name) }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-100"
                                                    {{ $warehouse->type === 'MAIN' ? 'disabled' : '' }}>
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.warehouses.reactivate', $warehouse) }}"
                                              onsubmit="confirmAction(event, this, 'Aktifkan Gudang', 'Aktifkan kembali gudang {{ addslashes($warehouse->name) }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                @can('delete', $warehouse)
                                    @if ($warehouse->type !== 'MAIN')
                                        <form id="delete-warehouse-{{ $warehouse->id }}" method="POST" action="{{ route('admin.warehouses.destroy', $warehouse) }}" style="display:none;">
                                            @csrf @method('DELETE')
                                        </form>
                                        <button type="button"
                                            onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { formId: 'delete-warehouse-{{ $warehouse->id }}', itemName: '{{ addslashes($warehouse->name) }}' } }))"
                                            class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center text-sm text-gray-500">Belum ada gudang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($warehouses->hasPages())
        <div class="border-t border-gray-100 px-5 py-3">{{ $warehouses->links() }}</div>
    @endif
</div>

@endsection
