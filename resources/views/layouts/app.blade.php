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
    <!-- TomSelect -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.default.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <!-- TomSelect JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }">

<div class="flex h-full" id="app-layout">

    {{-- ═══════════════════════════════════════════════════════════════════════
         SIDEBAR OVERLAY (mobile backdrop)
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div id="sidebar-overlay"
         class="fixed inset-0 z-20 bg-black/60 backdrop-blur-sm lg:hidden"
         x-show="sidebarOpen"
         x-transition.opacity
         @click="sidebarOpen = false"
         style="display: none;">
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SIDEBAR
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-slate-900 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

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

            {{-- ── Master Data ──────────────────────────────────────────────── --}}
            @if(auth()->user()->hasPermission('items.view') || auth()->user()->hasPermission('brands.view') || auth()->user()->hasPermission('categories.view') || auth()->user()->hasPermission('uoms.view'))
                <div class="px-3 pb-1 pt-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Master Data</p>
                </div>
            @endif

            @if(auth()->user()->hasPermission('items.view'))
                <a href="{{ route('admin.items.index') }}"
                   class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                          {{ request()->routeIs('admin.items.*')
                             ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                             : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Produk (SKU)</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('brands.view'))
                <a href="{{ route('admin.brands.index') }}"
                   class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                          {{ request()->routeIs('admin.brands.*')
                             ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                             : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>Brand</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('categories.view'))
                <a href="{{ route('admin.categories.index') }}"
                   class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                          {{ request()->routeIs('admin.categories.*')
                             ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                             : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>Kategori</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('uoms.view'))
                <a href="{{ route('admin.uoms.index') }}"
                   class="sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                          {{ request()->routeIs('admin.uoms.*')
                             ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                             : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                    <span>Satuan (UoM)</span>
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
            <button @click="sidebarOpen = !sidebarOpen"
                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 lg:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="!sidebarOpen">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="sidebarOpen" style="display: none;">
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
            <div class="relative flex-shrink-0" id="user-menu-container" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">
                <button @click="userMenuOpen = !userMenuOpen"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-100">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-xs font-semibold text-white shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="hidden max-w-[120px] truncate font-medium sm:block">
                        {{ auth()->user()->name }}
                    </span>
                    <svg class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform duration-150"
                         :class="userMenuOpen ? 'rotate-180' : 'rotate-0'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div x-show="userMenuOpen"
                     x-transition.origin.top.right
                     class="absolute right-0 top-full z-50 mt-2 w-56 rounded-xl bg-white shadow-xl ring-1 ring-black/5 focus:outline-none"
                     style="display: none;">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <p class="text-xs text-gray-500">Masuk sebagai</p>
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

        {{-- Flash messages (handled globally via SweetAlert2) --}}
        @if(session('success') || session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });
                    @if(session('success'))
                        Toast.fire({ icon: 'success', title: {!! json_encode(session('success')) !!} });
                    @endif
                    @if(session('error'))
                        Toast.fire({ icon: 'error', title: {!! json_encode(session('error')) !!} });
                    @endif
                });
            </script>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
            @yield('content')
        </main>

    </div>
</div>

<script>
    // Close sidebar on resize to tablet/desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) {
            // Re-sync alpine state if needed, though Tailwind handles lg:static
        }
    });

    // Global helper for SweetAlert confirmations
    window.confirmAction = function(e, form, title = 'Konfirmasi', msg = 'Yakin ingin melanjutkan aksi ini?') {
        e.preventDefault();
        Swal.fire({
            title: title,
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    };
    
    // Global initialize TomSelect on elements with class 'tom-select-init'
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.tom-select-init').forEach(function(el) {
            new TomSelect(el, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                dropdownParent: 'body'
            });
        });
    });
</script>

</body>
</html>
