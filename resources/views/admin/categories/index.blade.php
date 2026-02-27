@extends('layouts.app')

@section('title', 'Master Kategori')
@section('page-title', 'Master Kategori')

@section('content')

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Kelola kategori produk</p>
        @can('create', \App\Models\Category::class)
            <a href="{{ route('admin.categories.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/25 transition hover:from-blue-500 hover:to-indigo-500 hover:shadow-lg hover:shadow-blue-500/30">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kategori
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800 ring-1 ring-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Table Card ──────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-xl shadow-indigo-500/10 border border-slate-300">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50 to-slate-50">
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Kode
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Nama
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Parent
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status
                        </th>
                        <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($categories as $category)
                        <tr class="transition-colors duration-150 hover:bg-blue-50/40">
                            <td class="px-5 py-4 font-mono text-xs text-gray-700">{{ $category->code ?? '-' }}</td>
                            <td class="px-5 py-4 font-medium text-gray-900">{{ $category->name }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $category->parent?->name ?? '-' }}</td>
                            <td class="px-5 py-4">
                                @if ($category->is_active)
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-red-200/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    @can('update', $category)
                                        <a href="{{ route('admin.categories.edit', $category) }}"
                                            class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm shadow-amber-100/50 transition hover:bg-amber-100 hover:shadow-md hover:shadow-amber-200/50">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                    @endcan
                                    @if($category->is_active)
                                        @can('deactivate', $category)
                                            <form method="POST" action="{{ route('admin.categories.deactivate', $category) }}"
                                                onsubmit="confirmAction(event, this, 'Konfirmasi Status', 'Nonaktifkan kategori {{ addslashes($category->name) }}?')">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm shadow-red-100/50 transition hover:bg-red-100 hover:shadow-md hover:shadow-red-200/50">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="text-sm font-medium text-gray-600">Belum ada kategori terdaftar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">{{ $categories->links() }}</div>
        @endif
    </div>

@endsection