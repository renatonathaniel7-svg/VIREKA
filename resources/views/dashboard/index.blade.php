{{-- =========================================================
     View: dashboard/index.blade.php
     Controller: DashboardController@index
     
     Semua data di-load saat halaman dibuka (tidak ada AJAX).
     Chart.js data dikirim dari PHP ke JS via @json() blade directive.
     
     Layout: menggunakan layout app utama (extends layouts.app)
     ========================================================= --}}

@extends('layouts.app')

@section('title', 'Dashboard — FinTrack')

@section('content')

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- PAGE HEADER --}}
        {{-- ─────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Dashboard
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Selamat datang, <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ auth()->user()->name }}</span>
                    — {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('expenses.create') ?? '#' }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700
                          text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Catat Pengeluaran
                </a>
                <a href="{{ route('dashboard.report') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-gray-800
                          border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200
                          text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700
                          transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Laporan
                </a>
            </div>
        </div>


        @include('dashboard._widgets.balance')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @include('dashboard._widgets.top_spending')
            @include('dashboard._widgets.category_split')
            @include('dashboard._widgets.health_score')
        </div>

        @include('dashboard._widgets.daily_trend')

        @include('dashboard._widgets.goals')

        @include('dashboard._widgets.income_growth')

        @include('dashboard._widgets.budget_burndown')

        @php
            $userBadges = auth()->user()->badges ?? collect();
        @endphp
        @if($userBadges->count() > 0)
            <div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">
                    🏆 Badge yang Kamu Raih
                </h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($userBadges as $badge)
                        <div class="group relative flex items-center gap-2 px-3 py-2 rounded-xl
                                    bg-indigo-50 dark:bg-indigo-950 border border-indigo-100 dark:border-indigo-800">
                            <span class="text-lg">{{ $badge->icon ?? '🥇' }}</span>
                            <div>
                                <p class="text-xs font-bold text-indigo-700 dark:text-indigo-300">{{ $badge->name }}</p>
                                <p class="text-xs text-indigo-500 dark:text-indigo-400">{{ $badge->milestone_days }} hari</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Empty state global jika user baru --}}
        @if(
            $balanceSummary['verified_income'] == 0 &&
            $balanceSummary['verified_expense'] == 0
        )
            <div class="fintrack-card p-10 rounded-2xl text-center border border-dashed border-gray-200
                        dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="text-5xl mb-4">💰</div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Mulai Perjalanan Finansialmu!</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                    Belum ada transaksi yang terverifikasi. Mulai catat pendapatan dan pengeluaranmu untuk melihat dashboard penuh.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <a href="{{ route('income.create') ?? '#' }}"
                       class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        + Catat Pendapatan
                    </a>
                    <a href="{{ route('expenses.create') ?? '#' }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        + Catat Pengeluaran
                    </a>
                </div>
            </div>
        @endif

    </div>{{-- /container --}}
</div>

@endsection

@push('scripts')
{{-- Chart.js CDN — harus dimuat sebelum @stack('scripts') dieksekusi --}}
{{-- Letakkan <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     di layouts.app.blade.php, sebelum @stack('scripts') --}}
@endpush
