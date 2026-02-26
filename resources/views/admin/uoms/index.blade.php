@extends('layouts.app')

@section('title', 'Master Satuan (UoM)')
@section('page-title', 'Master Satuan (UoM)')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-gray-500">Kelola satuan unit pengukuran produk</p>
    @can('create', \App\Models\Uom::class)
        <a href="{{ route('admin.uoms.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Satuan
        </a>
    @endcan
</div>

@if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700 ring-1 ring-green-200">{{ session('success') }}</div>
@endif

<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200/80">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead>
            <tr class="bg-gray-50/80">
                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Kode</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($uoms as $uom)
                <tr class="transition hover:bg-gray-50/60">
                    <td class="px-5 py-4 font-mono font-semibold text-blue-700">{{ $uom->code }}</td>
                    <td class="px-5 py-4 text-gray-700">{{ $uom->name }}</td>
                    <td class="px-5 py-4 text-right">
                        @can('update', $uom)
                            <a href="{{ route('admin.uoms.edit', $uom) }}"
                               class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:border-blue-300 hover:text-blue-600">Edit</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-5 py-16 text-center text-sm text-gray-400">Belum ada satuan terdaftar</td></tr>
            @endforelse
        </tbody>
    </table>
    @if ($uoms->hasPages())
        <div class="border-t border-gray-100 px-5 py-3">{{ $uoms->links() }}</div>
    @endif
</div>

@endsection
