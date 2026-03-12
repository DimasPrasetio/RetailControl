@extends('layouts.app')

@section('title', 'Edit Cabang')
@section('page-title', 'Edit Cabang')

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.branches.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 transition hover:text-blue-600">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Daftar Cabang
    </a>
</div>

<div class="max-w-3xl overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl shadow-indigo-500/10">
    <div class="border-b border-gray-100 px-6 py-4">
        <h2 class="text-base font-semibold text-gray-900">{{ $branch->name }}</h2>
        <p class="mt-0.5 text-sm text-gray-500">Perbarui profil cabang tanpa mengubah kode cabang.</p>
    </div>

    <form method="POST" action="{{ route('admin.branches.update', $branch) }}">
        @csrf
        @method('PUT')
        @include('admin.branches._form')

        <div class="flex items-center justify-end gap-3 bg-gray-50/70 px-6 py-4">
            <a href="{{ route('admin.branches.index') }}"
               class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:text-gray-900">
                Batal
            </a>
            <button type="submit"
                    class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500">
                Perbarui Cabang
            </button>
        </div>
    </form>
</div>

@endsection
