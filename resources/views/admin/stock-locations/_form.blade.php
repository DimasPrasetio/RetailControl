@php
    $isEdit = isset($stockLocation);
    $selectedBranchValue = old('branch_id', $selectedBranchId ?? $stockLocation->branch_id ?? null);
    $selectedWarehouseValue = old('warehouse_id', $selectedWarehouseId ?? $stockLocation->warehouse_id ?? null);
@endphp

<div class="space-y-4 px-6 py-5">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="branch_id" class="mb-1.5 block text-sm font-medium text-gray-700">Cabang</label>
            <select id="branch_id"
                    name="branch_id"
                    {{ $isEdit ? 'disabled' : '' }}
                    onchange="{{ $isEdit ? '' : "window.location='" . route('admin.stock-locations.create') . "?branch_id=' + this.value" }}"
                    class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('branch_id') border-red-400 bg-red-50 @enderror">
                <option value="">- Pilih cabang -</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (string) $selectedBranchValue === (string) $branch->id ? 'selected' : '' }}>
                        {{ $branch->branch_code }} - {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            @if($isEdit)
                <input type="hidden" name="branch_id" value="{{ $stockLocation->branch_id }}">
            @endif
            @error('branch_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="warehouse_id" class="mb-1.5 block text-sm font-medium text-gray-700">Gudang</label>
            <select id="warehouse_id"
                    name="warehouse_id"
                    {{ $isEdit ? 'disabled' : '' }}
                    onchange="{{ $isEdit ? '' : "window.location='" . route('admin.stock-locations.create') . "?branch_id=" . ($selectedBranchValue ?: '') . "&warehouse_id=' + this.value" }}"
                    class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('warehouse_id') border-red-400 bg-red-50 @enderror">
                <option value="">- Pilih gudang -</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ (string) $selectedWarehouseValue === (string) $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->warehouse_code }} - {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
            @if($isEdit)
                <input type="hidden" name="warehouse_id" value="{{ $stockLocation->warehouse_id }}">
            @endif
            @error('warehouse_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="code" class="mb-1.5 block text-sm font-medium text-gray-700">Kode Lokasi</label>
            <input type="text"
                   id="code"
                   name="code"
                   value="{{ old('code', $stockLocation->code ?? '') }}"
                   required
                   maxlength="40"
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-mono text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('code') border-red-400 bg-red-50 @enderror"
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
                   class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('name') border-red-400 bg-red-50 @enderror"
                   placeholder="Area Rak A">
            @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="type" class="mb-1.5 block text-sm font-medium text-gray-700">Tipe Lokasi</label>
            <select id="type"
                    name="type"
                    class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('type') border-red-400 bg-red-50 @enderror">
                @foreach (['AREA', 'SUB_AREA'] as $type)
                    <option value="{{ $type }}" {{ old('type', $stockLocation->type ?? 'AREA') === $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
            @error('type')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="parent_id" class="mb-1.5 block text-sm font-medium text-gray-700">Parent Lokasi</label>
            <select id="parent_id"
                    name="parent_id"
                    class="tom-select-init block w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('parent_id') border-red-400 bg-red-50 @enderror">
                <option value="">- Tanpa parent -</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" {{ (string) old('parent_id', $stockLocation->parent_id ?? null) === (string) $parent->id ? 'selected' : '' }}>
                        {{ $parent->code }} - {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            @error('parent_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
