<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login — FinTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-500 mb-4 shadow-lg">
            <div class="w-9 h-9 rounded-xl overflow-hidden shadow-sm">
    <img
        src="{{ asset('images/vireka-logo.png') }}"
        alt="Vireka Logo"
        class="w-full h-full object-contain"
    >
</div>
        </div>
        <h1 class="text-2xl font-bold text-white">VIREKA</h1>
        <p class="text-slate-400 text-sm mt-1">Visual Integrity for Revenue & Expenditure Knowledge with Awareness</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-1">Masuk ke akun kamu</h2>
        <p class="text-slate-500 text-sm mb-6">Pantau keuanganmu dengan disiplin dan konsistensi.</p>

        {{-- Flash error --}}
        @if(session('success'))
        <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-lg mb-5">
            <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
        @endif

        {{-- Global error --}}
        @if($errors->has('email') && !$errors->has('email') == old('email'))
        @endif

        <form method="POST" action="{{ route('auth.login.post') }}" x-data>

            @csrf

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="nama@email.com"
                    class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                           {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}
                           focus:outline-none focus:ring-2"
                />
                @error('email')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4" x-data="{ show: false }">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                               {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}
                               focus:outline-none focus:ring-2 pr-10"
                    />
                    <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                    </button>
                </div>
                @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                    <span class="text-sm text-slate-600">Ingat saya</span>
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
                Masuk
            </button>

        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            Belum punya akun?
            <a href="{{ route('auth.register') }}" class="text-emerald-600 font-semibold hover:underline">Daftar sekarang</a>
        </p>
    </div>

    <p class="text-center text-xs text-slate-500 mt-6">
        FinTrack — Tugas Akhir Basis Data 2025
    </p>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
