@php
    $isEdit = isset($stockLocation);
@endphp

<div class="space-y-4 px-6 py-5">
    @if($isEdit)
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Lokasi Gudang</p>

        <div>
            <label for="branch_display" class="mb-1.5 block text-sm font-medium text-gray-700">Cabang</label>
            <select id="branch_display" disabled
                    class="w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500">
                <option selected>{{ $stockLocation->branch->branch_code }} - {{ $stockLocation->branch->name }}</option>
            </select>
            <input type="hidden" name="branch_id" value="{{ $stockLocation->branch_id }}">
        </div>

        <div>
            <label for="warehouse_display" class="mb-1.5 block text-sm font-medium text-gray-700">Gudang</label>
            <select id="warehouse_display" disabled
                    class="w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500">
                <option selected>{{ $stockLocation->warehouse->warehouse_code }} - {{ $stockLocation->warehouse->name }}</option>
            </select>
            <input type="hidden" name="warehouse_id" value="{{ $stockLocation->warehouse_id }}">
        </div>
    @endif
</div>

<div class="space-y-4 px-6 py-5">
    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Detail Lokasi</p>

    <div>
        <label for="code" class="mb-1.5 block text-sm font-medium text-gray-700">Kode Lokasi</label>
        <input type="text"
               id="code"
               name="code"
               value="{{ old('code', $stockLocation->code ?? '') }}"
               required
               maxlength="40"
               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-900 transition placeholder-gray-400 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('code') border-red-400 bg-red-50 @enderror"
               placeholder="JKT-MAIN-A1">
        @error('code')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nama Lokasi</label>
        <input type="text"
               id="name"
               name="name"
               value="{{ old('name', $stockLocation->name ?? '') }}"
               required
               maxlength="100"
               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition placeholder-gray-400 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('name') border-red-400 bg-red-50 @enderror"
               placeholder="Area Rak A">
        @error('name')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="label" class="mb-1.5 block text-sm font-medium text-gray-700">
            Label Lokasi <span class="text-xs font-normal text-gray-500">(opsional, mis. Rak, Lantai, Laci, Blok)</span>
        </label>
        <input type="text"
               id="label"
               name="label"
               value="{{ old('label', $stockLocation->label ?? '') }}"
               maxlength="50"
               placeholder="Rak, Lantai, Laci, Blok, ..."
               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition placeholder-gray-400 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('label') border-red-400 bg-red-50 @enderror">
        @error('label')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="parent_id" class="mb-1.5 block text-sm font-medium text-gray-700">Parent Lokasi <span class="text-xs font-normal text-gray-500">(opsional)</span></label>
        <select id="parent_id"
                name="parent_id"
                class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('parent_id') border-red-400 bg-red-50 @enderror">
            <option value="">- Tanpa parent -</option>
            @foreach ($parents as $opt)
                <option value="{{ $opt->id }}" {{ (string) old('parent_id', $stockLocation->parent_id ?? null) === (string) $opt->id ? 'selected' : '' }}>
                    {{ str_repeat('— ', $opt->depth) }}{{ $opt->code }} - {{ $opt->name }}
                </option>
            @endforeach
        </select>
        @error('parent_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
