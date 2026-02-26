@extends('layouts.app')

@section('title', 'Master Kategori')
@section('page-title', 'Master Kategori')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-500">Kelola kategori produk</p>
    @can('create', \App\Models\Category::class)
        <a href="{{ route('admin.categories.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </a>
    @endcan
</div>

@if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700 ring-1 ring-green-200">{{ session('success') }}</div>
@endif

<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200/80">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead>
                <tr class="bg-gray-50/80">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Kode</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Parent</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($categories as $category)
                    <tr class="transition hover:bg-gray-50/60">
                        <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $category->code ?? '-' }}</td>
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $category->parent?->name ?? '-' }}</td>
                        <td class="px-5 py-4">
                            @if ($category->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Aktif</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-600"><span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @can('update', $category)
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:border-blue-300 hover:text-blue-600">Edit</a>
                                @endcan
                                @if($category->is_active)
                                    @can('deactivate', $category)
                                        <form method="POST" action="{{ route('admin.categories.deactivate', $category) }}"
                                              onsubmit="return confirm('Nonaktifkan kategori {{ addslashes($category->name) }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:border-red-300 hover:text-red-600">
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-16 text-center text-sm text-gray-400">Belum ada kategori terdaftar</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($categories->hasPages())
        <div class="border-t border-gray-100 px-5 py-3">{{ $categories->links() }}</div>
    @endif
</div>

@endsection
