@extends('layouts.app')

@section('title', 'Tambah Lokasi Stok')
@section('page-title', 'Tambah Lokasi Stok')

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.stock-locations.index', array_filter(['branch_id' => $selectedBranchId, 'warehouse_id' => $selectedWarehouseId])) }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-blue-600">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Daftar Lokasi
    </a>
</div>

<div class="max-w-2xl rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
    <div class="rounded-t-2xl border-b border-gray-100 px-6 py-4">
        <h2 class="text-base font-semibold text-gray-900">Lokasi Stok Baru</h2>
        <p class="mt-0.5 text-sm text-gray-500">Tambahkan area atau sub-area untuk pemetaan stok gudang.</p>
    </div>

    <form method="POST" action="{{ route('admin.stock-locations.store') }}" class="divide-y divide-gray-100">
        @csrf

        {{-- Konteks: Cabang & Gudang --}}
        <div class="space-y-4 px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Lokasi Gudang</p>

            <div>
                <label for="branch_id" class="mb-1.5 block text-sm font-medium text-gray-700">Cabang</label>
                <select id="branch_id" name="branch_id" required
                        onchange="window.location='{{ route('admin.stock-locations.create') }}?branch_id=' + this.value"
                        class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('branch_id') border-red-400 bg-red-50 @enderror">
                    <option value="">- Pilih cabang -</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) old('branch_id', $selectedBranchId) === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->branch_code }} - {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="warehouse_id" class="mb-1.5 block text-sm font-medium text-gray-700">Gudang</label>
                <select id="warehouse_id" name="warehouse_id" required
                        onchange="window.location='{{ route('admin.stock-locations.create') }}?branch_id={{ $selectedBranchId }}&warehouse_id=' + this.value"
                        class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('warehouse_id') border-red-400 bg-red-50 @enderror">
                    <option value="">- Pilih gudang -</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ (string) old('warehouse_id', $selectedWarehouseId) === (string) $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->warehouse_code }} - {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
                @error('warehouse_id')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Detail Lokasi --}}
        <div class="space-y-4 px-6 py-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Detail Lokasi</p>

            <div>
                <label for="code" class="mb-1.5 block text-sm font-medium text-gray-700">Kode Lokasi</label>
                <input type="text"
                       id="code"
                       name="code"
                       value="{{ old('code') }}"
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
                       value="{{ old('name') }}"
                       required
                       maxlength="100"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition placeholder-gray-400 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('name') border-red-400 bg-red-50 @enderror"
                       placeholder="Area Rak A">
                @error('name')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="mb-1.5 block text-sm font-medium text-gray-700">Tipe Lokasi</label>
                <select id="type"
                        name="type"
                        class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('type') border-red-400 bg-red-50 @enderror">
                    @foreach (['AREA', 'SUB_AREA'] as $type)
                        <option value="{{ $type }}" {{ old('type', 'AREA') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="parent_id" class="mb-1.5 block text-sm font-medium text-gray-700">Parent Lokasi <span class="text-xs font-normal text-gray-500">(opsional)</span></label>
                <select id="parent_id"
                        name="parent_id"
                        class="tom-select-init w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-400/20 @error('parent_id') border-red-400 bg-red-50 @enderror">
                    <option value="">- Tanpa parent -</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" {{ (string) old('parent_id') === (string) $parent->id ? 'selected' : '' }}>
                            {{ $parent->code }} - {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 rounded-b-2xl bg-gray-50/70 px-6 py-4">
            <a href="{{ route('admin.stock-locations.index', array_filter(['branch_id' => $selectedBranchId, 'warehouse_id' => $selectedWarehouseId])) }}"
               class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:text-gray-900">
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Lokasi
            </button>
        </div>
    </form>
</div>

@endsection
