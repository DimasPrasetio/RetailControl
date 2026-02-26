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
               class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                Import Excel
            </a>
        @endcan
        @can('create', \App\Models\Item::class)
            <a href="{{ route('admin.items.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Produk
            </a>
        @endcan
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700 ring-1 ring-green-200">{{ session('success') }}</div>
@endif

{{-- Filter bar --}}
<form method="GET" action="{{ route('admin.items.index') }}" class="mb-4 flex flex-wrap gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / SKU..."
           class="rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 w-48">
    <select name="brand_id" class="rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 w-40">
        <option value="">Semua Brand</option>
        @foreach($brands as $b)
            <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
        @endforeach
    </select>
    <select name="category_id" class="rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 w-44">
        <option value="">Semua Kategori</option>
        @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
        @endforeach
    </select>
    <select name="active" class="rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 w-32">
        <option value="">Semua Status</option>
        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Nonaktif</option>
    </select>
    <button type="submit" class="rounded-xl bg-gray-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-600">Filter</button>
    <a href="{{ route('admin.items.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50">Reset</a>
</form>

{{-- Table --}}
<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200/80">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">SKU</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Brand</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Kategori</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Satuan</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($items as $item)
                    <tr class="transition hover:bg-gray-50/60">
                        <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $item->sku_code }}</td>
                        <td class="px-5 py-4 font-medium text-gray-900 max-w-xs truncate">{{ $item->name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->brand?->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->category?->name ?? '-' }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-600">{{ $item->baseUom->code }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @if ($item->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Aktif</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-600"><span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @can('view', $item)
                                    <a href="{{ route('admin.items.show', $item) }}"
                                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:border-blue-300 hover:text-blue-600">Detail</a>
                                @endcan
                                @can('update', $item)
                                    <a href="{{ route('admin.items.edit', $item) }}"
                                       class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:border-blue-300 hover:text-blue-600">Edit</a>
                                @endcan
                                @if($item->is_active)
                                    @can('deactivate', $item)
                                        <form method="POST" action="{{ route('admin.items.deactivate', $item) }}"
                                              onsubmit="return confirm('Nonaktifkan produk ini?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:border-red-300 hover:text-red-600">
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-16 text-center text-sm text-gray-400">Belum ada produk terdaftar. Gunakan import Excel atau tambah manual.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($items->hasPages())
        <div class="border-t border-gray-100 px-5 py-3">{{ $items->links() }}</div>
    @endif
</div>

@endsection
