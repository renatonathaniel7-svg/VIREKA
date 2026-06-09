
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VIREKA — Personal Finance Tracker')</title>

    {{-- Tailwind CSS via CDN (development) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gray: {
                            750: '#2d3748',
                        }
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
    </style>
    @stack('styles')
</head>

<body class="h-full bg-gray-50 dark:bg-gray-900 font-sans antialiased"
      x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', v => { localStorage.setItem('darkMode', v); document.documentElement.classList.toggle('dark', v); })"
      :class="{ 'dark': darkMode }">

@php
    // Unread notification count — lihat komentar di atas soal View Composer
    $unreadNotifCount = 0;
    if (auth()->check()) {
        $unreadNotifCount = \App\Models\AppreciationLog::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }
@endphp

<div class="flex h-full">

    {{-- ══════════════════════════════════════════
        SIDEBAR
    ══════════════════════════════════════════ --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100 dark:border-gray-700">
<div class="w-9 h-9 rounded-xl overflow-hidden shadow-sm">
    <img
        src="{{ asset('images/vireka-logo.png') }}"
        alt="Vireka Logo"
        class="w-full h-full object-contain"
    >
</div>
            <div>
                <div class="font-bold text-gray-900 dark:text-white text-sm leading-tight">VIREKA</div>
                <div class="text-xs text-gray-400 dark:text-gray-500">Personal Finance Tracker</div>
            </div>
        </div>

        {{-- Nav Links --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                      {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="text-base">📊</span>
                Dashboard
            </a>

            {{-- Divider --}}
            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                    Transaksi
                </p>
            </div>

            {{-- Pemasukan --}}
            <a href="{{ route('income.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                      {{ request()->routeIs('income.*') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="text-base">💰</span>
                Pendapatan
            </a>

            {{-- Pengeluaran --}}
            <a href="{{ route('expenses.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                      {{ request()->routeIs('expenses.*') ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="text-base">💸</span>
                Pengeluaran
            </a>

            {{-- Budget --}}
            <a href="{{ route('budgets.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                      {{ request()->routeIs('budgets.*') ? 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="text-base">🎯</span>
                Budget
            </a>

            {{-- Investasi --}}
            <a href="{{ route('investments.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                      {{ request()->routeIs('investments.*') ? 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="text-base">📈</span>
                Investasi
            </a>

            <li>
                <a href="{{ route('goals.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-100">

                    <span>🎯</span>

                    <span>Goals</span>
                </a>
            </li>

            {{-- Divider --}}
            <div class="pt-3 pb-1">
                <p class="px-3 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                    Gamifikasi
                </p>
            </div>

            {{-- ─── STREAK (Chat 5 addition) ─── --}}
            <a href="{{ route('streak.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                      {{ request()->routeIs('streak.*') ? 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="text-base">🔥</span>
                Streak
                @if(auth()->user()?->current_streak > 0)
                <span class="ml-auto text-xs font-bold bg-orange-100 dark:bg-orange-900/50 text-orange-600 dark:text-orange-400 px-1.5 py-0.5 rounded-full tabular-nums">
                    {{ auth()->user()->current_streak }}
                </span>
                @endif
            </a>

            {{-- ─── BADGE (Chat 5 addition) ─── --}}
            <a href="{{ route('badges.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                      {{ request()->routeIs('badges.*') ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                <span class="text-base">🏆</span>
                Badge
            </a>

        </nav>

        {{-- User info bottom --}}
        @auth
        <div class="px-3 py-3 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="text-xs text-gray-400 dark:text-gray-500 truncate">
                        {{ auth()->user()->email }}
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('auth.logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-xl text-xs text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                    <span>🚪</span> Logout
                </button>
            </form>
        </div>
        @endauth
    </aside>

    {{-- Sidebar overlay mobile --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity duration-200"
        x-transition:leave="transition-opacity duration-200"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/50 lg:hidden"
        x-cloak
    ></div>

    {{-- ══════════════════════════════════════════
        MAIN CONTENT
    ══════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-h-0 min-w-0">

        {{-- ── TOPBAR ── --}}
        <header class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center gap-4 shadow-sm">

            {{-- Hamburger (mobile) --}}
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page title --}}
            <h1 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex-1 truncate">
                @yield('page-title', 'Vireka')
            </h1>

            {{-- Right side controls --}}
            <div class="flex items-center gap-2">

                {{-- Dark mode toggle --}}
                <button
                    @click="darkMode = !darkMode"
                    class="p-1.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    title="Toggle dark mode"
                >
                    <span x-show="!darkMode" class="text-base">🌙</span>
                    <span x-show="darkMode"  class="text-base" x-cloak>☀️</span>
                </button>

                {{-- ─── NOTIFICATION BELL (Chat 5 addition) ─── --}}
                <a
                    href="{{ route('notifications.index') }}"
                    class="relative p-1.5 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    title="Notifikasi"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>

                    {{-- Badge merah unread count --}}
                    @if($unreadNotifCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none px-0.5">
                        {{ $unreadNotifCount > 99 ? '99+' : $unreadNotifCount }}
                    </span>
                    @endif
                </a>

            </div>
        </header>

        {{-- ── PAGE CONTENT ── --}}
        <main class="flex-1 overflow-y-auto">
            @yield('content')
        </main>

    </div>
</div>

{{-- Toast flash messages --}}
@if(session('success') && !request()->routeIs('notifications.*'))
<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4000)"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed bottom-6 right-6 z-50 bg-emerald-600 text-white text-sm font-medium px-4 py-3 rounded-xl shadow-lg flex items-center gap-2"
    x-cloak
>
    ✅ {{ session('success') }}
    <button @click="show = false" class="ml-2 opacity-70 hover:opacity-100">✕</button>
</div>
@endif

@if(session('error'))
<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 5000)"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-2"
    class="fixed bottom-6 right-6 z-50 bg-red-600 text-white text-sm font-medium px-4 py-3 rounded-xl shadow-lg flex items-center gap-2"
    x-cloak
>
    ❌ {{ session('error') }}
    <button @click="show = false" class="ml-2 opacity-70 hover:opacity-100">✕</button>
</div>
@endif
@stack('scripts')
</body>
</html>
