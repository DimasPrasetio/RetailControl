<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — RetailControl</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 font-sans antialiased">

<div class="flex h-full" id="app-layout">

    {{-- ═══════════════════════════════════════════════════════════════════════
         SIDEBAR OVERLAY (mobile backdrop)
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div id="sidebar-overlay"
         class="fixed inset-0 z-20 bg-black/60 backdrop-blur-sm hidden lg:hidden"
         onclick="toggleSidebar()">
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SIDEBAR
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-slate-900
                  -translate-x-full transition-transform duration-300 ease-in-out
                  lg:static lg:translate-x-0">

        {{-- Logo --}}
        <div class="flex h-16 flex-shrink-0 items-center gap-3 border-b border-slate-700/60 px-5">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-blue-500 shadow-lg shadow-blue-500/30">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold tracking-tight text-white">RetailControl</p>
                <p class="text-xs text-slate-400">Management System</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-1">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('dashboard')
                         ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                         : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <svg class="h-5 w-5 flex-shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- ── Administrasi ─────────────────────────────────────────────── --}}
            @if(auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('audit_logs.view'))
                <div class="px-3 pb-1 pt-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Administrasi</p>
                </div>
            @endif

            @can('viewAny', \App\Models\User::class)
                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                          {{ request()->routeIs('admin.users.*')
                             ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                             : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Manajemen User</span>
                </a>
            @endcan

            @if(auth()->user()->hasPermission('audit_logs.view'))
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                          {{ request()->routeIs('admin.audit-logs.*')
                             ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                             : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>Audit Log</span>
                </a>
            @endif

            {{-- ── Placeholder items (modul berikutnya) ──────────────────────── --}}
            <div class="px-3 pb-1 pt-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Operasional</p>
            </div>

            @foreach([
                ['icon' => 'M3 3h2l.4 2M7 13h10l4-4H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z', 'label' => 'Transaksi POS'],
                ['icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label' => 'Stok & Gudang'],
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'Piutang (AR)'],
                ['icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'label' => 'Pembelian'],
            ] as $item)
                <span class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 opacity-50 select-none">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span>{{ $item['label'] }}</span>
                    <span class="ml-auto text-xs bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Soon</span>
                </span>
            @endforeach

        </nav>

        {{-- User card at bottom --}}
        <div class="flex-shrink-0 border-t border-slate-700/60 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-sm font-semibold text-white shadow">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ auth()->user()->role->name->label() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit"
                            title="Keluar"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-700 hover:text-red-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ═══════════════════════════════════════════════════════════════════════
         MAIN AREA
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

        {{-- Top Header --}}
        <header class="flex h-16 flex-shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-4 sm:px-6">

            {{-- Hamburger (mobile) --}}
            <button id="hamburger-btn"
                    onclick="toggleSidebar()"
                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 lg:hidden">
                <svg id="hamburger-icon-open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="hamburger-icon-close" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Page title / breadcrumb --}}
            <div class="flex min-w-0 flex-1 items-center gap-2">
                <h1 class="truncate text-base font-semibold text-gray-800">
                    @yield('page-title', 'Dashboard')
                </h1>
                @hasSection('breadcrumb')
                    <span class="text-gray-300">/</span>
                    <span class="truncate text-sm text-gray-500">@yield('breadcrumb')</span>
                @endif
            </div>

            {{-- Right: user dropdown --}}
            <div class="relative flex-shrink-0" id="user-menu-container">
                <button onclick="toggleUserMenu()"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-100">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-xs font-semibold text-white shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="hidden max-w-[120px] truncate font-medium sm:block">
                        {{ auth()->user()->name }}
                    </span>
                    <svg class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-150" id="user-menu-chevron"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div id="user-menu-dropdown"
                     class="absolute right-0 top-full z-50 mt-2 hidden w-56 rounded-xl bg-white shadow-xl ring-1 ring-black/5 focus:outline-none">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <p class="text-xs text-gray-400">Masuk sebagai</p>
                        <p class="mt-0.5 truncate text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                        <span class="mt-1 inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                            {{ auth()->user()->role->name->label() }}
                        </span>
                    </div>
                    <div class="p-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-red-600 transition hover:bg-red-50 hover:text-red-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar dari sistem
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </header>

        {{-- Flash messages --}}
        @if(session('success') || session('error'))
            <div class="px-4 pt-4 sm:px-6">
                @if(session('success'))
                    <div class="mb-2 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-2 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
            @yield('content')
        </main>

    </div>
</div>

<script>
    // ── Sidebar toggle ──────────────────────────────────────────────────────
    let sidebarOpen = false;

    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const overlay  = document.getElementById('sidebar-overlay');
        const iconOpen = document.getElementById('hamburger-icon-open');
        const iconClose= document.getElementById('hamburger-icon-close');

        sidebarOpen = !sidebarOpen;

        if (sidebarOpen) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
        }
    }

    // ── User menu dropdown ──────────────────────────────────────────────────
    function toggleUserMenu() {
        const dropdown = document.getElementById('user-menu-dropdown');
        const chevron  = document.getElementById('user-menu-chevron');
        const isHidden = dropdown.classList.contains('hidden');

        dropdown.classList.toggle('hidden');
        chevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const container = document.getElementById('user-menu-container');
        const dropdown  = document.getElementById('user-menu-dropdown');
        const chevron   = document.getElementById('user-menu-chevron');

        if (container && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
            chevron.style.transform = 'rotate(0deg)';
        }
    });

    // Close sidebar on resize to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024 && sidebarOpen) {
            toggleSidebar();
        }
    });
</script>

</body>
</html>
