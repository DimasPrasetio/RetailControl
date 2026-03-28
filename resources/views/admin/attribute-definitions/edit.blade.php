@extends('layouts.app')

@section('title', 'Edit Atribut Produk')
@section('page-title', 'Edit Definisi Atribut')

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.attribute-definitions.index', ['tenant_id' => $attributeDefinition->tenant_id]) }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-blue-600">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Daftar Atribut
    </a>
</div>

<div class="max-w-4xl overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
    <div class="border-b border-gray-100 px-6 py-4">
        <h2 class="text-base font-semibold text-gray-900">Perbarui Definisi Atribut</h2>
        <p class="mt-0.5 text-sm text-gray-500">Sesuaikan label, kategori, dan tipe data untuk produk.</p>
    </div>

    <form method="POST" action="{{ route('admin.attribute-definitions.update', $attributeDefinition) }}">
        @csrf
        @method('PUT')
        @include('admin.attribute-definitions._form')

        <div class="flex items-center justify-end gap-3 bg-gray-50/70 px-6 py-4">
            <a href="{{ route('admin.attribute-definitions.index', ['tenant_id' => $attributeDefinition->tenant_id]) }}"
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
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@endsection
