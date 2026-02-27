@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk (SKU)')

@section('content')

    <div class="mx-auto max-w-2xl">
        <div class="rounded-2xl bg-white p-6 shadow-xl shadow-indigo-500/10 border border-slate-300">
            <form method="POST" action="{{ route('admin.items.update', $item) }}">
                @csrf @method('PUT')

                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-600">Identitas Produk</h3>

                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">SKU Code <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="sku_code" value="{{ old('sku_code', $item->sku_code) }}"
                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm uppercase shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('sku_code') border-red-400 @enderror">
                        @error('sku_code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
                        <div class="flex items-center gap-2 pt-3">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-blue-600">
                            <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Produk <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6 grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Brand</label>
                        <select name="brand_id"
                            class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                            <option value="">— Tidak ada —</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}" {{ old('brand_id', $item->brand_id) == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="category_id"
                            class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                            <option value="">— Tidak ada —</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ old('category_id', $item->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-600">Unit & Kemasan</h3>

                <div class="mb-6 grid grid-cols-3 gap-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Satuan Jual <span
                                class="text-red-500">*</span></label>
                        <select name="base_uom_id"
                            class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 @error('base_uom_id') border-red-400 @enderror">
                            @foreach($uoms as $u)
                                <option value="{{ $u->id }}" {{ old('base_uom_id', $item->base_uom_id) == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Satuan Beli</label>
                        <select name="purchase_uom_id"
                            class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                            <option value="">— Sama dengan jual —</option>
                            @foreach($uoms as $u)
                                <option value="{{ $u->id }}" {{ old('purchase_uom_id', $item->purchase_uom_id) == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Pack Qty</label>
                        <input type="number" name="pack_qty" value="{{ old('pack_qty', $item->pack_qty) }}" min="0.0001"
                            step="0.0001"
                            class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                    </div>
                </div>

                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-600">Pajak</h3>
                <div class="mb-6 flex items-center gap-2">
                    <input type="checkbox" name="tax_included" id="tax_included" value="1" {{ old('tax_included', $item->tax_included) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    <label for="tax_included" class="text-sm text-gray-700">Harga sudah termasuk PPN</label>
                </div>

                {{-- Section: Spesifikasi Produk --}}
                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-600">Spesifikasi Produk</h3>
                <p class="mb-3 text-xs text-gray-500">Detail teknis produk, seperti model, bahan, kapasitas, dll.</p>
                <div id="attr-rows" class="mb-4 space-y-2">
                    @php $attrs = old('attr_keys') ? array_combine(old('attr_keys', []), old('attr_values', [])) : ($item->attributes_json ?? []); @endphp
                    @forelse($attrs as $key => $val)
                        <div class="flex items-center gap-2 attr-row">
                            <input type="text" name="attr_keys[]" value="{{ $key }}" placeholder="Nama Spesifikasi"
                                class="w-2/5 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                            <input type="text" name="attr_values[]" value="{{ $val }}" placeholder="Keterangan"
                                class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                            <button type="button" onclick="this.closest('.attr-row').remove()"
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="flex items-center gap-2 attr-row">
                            <input type="text" name="attr_keys[]" placeholder="Nama Spesifikasi (misal: Model)"
                                class="w-2/5 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                            <input type="text" name="attr_values[]" placeholder="Keterangan (misal: RC-70)"
                                class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                            <button type="button" onclick="this.closest('.attr-row').remove()"
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="addAttrRow()"
                    class="mb-6 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-blue-400 hover:text-blue-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Spesifikasi
                </button>

                {{-- Section: Informasi Tambahan --}}
                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-600">Informasi Tambahan <span
                        class="text-xs font-normal normal-case text-gray-500">(opsional)</span></h3>
                <p class="mb-3 text-xs text-gray-500">Detail lain di luar spesifikasi utama, misal: warna, garansi, dll.</p>
                <div id="custom-rows" class="mb-4 space-y-2">
                    @php $customs = old('custom_keys') ? array_combine(old('custom_keys', []), old('custom_values', [])) : ($item->custom_fields_json ?? []); @endphp
                    @forelse($customs as $key => $val)
                        <div class="flex items-center gap-2 custom-row">
                            <input type="text" name="custom_keys[]" value="{{ $key }}" placeholder="Informasi"
                                class="w-2/5 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                            <input type="text" name="custom_values[]" value="{{ $val }}" placeholder="Keterangan"
                                class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                            <button type="button" onclick="this.closest('.custom-row').remove()"
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="flex items-center gap-2 custom-row">
                            <input type="text" name="custom_keys[]" placeholder="Informasi (misal: Warna)"
                                class="w-2/5 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                            <input type="text" name="custom_values[]" placeholder="Keterangan (misal: Hijau Daun)"
                                class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                            <button type="button" onclick="this.closest('.custom-row').remove()"
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="addCustomRow()"
                    class="mb-6 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-blue-400 hover:text-blue-600">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Detail Tambahan
                </button>

                {{-- Imported Data (read-only) --}}
                @if($item->raw_source_json)
                    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-600">Data Pemasok Eksternal <span
                            class="text-xs font-normal normal-case text-gray-500">(diimpor secara otomatis)</span></h3>
                    <div class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden text-sm">
                        <table class="min-w-full text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach($item->raw_source_json as $key => $val)
                                    <tr>
                                        <td class="px-4 py-2 font-medium text-gray-600 whitespace-nowrap w-1/3">
                                            {{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                        <td class="px-4 py-2 text-gray-800">{{ is_array($val) ? implode(', ', $val) : $val }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="flex gap-3">
                    <button type="submit"
                        class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/25 transition hover:from-blue-500 hover:to-indigo-500">Simpan
                        Perubahan</button>
                    <a href="{{ route('admin.items.show', $item) }}"
                        class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addAttrRow() {
            const container = document.getElementById('attr-rows');
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 attr-row';
            row.innerHTML = `
                    <input type="text" name="attr_keys[]" placeholder="Nama Spesifikasi (misal: Model)"
                        class="w-2/5 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                    <input type="text" name="attr_values[]" placeholder="Keterangan (misal: RC-70)"
                        class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                    <button type="button" onclick="this.closest('.attr-row').remove()"
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>`;
            container.appendChild(row);
        }

        function addCustomRow() {
            const container = document.getElementById('custom-rows');
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 custom-row';
            row.innerHTML = `
                    <input type="text" name="custom_keys[]" placeholder="Informasi (misal: Warna)"
                        class="w-2/5 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                    <input type="text" name="custom_values[]" placeholder="Keterangan (misal: Hijau Daun)"
                        class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                    <button type="button" onclick="this.closest('.custom-row').remove()"
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>`;
            container.appendChild(row);
        }
    </script>

@endsection