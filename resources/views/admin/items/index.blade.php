@extends('layouts.app')

@section('title', 'Master Produk (SKU)')
@section('page-title', 'Master Produk (SKU)')

@section('content')

    {{-- Header row --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Kelola katalog produk / SKU terpusat</p>
        <div class="flex gap-2">
            @can('import', \App\Models\Item::class)
                <a href="{{ route('admin.items.import-form') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm shadow-emerald-100/50 transition hover:bg-emerald-100 hover:shadow-md hover:shadow-emerald-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Excel
                </a>
            @endcan
            @can('create', \App\Models\Item::class)
                <a href="{{ route('admin.items.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-md shadow-blue-500/25 transition hover:from-blue-500 hover:to-indigo-500 hover:shadow-lg hover:shadow-blue-500/30">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Produk
                </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700 ring-1 ring-green-200">{{ session('success') }}
        </div>
    @endif

    {{-- Filter bar --}}
    <div class="mb-5 rounded-2xl bg-white p-4 shadow-xl shadow-indigo-500/10 border border-slate-300">
        <form method="GET" action="{{ route('admin.items.index') }}" class="flex flex-wrap items-end gap-3" id="filter-form">
            <div class="flex-1 min-w-[180px]">
                <label class="mb-1 block text-xs font-medium text-gray-500">Pencarian</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU..."
                    class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
            </div>
            <div class="min-w-[150px]">
                <label class="mb-1 block text-xs font-medium text-gray-500">Brand</label>
                <select name="brand_id" id="filter-brand"
                    class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua Brand</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="mb-1 block text-xs font-medium text-gray-500">Kategori</label>
                <select name="category_id" id="filter-category"
                    class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[130px]">
                <label class="mb-1 block text-xs font-medium text-gray-500">Status</label>
                <select name="active"
                    class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5 text-sm shadow-inner shadow-gray-100/50 transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100">
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
                @if(request()->hasAny(['q', 'brand_id', 'category_id', 'active']))
                    <a href="{{ route('admin.items.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-500 shadow-sm transition hover:bg-gray-50 hover:text-gray-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Wait slightly for TomSelect to init globally, then bind logic
            setTimeout(() => {
                const map = @json($filterMap);
                const brandSel = document.getElementById('filter-brand');
                const catSel = document.getElementById('filter-category');

                const allBrandOpts = Array.from(brandSel.options).map(o => ({ value: o.value, text: o.textContent }));
                const allCatOpts = Array.from(catSel.options).map(o => ({ value: o.value, text: o.textContent }));

                function rebuildOptions(select, allOpts, allowedIds, currentVal) {
                    const tom = select.tomselect;
                    const val = currentVal ?? (tom ? tom.getValue() : select.value);
                    
                    if (tom) {
                        tom.clearOptions();
                        allOpts.forEach(opt => {
                            if (opt.value === '' || !allowedIds || allowedIds.includes(Number(opt.value))) {
                                tom.addOption({value: opt.value, text: opt.text});
                            }
                        });
                        if (val) tom.setValue(val, true); // true = silent (don't trigger change event infinitely)
                    } else {
                        // Fallback Vanilla
                        select.innerHTML = '';
                        allOpts.forEach(opt => {
                            if (opt.value === '' || !allowedIds || allowedIds.includes(Number(opt.value))) {
                                const o = document.createElement('option');
                                o.value = opt.value;
                                o.textContent = opt.text;
                                if (opt.value === val) o.selected = true;
                                select.appendChild(o);
                            }
                        });
                    }
                }

                brandSel.addEventListener('change', function () {
                    const brandId = this.value;
                    if (brandId) {
                        const allowedCats = (map.brandToCategories[brandId] || []).map(Number);
                        rebuildOptions(catSel, allCatOpts, allowedCats);
                    } else {
                        rebuildOptions(catSel, allCatOpts, null);
                    }
                });

                catSel.addEventListener('change', function () {
                    const catId = this.value;
                    if (catId) {
                        const allowedBrands = (map.categoryToBrands[catId] || []).map(Number);
                        rebuildOptions(brandSel, allBrandOpts, allowedBrands);
                    } else {
                        rebuildOptions(brandSel, allBrandOpts, null);
                    }
                });

                if (brandSel.value) {
                    const allowedCats = (map.brandToCategories[brandSel.value] || []).map(Number);
                    rebuildOptions(catSel, allCatOpts, allowedCats, '{{ request('category_id') }}');
                }
                if (catSel.value) {
                    const allowedBrands = (map.categoryToBrands[catSel.value] || []).map(Number);
                    rebuildOptions(brandSel, allBrandOpts, allowedBrands, '{{ request('brand_id') }}');
                }
            }, 100);
        });
    </script>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-xl shadow-indigo-500/10 border border-slate-300">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50 to-slate-50">
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">SKU</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Brand</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kategori</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Satuan</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($items as $item)
                        <tr class="transition-colors duration-150 hover:bg-blue-50/40">
                            <td class="px-5 py-4 font-mono text-xs text-gray-700">{{ $item->sku_code }}</td>
                            <td class="px-5 py-4 font-medium text-gray-900 max-w-xs truncate">{{ $item->name }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ $item->brand?->name ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $item->category?->name ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-mono font-semibold text-gray-600">{{ $item->baseUom->code }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if ($item->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 ring-1 ring-red-200/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    @can('view', $item)
                                        <a href="{{ route('admin.items.show', $item) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 shadow-sm transition hover:bg-blue-100">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Detail
                                        </a>
                                    @endcan
                                    @can('update', $item)
                                        <a href="{{ route('admin.items.edit', $item) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm transition hover:bg-amber-100">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                    @endcan
                                    @if($item->is_active)
                                        @can('deactivate', $item)
                                            <form method="POST" action="{{ route('admin.items.deactivate', $item) }}"
                                                onsubmit="confirmAction(event, this, 'Konfirmasi Modifikasi', 'Nonaktifkan produk ini?')">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 shadow-sm transition hover:bg-orange-100">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                    @can('delete', $item)
                                        <form id="delete-item-{{ $item->id }}" method="POST" action="{{ route('admin.items.destroy', $item) }}" style="display:none;">
                                            @csrf @method('DELETE')
                                        </form>
                                        <button type="button"
                                            onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { formId: 'delete-item-{{ $item->id }}', itemName: '{{ addslashes($item->name) }}' } }))"
                                            class="inline-flex items-center gap-1 rounded-lg border border-red-700 bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-600">Belum ada produk terdaftar</p>
                                <p class="mt-1 text-xs text-gray-500">Gunakan import Excel atau tambah manual</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">{{ $items->links() }}</div>
        @endif
    </div>

@endsection