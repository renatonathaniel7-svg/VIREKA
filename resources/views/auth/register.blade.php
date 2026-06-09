<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar — FinTrack</title>
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
            <i data-lucide="trending-up" class="w-7 h-7 text-white"></i>
        </div>
        <h1 class="text-2xl font-bold text-white">FinTrack</h1>
        <p class="text-slate-400 text-sm mt-1">Mulai perjalanan finansialmu hari ini</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-semibold text-slate-800 mb-1">Buat akun baru</h2>
        <p class="text-slate-500 text-sm mb-6">Gratis. Tidak perlu kartu kredit.</p>

        <form method="POST" action="{{ route('auth.register.post') }}" x-data>
            @csrf

            {{-- Name --}}
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autocomplete="name"
                    placeholder="Contoh: Budi Santoso"
                    class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors
                           {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}
                           focus:outline-none focus:ring-2"
                />
                @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

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
                           {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}
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
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="w-full px-3.5 py-2.5 rounded-lg border text-sm transition-colors pr-10
                               {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}
                               focus:outline-none focus:ring-2"
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

            {{-- Confirm Password --}}
            <div class="mb-6" x-data="{ show: false }">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label>
                <div class="relative">
                    <input
                        :type="show ? 'text' : 'password'"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi password"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm transition-colors pr-10
                               focus:outline-none focus:ring-2 focus:border-emerald-500 focus:ring-emerald-100"
                    />
                    <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i :data-lucide="show ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
                Buat Akun
            </button>

        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            Sudah punya akun?
            <a href="{{ route('auth.login') }}" class="text-emerald-600 font-semibold hover:underline">Masuk</a>
        </p>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
