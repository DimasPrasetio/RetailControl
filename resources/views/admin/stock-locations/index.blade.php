@extends('layouts.app')

@section('title', 'Lokasi Stok')
@section('page-title', 'Master Lokasi Stok')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────── --}}
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-600">Kelola hierarki lokasi penyimpanan di dalam gudang (unlimited level).</p>
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

{{-- ── Filter ───────────────────────────────────────────────────── --}}
<div class="mb-5 rounded-2xl border border-slate-300 bg-white p-4 shadow-xl shadow-indigo-500/10">
    <form method="GET" action="{{ route('admin.stock-locations.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="min-w-[220px] flex-1">
            <label for="filter-q" class="mb-1 block text-xs font-medium text-gray-600">Pencarian</label>
            <input id="filter-q" type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari nama atau kode lokasi..."
                   class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
        </div>
        @if(auth()->user()->isGlobal())
            <div class="min-w-[190px]">
                <label for="filter-branch" class="mb-1 block text-xs font-medium text-gray-600">Cabang</label>
                <select id="filter-branch" name="branch_id"
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
            <label for="filter-warehouse" class="mb-1 block text-xs font-medium text-gray-600">Gudang</label>
            <select id="filter-warehouse" name="warehouse_id"
                    class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm text-gray-900 shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                <option value="">Semua Gudang</option>
                @foreach ($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                        {{ $wh->warehouse_code }} - {{ $wh->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[140px]">
            <label for="filter-active" class="mb-1 block text-xs font-medium text-gray-600">Status</label>
            <select id="filter-active" name="active"
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

{{-- ══════════════════════════════════════════════════════════════ --}}
{{--  TREE MODE (default — no search query)                        --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
@if ($treeMode)

    @if ($rootsByWarehouse->isEmpty())
        <div class="overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
            <div class="px-5 py-16 text-center">
                <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-sm font-medium text-gray-600">Belum ada lokasi stok terdaftar.</p>
            </div>
        </div>
    @else

        @foreach ($rootsByWarehouse as $warehouseId => $roots)
            @php $warehouse = $warehouseModels->get($warehouseId); @endphp
            @if (!$warehouse) @continue @endif

            <div x-data="{ open: true }"
                 class="mb-4 overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">

                {{-- Warehouse Header --}}
                <button @click="open = !open"
                    class="flex w-full items-center gap-3 bg-gradient-to-r from-slate-50 to-gray-50 px-5 py-4 text-left transition hover:from-slate-100 hover:to-gray-100">

                    {{-- Warehouse icon --}}
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-900">{{ $warehouse->name }}</span>
                            <span class="font-mono text-xs text-gray-400">{{ $warehouse->warehouse_code }}</span>
                        </div>
                        <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-500">
                            @if ($warehouse->branch)
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M9 17h.01M15 17h.01"/>
                                </svg>
                                <span>{{ $warehouse->branch->name }}</span>
                                <span>·</span>
                            @endif
                            <span>{{ $roots->count() }} root lokasi</span>
                            <span>·</span>
                            <span>{{ $allLocations->where('warehouse_id', $warehouseId)->count() }} total</span>
                        </div>
                    </div>

                    {{-- Chevron --}}
                    <svg class="h-5 w-5 flex-shrink-0 text-gray-400 transition-transform duration-200"
                        :class="open ? 'rotate-180' : 'rotate-0'"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Tree nodes --}}
                <div x-show="open"
                    x-transition:enter="transition-opacity duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="divide-y divide-gray-50 px-2 pb-2 pt-1">

                    @foreach ($roots as $root)
                        @include('admin.stock-locations._node', [
                            'location'     => $root,
                            'allLocations' => $allLocations,
                            'depth'        => 0,
                        ])
                    @endforeach
                </div>
            </div>
        @endforeach

    @endif

{{-- ══════════════════════════════════════════════════════════════ --}}
{{--  FLAT MODE (search results — paginated table)                 --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
@else

    <div class="mb-3 flex items-center gap-2 text-sm text-gray-500">
        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <span>Hasil pencarian <strong>"{{ request('q') }}"</strong> — {{ $stockLocations->total() }} lokasi ditemukan</span>
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
                                <div class="font-mono text-xs text-gray-500">{{ $stockLocation->code }}</div>
                                @php $badge = $stockLocation->system_type ?? $stockLocation->label; @endphp
                                @if ($badge)
                                    <span class="mt-1 inline-block rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">{{ $badge }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <div class="font-medium text-gray-800">{{ $stockLocation->warehouse?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $stockLocation->branch?->branch_code ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                @if($stockLocation->parent)
                                    <div class="font-medium text-gray-800">{{ $stockLocation->parent->name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $stockLocation->parent->code }}</div>
                                @else
                                    <span class="text-xs text-gray-400">Root</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($stockLocation->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-red-200/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Nonaktif
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
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-100">
                                                        Nonaktifkan
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.stock-locations.reactivate', $stockLocation) }}"
                                                      onsubmit="confirmAction(event, this, 'Aktifkan Lokasi', 'Aktifkan kembali lokasi {{ addslashes($stockLocation->name) }}?')">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                        Aktifkan
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                        @can('delete', $stockLocation)
                                            <form id="delete-stockloc-{{ $stockLocation->id }}" method="POST"
                                                action="{{ route('admin.stock-locations.destroy', $stockLocation) }}" style="display:none;">
                                                @csrf @method('DELETE')
                                            </form>
                                            <button type="button"
                                                onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { formId: 'delete-stockloc-{{ $stockLocation->id }}', itemName: '{{ addslashes($stockLocation->name) }}' } }))"
                                                class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Hapus
                                            </button>
                                        @endcan
                                    @else
                                        <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">Lokasi Sistem</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center text-sm text-gray-500">Tidak ada lokasi yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($stockLocations->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">{{ $stockLocations->links() }}</div>
        @endif
    </div>

@endif

@endsection
