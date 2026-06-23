{{--
    resources/views/streak/index.blade.php
    Halaman Streak — menampilkan streak harian, progress badge, dan timeline 5 hari terakhir.

    DATA DARI CONTROLLER:
    - $user          : Auth user (current_streak, best_streak, grace_days)
    - $recentHistory : Array 5 hari terakhir
    - $nextMilestone : ['milestone', 'label', 'remaining']
    - $progressPct   : Progress bar percentage (0–100)
    - $surviveLevel  : NORMAL | CAUTION | SURVIVE | CRITICAL
--}}

@extends('layouts.app')

@section('title', 'Streak Kamu — FinTrack')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 space-y-6">

    {{-- ══════════════════════════════════════════
        HEADER
    ══════════════════════════════════════════ --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                🔥 Streak Kamu
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Konsistensi pengeluaran harianmu tercatat di sini
            </p>
        </div>
        <div class="text-right">
            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                Diperbarui kemarin tengah malam
            </span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
        CARD UTAMA: CURRENT STREAK + BEST STREAK
    ══════════════════════════════════════════ --}}
    <div class="bg-gradient-to-br from-orange-500 via-red-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg shadow-orange-200 dark:shadow-orange-900/30">
        <div class="grid grid-cols-2 gap-6 mb-6">

            {{-- Current Streak --}}
            <div class="text-center">
                <div class="text-6xl font-black mb-1 tabular-nums">
                    {{ $user->current_streak }}
                </div>
                <div class="text-orange-100 text-sm font-medium uppercase tracking-widest">
                    🔥 Streak Sekarang
                </div>
                <div class="text-orange-200 text-xs mt-1">
                    @if($user->grace_days > 0)
                        ⏳ Grace period aktif ({{ $user->grace_days }}/1 hari)
                    @else
                        hari berturut-turut
                    @endif
                </div>
            </div>

            {{-- Best Streak --}}
            <div class="text-center border-l border-white/20 pl-6">
                <div class="text-6xl font-black mb-1 tabular-nums text-yellow-200">
                    {{ $user->best_streak }}
                </div>
                <div class="text-orange-100 text-sm font-medium uppercase tracking-widest">
                    ⭐ Rekor Terbaik
                </div>
                <div class="text-orange-200 text-xs mt-1">
                    all-time best
                </div>
            </div>
        </div>

        {{-- Progress Bar ke Milestone Berikutnya --}}
        <div class="bg-white/20 rounded-xl p-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-semibold text-white">
                    Menuju: {{ $nextMilestone['label'] }}
                </span>
                <span class="text-xs text-orange-100">
                    @if($nextMilestone['remaining'] > 0)
                        {{ $nextMilestone['remaining'] }} hari lagi
                    @else
                        ✅ Milestone tercapai!
                    @endif
                </span>
            </div>

            <div class="w-full bg-white/20 rounded-full h-3">
                <div
                    class="bg-white rounded-full h-3 transition-all duration-700 ease-out"
                    style="width: {{ $progressPct }}%"
                ></div>
            </div>

            <div class="flex justify-between text-xs text-orange-100 mt-1">
                <span>Streak saat ini: {{ $user->current_streak }} hari</span>
                <span>Target: {{ $nextMilestone['milestone'] }} hari</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
        SURVIVE MODE STATUS CARD
    ══════════════════════════════════════════ --}}
    @php
        $surviveConfig = [
            'NORMAL'   => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'border' => 'border-emerald-200 dark:border-emerald-700', 'text' => 'text-emerald-700 dark:text-emerald-300', 'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800 dark:text-emerald-300', 'icon' => '🟢', 'desc' => 'Kondisi keuanganmu sehat. Tetap pertahankan!'],
            'CAUTION'  => ['bg' => 'bg-yellow-50 dark:bg-yellow-900/20', 'border' => 'border-yellow-200 dark:border-yellow-700', 'text' => 'text-yellow-700 dark:text-yellow-300', 'badge' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-300', 'icon' => '🟡', 'desc' => 'Balance mulai menipis. Kurangi pengeluaran tidak penting.'],
            'SURVIVE'  => ['bg' => 'bg-orange-50 dark:bg-orange-900/20', 'border' => 'border-orange-200 dark:border-orange-700', 'text' => 'text-orange-700 dark:text-orange-300', 'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-800 dark:text-orange-300', 'icon' => '🟠', 'desc' => 'Mode bertahan aktif. Batasi hanya pengeluaran esensial!'],
            'CRITICAL' => ['bg' => 'bg-red-50 dark:bg-red-900/20', 'border' => 'border-red-200 dark:border-red-700', 'text' => 'text-red-700 dark:text-red-300', 'badge' => 'bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-300', 'icon' => '🔴', 'desc' => 'Balance kritis! Pertimbangkan withdrawal investasi.'],
        ];
        $sc = $surviveConfig[$surviveLevel] ?? $surviveConfig['NORMAL'];
    @endphp

    <div class="rounded-xl border {{ $sc['border'] }} {{ $sc['bg'] }} p-4 flex items-center gap-4">
        <div class="text-3xl">{{ $sc['icon'] }}</div>
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <span class="font-semibold {{ $sc['text'] }}">Status Keuangan:</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $sc['badge'] }}">
                    {{ $surviveLevel }}
                </span>
            </div>
            <p class="text-sm {{ $sc['text'] }} opacity-80 mt-0.5">{{ $sc['desc'] }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="text-xs {{ $sc['text'] }} underline underline-offset-2 opacity-70 hover:opacity-100">
            Detail →
        </a>
    </div>

    {{-- ══════════════════════════════════════════
        TIMELINE 5 HARI TERAKHIR
    ══════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                📅 Riwayat 5 Hari Terakhir
            </h2>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                Hanya transaksi yang sudah terverifikasi dihitung
            </p>
        </div>

        <div class="divide-y divide-gray-50 dark:divide-gray-700">
            @foreach($recentHistory as $day)
            @php
                $statusConfig = [
                    'excellent' => ['icon' => '✅', 'label' => 'Hemat', 'bar' => 'bg-emerald-400', 'text' => 'text-emerald-600 dark:text-emerald-400'],
                    'good'      => ['icon' => '👍', 'label' => 'Baik',  'bar' => 'bg-blue-400',    'text' => 'text-blue-600 dark:text-blue-400'],
                    'warning'   => ['icon' => '⚠️', 'label' => 'Mepet', 'bar' => 'bg-yellow-400',  'text' => 'text-yellow-600 dark:text-yellow-400'],
                    'danger'    => ['icon' => '❌', 'label' => 'Over',  'bar' => 'bg-red-400',     'text' => 'text-red-600 dark:text-red-400'],
                    'no_data'   => ['icon' => '⏸️', 'label' => 'Kosong','bar' => 'bg-gray-200 dark:bg-gray-600',    'text' => 'text-gray-400 dark:text-gray-500'],
                    'no_budget' => ['icon' => '❓', 'label' => 'No Budget','bar' => 'bg-gray-200 dark:bg-gray-600', 'text' => 'text-gray-400 dark:text-gray-500'],
                ];
                $sc_day = $statusConfig[$day['status']] ?? $statusConfig['no_data'];
            @endphp
            <div class="px-6 py-3 flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">

                {{-- Hari --}}
                <div class="w-24 flex-shrink-0">
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 capitalize">
                        {{ $day['day_name'] }}
                    </div>
                    <div class="text-xs text-gray-400 dark:text-gray-500">
                        {{ \Carbon\Carbon::parse($day['date'])->format('d M') }}
                    </div>
                </div>

                {{-- Pengeluaran --}}
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-800 dark:text-gray-100">
                        Rp {{ number_format($day['spent'], 0, ',', '.') }}
                    </div>
                    @if($day['budget'] > 0)
                    <div class="mt-1 w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                        <div
                            class="h-1.5 rounded-full {{ $sc_day['bar'] }} transition-all"
                            style="width: {{ min(100, $day['pct'] ?? 0) }}%"
                        ></div>
                    </div>
                    @endif
                </div>

                {{-- Vs Budget --}}
                <div class="w-16 text-right flex-shrink-0">
                    @if($day['pct'] !== null)
                    <span class="text-sm font-semibold {{ $sc_day['text'] }}">
                        {{ $day['pct'] }}%
                    </span>
                    @else
                    <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                    @endif
                </div>

                {{-- Status --}}
                <div class="w-20 text-right flex-shrink-0">
                    <span class="inline-flex items-center gap-1 text-xs font-medium">
                        {{ $sc_day['icon'] }}
                        <span class="{{ $sc_day['text'] }}">{{ $sc_day['label'] }}</span>
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════
        TIPS / CATATAN SISTEM
    ══════════════════════════════════════════ --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4">
        <p class="text-xs text-blue-600 dark:text-blue-400 leading-relaxed">
            <span class="font-semibold">ℹ️ Cara kerja streak:</span>
            Streak naik jika pengeluaran verified kamu di bawah 75% budget harian.
            Streak reset jika ≥75%. Satu hari tanpa transaksi diberi grace period —
            dua hari berturut-turut tanpa transaksi akan reset streak.
            Hanya transaksi berstatus <strong>verified</strong> yang dihitung.
        </p>
    </div>

</div>
@endsection