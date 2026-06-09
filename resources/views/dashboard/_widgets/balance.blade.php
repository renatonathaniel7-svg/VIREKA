{{-- dashboard/_widgets/balance.blade.php --}}
{{-- Balance Summary Widget — Liquid, Investment Pool, Shadow Balance --}}
{{-- Includes Survive Mode Banner berdasarkan users.survive_level --}}

@php
    $liquid    = $balanceSummary['liquid_balance'] ?? 0;
    $pool      = $balanceSummary['investment_pool'] ?? 0;
    $shadow    = $balanceSummary['shadow_balance'] ?? 0;
    $total     = $balanceSummary['total_balance'] ?? 0;
    $mIncome   = $balanceSummary['monthly_income'] ?? 0;
    $mExpense  = $balanceSummary['monthly_expense'] ?? 0;

    $level     = $surviveInfo['level'] ?? 'normal';
    $colors    = $surviveInfo['colors'] ?? [];
    $wantFrozen = $surviveInfo['want_frozen'] ?? false;
@endphp

{{-- ── Main Balance Cards ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

    {{-- Liquid Balance —  uang yang tersedia untuk dipakai --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/40 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Liquid Balance</span>
        </div>
        <p class="text-2xl font-bold {{ $liquid >= 0 ? 'text-gray-900 dark:text-white' : 'text-red-600 dark:text-red-400' }}">
            {{ $liquid < 0 ? '-' : '' }}Rp {{ number_format(abs($liquid), 0, ',', '.') }}
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Tersedia untuk pengeluaran harian</p>
    </div>

    {{-- Investment Pool — dana yang dialokasikan ke investasi --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Investment Pool</span>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">
            Rp {{ number_format($pool, 0, ',', '.') }}
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
            <a href="{{ route('investments.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                Lihat portfolio →
            </a>
        </p>
    </div>

    {{-- Shadow Balance — unverified transactions --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Shadow Balance</span>
        </div>
        <p class="text-2xl font-bold text-gray-500 dark:text-gray-400">
            {{ $shadow >= 0 ? '+' : '-' }}Rp {{ number_format(abs($shadow), 0, ',', '.') }}
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Transaksi belum terverifikasi</p>
    </div>
</div>

{{-- ── Survive Mode Banner ──────────────────────────────────────────────────── --}}
{{-- Hanya tampil jika level BUKAN normal --}}
@if($level !== 'normal')
<div class="mt-4">

    {{-- CAUTION: Banner kuning --}}
    @if($level === 'caution')
    <div class="flex items-start gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-400 dark:border-yellow-600 rounded-xl">
        <span class="text-xl flex-shrink-0 mt-0.5">⚠️</span>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">
                Liquid Balance Menipis — Budget Dikurangi 20%
            </p>
            <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-0.5">
                Liquid balance Anda berada di zona waspada (15–30% dari rata-rata pengeluaran bulanan).
                Budget harian telah disesuaikan. Pertimbangkan mengurangi pengeluaran kategori Want.
            </p>
        </div>
    </div>

    {{-- SURVIVE: Banner oranye --}}
    @elseif($level === 'survive')
    <div class="flex items-start gap-3 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-500 dark:border-orange-600 rounded-xl">
        <span class="text-xl flex-shrink-0 mt-0.5">🔴</span>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-orange-800 dark:text-orange-300">
                Survive Mode Aktif — Budget Dikurangi 40%
            </p>
            <p class="text-xs text-orange-700 dark:text-orange-400 mt-0.5">
                Liquid balance sangat rendah (5–15% dari rata-rata pengeluaran). Kategori <strong>Want</strong> dibekukan.
                Fokus pada pengeluaran Need dan Essential saja.
            </p>
            @if($wantFrozen)
            <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 bg-orange-200 dark:bg-orange-900/40 rounded-full">
                <svg class="w-3 h-3 text-orange-700 dark:text-orange-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
                </svg>
                <span class="text-xs font-semibold text-orange-700 dark:text-orange-400">Kategori Want dibekukan</span>
            </div>
            @endif
        </div>
    </div>

    {{-- CRITICAL: Banner merah berkedip --}}
    @elseif($level === 'critical')
    <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-600 dark:border-red-700 rounded-xl animate-pulse">
        <span class="text-xl flex-shrink-0 mt-0.5">🚨</span>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                Kondisi Kritis! Budget Dikurangi 60%
            </p>
            <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">
                Liquid balance hampir habis (&lt; 5% dari rata-rata pengeluaran). Hanya pengeluaran
                <strong>esensial</strong> yang diizinkan. Pertimbangkan pencairan investasi untuk menstabilkan kondisi.
            </p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <a href="{{ route('investments.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors no-underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Lihat Portfolio Investasi
                </a>
                <span class="text-xs text-red-600 dark:text-red-400">
                    Pencairan memerlukan verifikasi screenshot.
                </span>
            </div>
        </div>
    </div>
    @endif

</div>
@endif

{{-- ── Monthly Quick Stats ──────────────────────────────────────────────────── --}}
<div class="mt-4 grid grid-cols-3 gap-3">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3 text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pemasukan Bulan Ini</p>
        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 font-mono">
            Rp {{ number_format($mIncome, 0, ',', '.') }}
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3 text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pengeluaran Bulan Ini</p>
        <p class="text-sm font-bold text-red-600 dark:text-red-400 font-mono">
            Rp {{ number_format($mExpense, 0, ',', '.') }}
        </p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-3 text-center">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Selisih Bulan Ini</p>
        @php $mSaving = $mIncome - $mExpense; @endphp
        <p class="text-sm font-bold {{ $mSaving >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} font-mono">
            {{ $mSaving >= 0 ? '+' : '-' }}Rp {{ number_format(abs($mSaving), 0, ',', '.') }}
        </p>
    </div>
</div>
