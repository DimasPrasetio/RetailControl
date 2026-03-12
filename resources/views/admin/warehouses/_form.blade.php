@php
    $isEdit = isset($warehouse);
    $isMainWarehouse = ($warehouse->type ?? null) === 'MAIN';
@endphp

<div class="space-y-4 px-6 py-5">
    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Profil Gudang</p>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="branch_id" class="mb-1.5 block text-sm font-medium text-gray-700">Cabang</label>
            <select id="branch_id"
                    name="branch_id"
                    {{ $isEdit ? 'disabled' : 'required' }}
                    class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('branch_id') border-red-400 bg-red-50 @enderror">
                <option value="">- Pilih cabang -</option>
                @foreach ($branches as $branchOption)
                    <option value="{{ $branchOption->id }}"
                        {{ old('branch_id', $warehouse->branch_id ?? '') == $branchOption->id ? 'selected' : '' }}>
                        {{ $branchOption->branch_code }} - {{ $branchOption->name }}
                    </option>
                @endforeach
            </select>
            @if($isEdit)
                <input type="hidden" name="branch_id" value="{{ $warehouse->branch_id }}">
            @endif
            @error('branch_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="warehouse_code" class="mb-1.5 block text-sm font-medium text-gray-700">Kode Gudang</label>
            <input type="text"
                   id="warehouse_code"
                   name="warehouse_code"
                   value="{{ old('warehouse_code', $warehouse->warehouse_code ?? '') }}"
                   {{ $isEdit ? 'readonly' : 'required' }}
                   maxlength="30"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('warehouse_code') border-red-400 bg-red-50 @enderror"
                   placeholder="JKT-SEC-01">
            @error('warehouse_code')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nama Gudang</label>
            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name', $warehouse->name ?? '') }}"
                   required
                   maxlength="100"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('name') border-red-400 bg-red-50 @enderror"
                   placeholder="Gudang Utama">
            @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="type" class="mb-1.5 block text-sm font-medium text-gray-700">Tipe Gudang</label>
            <select id="type"
                    name="type"
                    {{ $isMainWarehouse ? 'disabled' : '' }}
                    class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('type') border-red-400 bg-red-50 @enderror">
                @foreach (['MAIN', 'SECONDARY', 'RETURNS'] as $typeOption)
                    <option value="{{ $typeOption }}" {{ old('type', $warehouse->type ?? 'SECONDARY') === $typeOption ? 'selected' : '' }}>
                        {{ $typeOption }}
                    </option>
                @endforeach
            </select>
            @if($isMainWarehouse)
                <input type="hidden" name="type" value="MAIN">
                <p class="mt-1.5 text-xs text-gray-500">Gudang utama selalu bertipe MAIN.</p>
            @endif
            @error('type')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700">Catatan</label>
        <textarea id="notes"
                  name="notes"
                  rows="3"
                  class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('notes') border-red-400 bg-red-50 @enderror"
                  placeholder="Catatan operasional gudang">{{ old('notes', $warehouse->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @unless($isEdit)
        <div class="flex items-start gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
            <div class="flex h-5 items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-400/30">
            </div>
            <div>
                <label for="is_active" class="cursor-pointer text-sm font-medium text-gray-700">Gudang aktif</label>
                <p class="text-xs text-gray-500">Gudang aktif dapat dipakai modul operasional berikutnya.</p>
            </div>
        </div>
    @endunless
</div>
