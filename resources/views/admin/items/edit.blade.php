@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk (SKU)')

@section('content')

<div class="mx-auto max-w-2xl">
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200/80">
        <form method="POST" action="{{ route('admin.items.update', $item) }}">
            @csrf @method('PUT')

            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Identitas Produk</h3>

            <div class="mb-4 grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">SKU Code <span class="text-red-500">*</span></label>
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
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}"
                       class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6 grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Brand</label>
                    <select name="brand_id" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                        <option value="">— Tidak ada —</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ old('brand_id', $item->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="category_id" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                        <option value="">— Tidak ada —</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ old('category_id', $item->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Unit & Kemasan</h3>

            <div class="mb-6 grid grid-cols-3 gap-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Satuan Jual <span class="text-red-500">*</span></label>
                    <select name="base_uom_id" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 @error('base_uom_id') border-red-400 @enderror">
                        @foreach($uoms as $u)
                            <option value="{{ $u->id }}" {{ old('base_uom_id', $item->base_uom_id) == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Satuan Beli</label>
                    <select name="purchase_uom_id" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                        <option value="">— Sama dengan jual —</option>
                        @foreach($uoms as $u)
                            <option value="{{ $u->id }}" {{ old('purchase_uom_id', $item->purchase_uom_id) == $u->id ? 'selected' : '' }}>{{ $u->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Pack Qty</label>
                    <input type="number" name="pack_qty" value="{{ old('pack_qty', $item->pack_qty) }}" min="0.0001" step="0.0001"
                           class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400">
                </div>
            </div>

            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-400">Pajak</h3>
            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" name="tax_included" id="tax_included" value="1" {{ old('tax_included', $item->tax_included) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-blue-600">
                <label for="tax_included" class="text-sm text-gray-700">Harga sudah termasuk PPN</label>
            </div>

            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wider text-gray-400">Atribut Teknis (JSON)</h3>
            <div class="mb-6">
                <textarea name="attributes_json" rows="4"
                          class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 font-mono text-xs shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">{{ old('attributes_json', $item->attributes_json ? json_encode($item->attributes_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">Simpan Perubahan</button>
                <a href="{{ route('admin.items.show', $item) }}" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
</div>

@endsection
