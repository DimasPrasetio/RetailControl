@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
    <p class="text-sm text-gray-500 mt-1">
        Selamat datang, {{ auth()->user()->name }}.
    </p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    {{-- Kartu akses cepat berdasarkan permission --}}

    @can('viewAny', \App\Models\User::class)
        <a href="{{ route('admin.users.index') }}"
            class="block bg-white rounded-lg border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Manajemen User</p>
            <p class="text-gray-800 font-semibold">Kelola akun &amp; hak akses</p>
        </a>
    @endcan

    @if (auth()->user()->hasPermission('audit_logs.view'))
        <a href="{{ route('admin.audit-logs.index') }}"
            class="block bg-white rounded-lg border border-gray-200 p-5 hover:shadow-md transition">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Audit Log</p>
            <p class="text-gray-800 font-semibold">Riwayat aktivitas sistem</p>
        </a>
    @endif
</div>
@endsection
