<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — RetailControl</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased min-h-screen flex items-center justify-center">

    <div class="w-full max-w-sm bg-white rounded-xl shadow-md p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">RetailControl</h1>
        <p class="text-sm text-gray-500 mb-6">Masuk ke akun Anda</p>

        {{-- Error session --}}
        @if (session('error'))
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded mb-4 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            {{-- Login (username atau email) --}}
            <div class="mb-4">
                <label for="login" class="block text-sm font-medium text-gray-700 mb-1">
                    Username atau Email
                </label>
                <input
                    type="text"
                    id="login"
                    name="login"
                    value="{{ old('login') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('login') border-red-500 @enderror"
                    placeholder="username atau email@domain.com"
                >
                @error('login')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Password
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                    placeholder="••••••••"
                >
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="flex items-center mb-6">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                >
                <label for="remember" class="ml-2 text-sm text-gray-600">
                    Ingat saya
                </label>
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition"
            >
                Masuk
            </button>
        </form>
    </div>

</body>
</html>
