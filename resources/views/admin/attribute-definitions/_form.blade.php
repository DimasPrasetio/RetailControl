@php
    $isEdit = isset($attributeDefinition);
@endphp

<div class="space-y-4 px-6 py-5">
    @if(auth()->user()->isPlatformAdmin())
        <div>
            <label for="tenant_id" class="mb-1.5 block text-sm font-medium text-gray-700">Perusahaan</label>
            <select id="tenant_id"
                    name="tenant_id"
                    {{ $isEdit ? 'disabled' : '' }}
                    onchange="{{ $isEdit ? '' : "window.location='" . route('admin.attribute-definitions.create') . "?tenant_id=' + this.value" }}"
                    class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('tenant_id') border-red-400 bg-red-50 @enderror">
                <option value="">- Pilih perusahaan -</option>
                @foreach ($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ old('tenant_id', $selectedTenantId ?? $attributeDefinition->tenant_id ?? null) == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->name }}
                    </option>
                @endforeach
            </select>
            @if($isEdit)
                <input type="hidden" name="tenant_id" value="{{ $attributeDefinition->tenant_id }}">
                <p class="mt-1.5 text-xs text-gray-500">Perusahaan dikunci agar relasi kategori dan produk tetap konsisten.</p>
            @endif
            @error('tenant_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="key" class="mb-1.5 block text-sm font-medium text-gray-700">Key Atribut</label>
            <input type="text"
                   id="key"
                   name="key"
                   value="{{ old('key', $attributeDefinition->key ?? '') }}"
                   required
                   maxlength="50"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('key') border-red-400 bg-red-50 @enderror"
                   placeholder="diameter_mm">
            <p class="mt-1.5 text-xs text-gray-500">Gunakan huruf kecil, angka, dan underscore.</p>
            @error('key')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="label" class="mb-1.5 block text-sm font-medium text-gray-700">Label</label>
            <input type="text"
                   id="label"
                   name="label"
                   value="{{ old('label', $attributeDefinition->label ?? '') }}"
                   required
                   maxlength="100"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('label') border-red-400 bg-red-50 @enderror"
                   placeholder="Diameter (mm)">
            @error('label')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="data_type" class="mb-1.5 block text-sm font-medium text-gray-700">Tipe Data</label>
            <select id="data_type"
                    name="data_type"
                    required
                    class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('data_type') border-red-400 bg-red-50 @enderror">
                @foreach (['text', 'number', 'select', 'boolean'] as $type)
                    <option value="{{ $type }}" {{ old('data_type', $attributeDefinition->data_type ?? 'text') === $type ? 'selected' : '' }}>
                        {{ strtoupper($type) }}
                    </option>
                @endforeach
            </select>
            @error('data_type')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="unit" class="mb-1.5 block text-sm font-medium text-gray-700">Unit</label>
            <input type="text"
                   id="unit"
                   name="unit"
                   value="{{ old('unit', $attributeDefinition->unit ?? '') }}"
                   maxlength="20"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('unit') border-red-400 bg-red-50 @enderror"
                   placeholder="mm">
            @error('unit')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="options_text" class="mb-1.5 block text-sm font-medium text-gray-700">Opsi Pilihan</label>
        <textarea id="options_text"
                  name="options_text"
                  rows="3"
                  class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('options_text') border-red-400 bg-red-50 @enderror"
                  placeholder="NYM, NYY, NYA">{{ old('options_text', isset($attributeDefinition) ? implode(', ', $attributeDefinition->options_json ?? []) : '') }}</textarea>
        <p class="mt-1.5 text-xs text-gray-500">Isi jika tipe data `select`. Pisahkan dengan koma.</p>
        @error('options_text')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="category_ids" class="mb-1.5 block text-sm font-medium text-gray-700">Kategori yang Menggunakan</label>
        <select id="category_ids"
                name="category_ids[]"
                multiple
                class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('category_ids') border-red-400 bg-red-50 @enderror @error('category_ids.*') border-red-400 bg-red-50 @enderror">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    {{ in_array($category->id, old('category_ids', isset($attributeDefinition) ? $attributeDefinition->categories->pluck('id')->all() : []), true) ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_ids')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
        @error('category_ids.*')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-start gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
        <div class="flex h-5 items-center">
            <input type="hidden" name="is_required" value="0">
            <input type="checkbox" id="is_required" name="is_required" value="1"
                   {{ old('is_required', $attributeDefinition->is_required ?? false) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-400/30">
        </div>
        <div>
            <label for="is_required" class="cursor-pointer text-sm font-medium text-gray-700">Wajib diisi</label>
            <p class="text-xs text-gray-500">Field ini akan dianggap wajib saat kategori terkait dipilih di master produk.</p>
        </div>
    </div>
</div>
