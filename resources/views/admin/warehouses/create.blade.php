@extends('layouts.app')

@section('title', 'Tambah Gudang')
@section('page-title', 'Tambah Gudang')

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.warehouses.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-blue-600">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Daftar Gudang
    </a>
</div>

<div class="max-w-3xl overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
    <div class="border-b border-gray-100 px-6 py-4">
        <h2 class="text-base font-semibold text-gray-900">Gudang Baru</h2>
        <p class="mt-0.5 text-sm text-gray-500">Tambahkan gudang sesuai kebutuhan cabang operasional.</p>
    </div>

    <form method="POST" action="{{ route('admin.warehouses.store') }}">
        @csrf
        @include('admin.warehouses._form')

        <div class="flex items-center justify-end gap-3 bg-gray-50/70 px-6 py-4">
            <a href="{{ route('admin.warehouses.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:text-gray-900">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Gudang
            </button>
        </div>
    </form>
</div>

@endsection
