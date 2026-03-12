@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Selamat datang, {{ auth()->user()->name }}</h2>
        <p class="mt-0.5 text-sm text-gray-500">
            {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
            - {{ auth()->user()->role->name->label() }}
        </p>
    </div>
</div>

<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @can('viewAny', \App\Models\User::class)
        <div class="relative overflow-hidden rounded-2xl border border-slate-300 bg-white p-5 shadow-xl shadow-indigo-500/10">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Total Pengguna</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                    <p class="mt-1.5 text-xs text-gray-500">
                        <span class="font-semibold text-green-600">{{ $stats['active_users'] }} aktif</span>
                        &nbsp;&bull;&nbsp;
                        {{ $stats['total_users'] - $stats['active_users'] }} nonaktif
                    </p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50">
                    <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-blue-400 to-blue-600"></div>
        </div>
    @endcan

    @if(auth()->user()->hasPermission('audit_logs.view'))
        <div class="relative overflow-hidden rounded-2xl border border-slate-300 bg-white p-5 shadow-xl shadow-indigo-500/10">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Aktivitas Hari Ini</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['audit_today'] }}</p>
                    <p class="mt-1.5 text-xs text-gray-500">Entri audit log tercatat</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50">
                    <svg class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-purple-400 to-purple-600"></div>
        </div>
    @endif

    <div class="relative overflow-hidden rounded-2xl border border-slate-300 bg-white p-5 shadow-xl shadow-indigo-500/10">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">Modul Aktif</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">3 <span class="text-base font-medium text-gray-500">/ 10</span></p>
                <p class="mt-1.5 text-xs text-gray-500">User &amp; Access Control, Master Produk, Cabang &amp; Gudang</p>
            </div>
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-green-50">
                <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-green-400 to-emerald-500"></div>
    </div>
</div>

@if(
    auth()->user()->hasPermission('users.view') ||
    auth()->user()->hasPermission('audit_logs.view') ||
    auth()->user()->hasPermission('items.view') ||
    auth()->user()->hasPermission('branches.view') ||
    auth()->user()->hasPermission('warehouses.view')
)
    <div class="mb-8">
        <h3 class="mb-3 text-sm font-semibold text-gray-700">Akses Cepat</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @can('viewAny', \App\Models\User::class)
                <a href="{{ route('admin.users.index') }}"
                   class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 transition group-hover:bg-blue-100">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800">Manajemen User</p>
                        <p class="text-xs text-gray-500">Kelola akun &amp; hak akses</p>
                    </div>
                </a>
            @endcan

            @if(auth()->user()->hasPermission('audit_logs.view'))
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:border-purple-300 hover:shadow-lg hover:shadow-purple-500/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 transition group-hover:bg-purple-100">
                        <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800">Audit Log</p>
                        <p class="text-xs text-gray-500">Riwayat aktivitas sistem</p>
                    </div>
                </a>
            @endif

            @if(auth()->user()->hasPermission('items.view'))
                <a href="{{ route('admin.items.index') }}"
                   class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 transition group-hover:bg-emerald-100">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800">Master Produk</p>
                        <p class="text-xs text-gray-500">Kelola SKU, brand, kategori, dan satuan</p>
                    </div>
                </a>
            @endif

            @if(auth()->user()->hasPermission('branches.view'))
                <a href="{{ route('admin.branches.index') }}"
                   class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 transition group-hover:bg-amber-100">
                        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M15 9h.01M9 13h.01M15 13h.01M9 17h.01M15 17h.01"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800">Cabang</p>
                        <p class="text-xs text-gray-500">Kelola toko atau cabang operasional</p>
                    </div>
                </a>
            @endif

            @if(auth()->user()->hasPermission('warehouses.view'))
                <a href="{{ route('admin.warehouses.index') }}"
                   class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-lg hover:shadow-cyan-500/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 transition group-hover:bg-cyan-100">
                        <svg class="h-5 w-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800">Gudang</p>
                        <p class="text-xs text-gray-500">Kelola gudang utama, sekunder, dan retur</p>
                    </div>
                </a>
            @endif
        </div>
    </div>
@endif

<div>
    <h3 class="mb-3 text-sm font-semibold text-gray-700">Modul Berikutnya</h3>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
        @foreach([
            ['label' => 'Harga & Diskon', 'module' => '04'],
            ['label' => 'Inventori', 'module' => '05'],
            ['label' => 'POS Transaksi', 'module' => '06'],
            ['label' => 'Delivery Order', 'module' => '07'],
            ['label' => 'Piutang (AR)', 'module' => '08'],
            ['label' => 'Pembelian', 'module' => '09'],
            ['label' => 'Akuntansi', 'module' => '10'],
        ] as $module)
            <div class="flex items-center gap-3 rounded-xl border border-dashed border-gray-200 bg-white/50 px-4 py-3">
                <span class="flex-shrink-0 text-xs font-bold tabular-nums text-gray-300">{{ $module['module'] }}</span>
                <p class="truncate text-sm font-medium text-gray-500">{{ $module['label'] }}</p>
                <span class="ml-auto flex-shrink-0 rounded-full bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">Soon</span>
            </div>
        @endforeach
    </div>
</div>

@endsection
