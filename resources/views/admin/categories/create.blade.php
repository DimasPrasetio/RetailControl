@extends('layouts.app')

@section('title', 'Tambah Kategori')
@section('page-title', 'Tambah Kategori')

@section('content')

    <div class="mx-auto max-w-lg">
        <div class="rounded-2xl bg-white p-6 shadow-xl shadow-indigo-500/10 border border-slate-300">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf

                @if(auth()->user()->isPlatformAdmin())
                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Perusahaan <span class="text-red-500">*</span></label>
                        <select name="tenant_id"
                            onchange="window.location='{{ route('admin.categories.create') }}?tenant_id=' + this.value"
                            class="tom-select-init w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('tenant_id') border-red-400 @enderror">
                            <option value="">- Pilih perusahaan -</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id', $selectedTenantId) == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                        @error('tenant_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                @endif

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Parent Kategori</label>
                    <select name="parent_id"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100">
                        <option value="">— Tidak ada (root) —</option>
                        @foreach($parents as $opt)
                            <option value="{{ $opt->id }}" {{ old('parent_id') == $opt->id ? 'selected' : '' }}>
                                {{ str_repeat('— ', $opt->depth) }}{{ $opt->name }}
                            </option>
                        @endforeach
                    </select>
                    @if(auth()->user()->isPlatformAdmin() && ! old('tenant_id', $selectedTenantId))
                        <p class="mt-1 text-xs text-gray-500">Pilih perusahaan terlebih dahulu untuk memuat parent kategori.</p>
                    @endif
                    @error('parent_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Kategori <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Kode <span
                            class="text-gray-500">(opsional, unik)</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="mis. PIPA_PVC"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 @error('code') border-red-400 @enderror">
                    @error('code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6 flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                        class="h-4 w-4 rounded border-gray-300 text-blue-600">
                    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">Simpan</button>
                    <a href="{{ route('admin.categories.index') }}"
                        class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">Batal</a>
                </div>
            </form>
        </div>
    </div>

@endsection
