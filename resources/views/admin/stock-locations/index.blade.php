@extends('layouts.app')

@section('title', 'Lokasi Stok')
@section('page-title', 'Master Lokasi Stok')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-600">Kelola area dan sub-area penyimpanan di dalam gudang.</p>
    @can('create', \App\Models\StockLocation::class)
        <a href="{{ route('admin.stock-locations.create', array_filter(['branch_id' => request('branch_id'), 'warehouse_id' => request('warehouse_id')])) }}"
           class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/25 transition hover:from-blue-500 hover:to-indigo-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Lokasi
        </a>
    @endcan
</div>

<div class="mb-5 rounded-2xl border border-slate-300 bg-white p-4 shadow-xl shadow-indigo-500/10">
    <form method="GET" action="{{ route('admin.stock-locations.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="min-w-[220px] flex-1">
            <label class="mb-1 block text-xs font-medium text-gray-600">Pencarian</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode lokasi..."
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
        <div class="min-w-[190px]">
            <label class="mb-1 block text-xs font-medium text-gray-600">Gudang</label>
            <select name="warehouse_id"
                    class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                <option value="">Semua Gudang</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->warehouse_code }} - {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>
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
            @if(request()->hasAny(['q', 'branch_id', 'warehouse_id', 'active']))
                <a href="{{ route('admin.stock-locations.index') }}"
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
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Lokasi</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Gudang</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Parent</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                    <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($stockLocations as $stockLocation)
                    <tr class="hover:bg-blue-50/40">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $stockLocation->name }}</div>
                            <div class="text-xs font-mono text-gray-500">{{ $stockLocation->code }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $stockLocation->type }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            <div class="font-medium text-gray-800">{{ $stockLocation->warehouse?->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $stockLocation->branch?->branch_code ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">
                            @if($stockLocation->parent)
                                <div class="font-medium text-gray-800">{{ $stockLocation->parent->name }}</div>
                                <div class="text-xs text-gray-500">{{ $stockLocation->parent->code }}</div>
                            @else
                                <span class="text-xs text-gray-400">Root</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($stockLocation->is_active)
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
                                @if(!$stockLocation->isSystemLocation())
                                    @can('update', $stockLocation)
                                        <a href="{{ route('admin.stock-locations.edit', $stockLocation) }}"
                                           class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                            Edit
                                        </a>
                                    @endcan
                                    @can('deactivate', $stockLocation)
                                        @if ($stockLocation->is_active)
                                            <form method="POST" action="{{ route('admin.stock-locations.deactivate', $stockLocation) }}"
                                                  onsubmit="confirmAction(event, this, 'Konfirmasi Status', 'Nonaktifkan lokasi {{ addslashes($stockLocation->name) }}?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-100">
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.stock-locations.reactivate', $stockLocation) }}"
                                                  onsubmit="confirmAction(event, this, 'Aktifkan Lokasi', 'Aktifkan kembali lokasi {{ addslashes($stockLocation->name) }}?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                @else
                                    <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">Lokasi Sistem</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center text-sm text-gray-500">Belum ada lokasi stok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($stockLocations->hasPages())
        <div class="border-t border-gray-100 px-5 py-3">{{ $stockLocations->links() }}</div>
    @endif
</div>

@endsection
