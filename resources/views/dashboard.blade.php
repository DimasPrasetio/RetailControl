@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ── Welcome bar ──────────────────────────────────────────────────────── --}}
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Selamat datang, {{ auth()->user()->name }} 👋</h2>
        <p class="mt-0.5 text-sm text-gray-500">
            {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
            &mdash; {{ auth()->user()->role->name->label() }}
        </p>
    </div>
</div>

{{-- ── Stat cards ───────────────────────────────────────────────────────── --}}
<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

    @can('viewAny', \App\Models\User::class)
    <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200/80">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Pengguna</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                <p class="mt-1.5 text-xs text-gray-500">
                    <span class="font-semibold text-green-600">{{ $stats['active_users'] }} aktif</span>
                    &nbsp;&bull;&nbsp;
                    {{ $stats['total_users'] - $stats['active_users'] }} nonaktif
                </p>
            </div>
            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-blue-50">
                <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-blue-400 to-blue-600"></div>
    </div>
    @endcan

    @if(auth()->user()->hasPermission('audit_logs.view'))
    <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200/80">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Aktivitas Hari Ini</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['audit_today'] }}</p>
                <p class="mt-1.5 text-xs text-gray-500">Entri audit log tercatat</p>
            </div>
            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-purple-50">
                <svg class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-purple-400 to-purple-600"></div>
    </div>
    @endif

    <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200/80">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Modul Aktif</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">1 <span class="text-base font-medium text-gray-400">/ 10</span></p>
                <p class="mt-1.5 text-xs text-gray-500">User &amp; Access Control</p>
            </div>
            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-green-50">
                <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-green-400 to-emerald-500"></div>
    </div>

</div>

{{-- ── Quick access ─────────────────────────────────────────────────────── --}}
@if(auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('audit_logs.view'))
<div class="mb-8">
    <h3 class="mb-3 text-sm font-semibold text-gray-700">Akses Cepat</h3>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">

        @can('viewAny', \App\Models\User::class)
        <a href="{{ route('admin.users.index') }}"
           class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:border-blue-200 hover:shadow-md">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-blue-50 transition group-hover:bg-blue-100">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-800">Manajemen User</p>
                <p class="text-xs text-gray-500">Kelola akun &amp; hak akses</p>
            </div>
            <svg class="h-4 w-4 flex-shrink-0 text-gray-300 transition-transform group-hover:translate-x-0.5 group-hover:text-gray-400"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endcan

        @if(auth()->user()->hasPermission('audit_logs.view'))
        <a href="{{ route('admin.audit-logs.index') }}"
           class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:border-purple-200 hover:shadow-md">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-purple-50 transition group-hover:bg-purple-100">
                <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-800">Audit Log</p>
                <p class="text-xs text-gray-500">Riwayat aktivitas sistem</p>
            </div>
            <svg class="h-4 w-4 flex-shrink-0 text-gray-300 transition-transform group-hover:translate-x-0.5 group-hover:text-gray-400"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif

    </div>
</div>
@endif

{{-- ── Coming soon ──────────────────────────────────────────────────────── --}}
<div>
    <h3 class="mb-3 text-sm font-semibold text-gray-700">Modul Berikutnya</h3>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
        @foreach([
            ['label' => 'Master Data',    'module' => '02'],
            ['label' => 'Harga & Diskon', 'module' => '03'],
            ['label' => 'Inventori',      'module' => '04'],
            ['label' => 'POS Transaksi',  'module' => '05'],
            ['label' => 'Delivery Order', 'module' => '06'],
            ['label' => 'Piutang (AR)',   'module' => '07'],
            ['label' => 'Pembelian',      'module' => '08'],
            ['label' => 'Akuntansi',      'module' => '09'],
        ] as $m)
        <div class="flex items-center gap-3 rounded-xl border border-dashed border-gray-200 bg-white/50 px-4 py-3">
            <span class="flex-shrink-0 text-xs font-bold tabular-nums text-gray-300">{{ $m['module'] }}</span>
            <p class="truncate text-sm font-medium text-gray-400">{{ $m['label'] }}</p>
            <span class="ml-auto flex-shrink-0 rounded-full bg-gray-100 px-1.5 py-0.5 text-xs text-gray-400">Soon</span>
        </div>
        @endforeach
    </div>
</div>

@endsection
