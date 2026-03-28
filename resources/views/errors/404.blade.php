<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Halaman Tidak Ditemukan — RetailControl</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 font-sans antialiased">

<div class="flex min-h-full items-center justify-center px-4 py-12">
    <div class="w-full max-w-md text-center">

        {{-- Icon --}}
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-blue-500/10 ring-1 ring-blue-500/20">
            <svg class="h-10 w-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        {{-- Code --}}
        <p class="text-7xl font-bold text-blue-400/30 leading-none tracking-tight select-none">404</p>

        {{-- Message --}}
        <h1 class="mt-4 text-2xl font-bold text-white">Halaman Tidak Ditemukan</h1>
        <p class="mt-2 text-sm text-blue-200/60">
            Halaman yang Anda cari tidak ada atau telah dipindahkan.
        </p>

        {{-- Actions --}}
        <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-5 py-2.5 text-sm font-semibold text-white
                          shadow-lg shadow-blue-500/25 transition-all hover:bg-blue-400 active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Ke Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-5 py-2.5 text-sm font-semibold text-white
                          shadow-lg shadow-blue-500/25 transition-all hover:bg-blue-400 active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Ke Halaman Login
                </a>
            @endauth

            <button onclick="history.back()"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-semibold text-blue-200
                           transition-all hover:bg-white/10 active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </button>
        </div>

        <p class="mt-10 text-xs text-blue-300/30">&copy; {{ date('Y') }} RetailControl. All rights reserved.</p>
    </div>
</div>

</body>
</html>
