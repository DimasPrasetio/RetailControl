@extends('layouts.app')

@section('title', 'Master Brand')
@section('page-title', 'Master Brand')

@section('content')

    {{-- ── Header ──────────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600">Kelola daftar brand / merek produk</p>
        @can('create', \App\Models\Brand::class)
            <a href="{{ route('admin.brands.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/25 transition hover:from-blue-500 hover:to-indigo-500 hover:shadow-lg hover:shadow-blue-500/30">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Brand
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
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Brand
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status
                        </th>
                        <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($brands as $brand)
                        <tr class="transition-colors duration-150 hover:bg-blue-50/40">
                            <td class="px-5 py-4 font-medium text-gray-900">{{ $brand->name }}</td>
                            <td class="px-5 py-4">
                                @if ($brand->is_active)
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
                                    @can('update', $brand)
                                        <a href="{{ route('admin.brands.edit', $brand) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm transition hover:bg-amber-100">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                    @endcan
                                    @if($brand->is_active)
                                        @can('deactivate', $brand)
                                            <form method="POST" action="{{ route('admin.brands.deactivate', $brand) }}"
                                                onsubmit="confirmAction(event, this, 'Konfirmasi Status', 'Nonaktifkan brand {{ addslashes($brand->name) }}?')">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 shadow-sm transition hover:bg-orange-100">
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
                                    @can('delete', $brand)
                                        <form id="delete-brand-{{ $brand->id }}" method="POST" action="{{ route('admin.brands.destroy', $brand) }}" style="display:none;">
                                            @csrf @method('DELETE')
                                        </form>
                                        <button type="button"
                                            onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { formId: 'delete-brand-{{ $brand->id }}', itemName: '{{ addslashes($brand->name) }}' } }))"
                                            class="inline-flex items-center gap-1 rounded-lg border border-red-700 bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-16 text-center">
                                <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-600">Belum ada brand terdaftar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($brands->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">{{ $brands->links() }}</div>
        @endif
    </div>

@endsection