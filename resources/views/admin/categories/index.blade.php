@extends('layouts.app')

@section('title', 'Master Kategori')
@section('page-title', 'Master Kategori')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────── --}}
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-600">Kelola kategori produk beserta sub-kategorinya (unlimited level).</p>
    @can('create', \App\Models\Category::class)
        <a href="{{ route('admin.categories.create') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/25 transition hover:from-blue-500 hover:to-indigo-500 hover:shadow-lg hover:shadow-blue-500/30">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
        </a>
    @endcan
</div>

{{-- ── Tree Card ──────────────────────────────────────────────── --}}
<div class="overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
    @forelse ($roots as $root)
        <div class="border-b border-gray-100 last:border-b-0">
            @include('admin.categories._node', [
                'category'      => $root,
                'allCategories' => $allCategories,
                'depth'         => 0,
            ])
        </div>
    @empty
        <div class="px-5 py-16 text-center">
            <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <p class="text-sm font-medium text-gray-600">Belum ada kategori terdaftar</p>
        </div>
    @endforelse
</div>

@endsection
