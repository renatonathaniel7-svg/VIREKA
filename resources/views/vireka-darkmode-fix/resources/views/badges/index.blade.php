{{--
    resources/views/badges/index.blade.php
    Halaman Badge — tampilkan semua badge, earned vs locked.

    DATA DARI CONTROLLER:
    - $badges      : Collection badge objects dengan property:
                     badge_key, name, description, icon, streak_required,
                     earned, earned_at, remaining_streak, is_permanently_missed
    - $earnedCount : int
    - $totalCount  : int
    - $user        : Auth user
--}}

@extends('layouts.app')

@section('title', 'Badge Kamu — FinTrack')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 space-y-6">

    {{-- ══════════════════════════════════════════
        HEADER
    ══════════════════════════════════════════ --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                🏆 Badge Kamu
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Penghargaan permanen atas konsistensi finansialmu
            </p>
        </div>
        <div class="text-right">
            <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tabular-nums">
                {{ $earnedCount }}<span class="text-gray-300 dark:text-gray-600">/{{ $totalCount }}</span>
            </div>
            <div class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider">badge diraih</div>
        </div>
    </div>

    {{-- Progress bar earned badges --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 px-5 py-4">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Progress Koleksi Badge</span>
            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">
                {{ $totalCount > 0 ? round(($earnedCount / $totalCount) * 100) : 0 }}%
            </span>
        </div>
        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
            <div
                class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full transition-all duration-700"
                style="width: {{ $totalCount > 0 ? ($earnedCount / $totalCount) * 100 : 0 }}%"
            ></div>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
            Streak terbaik kamu: ⭐ {{ $user->best_streak }} hari
        </p>
    </div>

    {{-- ══════════════════════════════════════════
        GRID BADGE (5 BADGE)
    ══════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        @foreach($badges as $badge)
        @php
            $isEarned = $badge->earned;
            $isMissed = $badge->is_permanently_missed ?? false;
        @endphp

        <div class="
            relative flex flex-col items-center text-center p-4 rounded-2xl border-2 transition-all duration-300
            @if($isEarned)
                border-indigo-300 dark:border-indigo-600 bg-gradient-to-b from-indigo-50 to-white dark:from-indigo-900/30 dark:to-gray-800 shadow-md shadow-indigo-100 dark:shadow-indigo-900/20
            @else
                border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50
            @endif
        ">

            {{-- Badge earned ribbon --}}
            @if($isEarned)
            <div class="absolute -top-2 -right-2 bg-indigo-500 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shadow-sm">
                ✓
            </div>
            @endif

            {{-- Badge Icon --}}
            <div class="
                text-4xl mb-3 transition-all duration-300
                @if(!$isEarned) opacity-30 grayscale @endif
            ">
                {{ $badge->icon }}
            </div>

            {{-- Badge Name --}}
            <div class="
                text-xs font-bold mb-1 leading-tight
                @if($isEarned) text-indigo-700 dark:text-indigo-300 @else text-gray-400 dark:text-gray-500 @endif
            ">
                @if($isEarned)
                    {{ $badge->name }}
                @else
                    ???
                @endif
            </div>

            {{-- Streak Required --}}
            <div class="
                text-xs mb-2
                @if($isEarned) text-gray-500 dark:text-gray-400 @else text-gray-300 dark:text-gray-600 @endif
            ">
                {{ $badge->streak_required }} hari
            </div>

            {{-- Status bawah --}}
            @if($isEarned)
                <div class="mt-auto">
                    <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">
                        ✅ Diraih
                    </div>
                    @if($badge->earned_at)
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ \Carbon\Carbon::parse($badge->earned_at)->format('d M Y') }}
                    </div>
                    @endif
                </div>
            @else
                <div class="mt-auto">
                    <div class="text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                        🔒 Terkunci
                    </div>
                    @if($badge->remaining_streak > 0)
                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ $badge->remaining_streak }} hari lagi
                    </div>
                    @endif
                </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════
        INFO CARD: CARA KERJA BADGE
    ══════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                📖 Cara Mendapatkan Badge
            </h3>
        </div>
        <div class="px-6 py-4 space-y-3">
            <div class="flex items-start gap-3">
                <span class="text-lg flex-shrink-0">🎯</span>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Badge diberikan saat streak kamu TEPAT mencapai milestone (3, 7, 14, 30, atau 60 hari).
                </p>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-lg flex-shrink-0">♾️</span>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Badge bersifat <strong class="text-gray-700 dark:text-gray-300">permanen</strong> — tidak akan hilang meskipun streak di-reset.
                </p>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-lg flex-shrink-0">✅</span>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Hanya transaksi berstatus <strong class="text-gray-700 dark:text-gray-300">verified</strong> yang dihitung untuk streak.
                </p>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-lg flex-shrink-0">🔄</span>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Jika streak reset, kamu tetap bisa dapat badge yang sama di run berikutnya — selama belum pernah dapat badge itu sebelumnya.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection