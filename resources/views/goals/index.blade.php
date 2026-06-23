{{-- goals/index.blade.php --}}
{{-- Goals & Wishlist — restyled to match investments/index.blade.php design system --}}

@extends('layouts.app')

@section('title', 'Goals & Wishlist')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Page Header ──────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Goals & Wishlist
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola target finansialmu
                </p>
            </div>
            <a href="{{ route('goals.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Goal
            </a>
        </div>

        {{-- ── Summary Cards ────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">

            {{-- Total Goal --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Goal</span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $totalGoals }} Goal aktif
                </p>
            </div>

            {{-- Dana Terkumpul --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/40 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Dana Terkumpul</span>
                </div>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                    Rp {{ number_format($totalCollected, 0, ',', '.') }}
                </p>
            </div>

            {{-- Progress Rata-rata --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Progress Rata-rata</span>
                </div>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $averageProgress }}%
                </p>
            </div>

        </div>

        {{-- ── Goals List ───────────────────────────────────────────────────── --}}
        <div class="space-y-4">
            @forelse($goals as $goal)

                @php
                    $progress = $goal->target_amount > 0
                        ? min(100, round(($goal->collected_amount / $goal->target_amount) * 100))
                        : 0;

                    $progressBarColor = match(true) {
                        $progress >= 100 => 'bg-emerald-500',
                        $progress >= 70  => 'bg-blue-500',
                        $progress >= 30  => 'bg-yellow-500',
                        default          => 'bg-red-500',
                    };

                    $progressBadgeColor = match(true) {
                        $progress >= 100 => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                        $progress >= 70  => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                        $progress >= 30  => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                        default          => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                    };

                    $remainingTime = null;

                    if ($goal->target_date) {
                        $targetDate = \Carbon\Carbon::parse($goal->target_date);
                        $diff = now()->diff($targetDate);

                        if ($diff->y > 0) {
                            $remainingTime = $diff->y . ' tahun';

                            if ($diff->m > 0) {
                                $remainingTime .= ' ' . $diff->m . ' bulan';
                            }
                        } elseif ($diff->m > 0) {
                            $remainingTime = $diff->m . ' bulan';

                            if ($diff->d > 0) {
                                $remainingTime .= ' ' . $diff->d . ' hari';
                            }
                        } else {
                            $remainingTime = $diff->d . ' hari';
                        }
                    }

                    $remaining = max(0, $goal->target_amount - $goal->collected_amount);
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-150">

                    {{-- Header --}}
                    <div class="flex justify-between items-start gap-4">
                        <div class="min-w-0">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white truncate">
                                🎯 {{ $goal->name }}
                            </h3>
                            @if($goal->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $goal->description }}
                                </p>
                            @endif
                        </div>

                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold flex-shrink-0 {{ $progressBadgeColor }}">
                            {{ $progress }}%
                        </span>
                    </div>

                    {{-- Progress bar --}}
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3 mt-4">
                        <div class="{{ $progressBarColor }} h-3 rounded-full transition-all duration-300"
                             style="width: {{ $progress }}%"></div>
                    </div>

                    <div class="mt-2 text-sm font-mono text-gray-500 dark:text-gray-400">
                        Rp {{ number_format($goal->collected_amount, 0, ',', '.') }}
                        <span class="text-gray-300 dark:text-gray-600">/</span>
                        Rp {{ number_format($goal->target_amount, 0, ',', '.') }}
                    </div>

                    {{-- Stats --}}
                    <div class="grid grid-cols-3 gap-4 mt-5 pt-5 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Terkumpul</p>
                            <p class="font-mono font-semibold text-green-600 dark:text-green-400">
                                Rp {{ number_format($goal->collected_amount, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Target</p>
                            <p class="font-mono font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($goal->target_amount, 0, ',', '.') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Sisa</p>
                            <p class="font-mono font-semibold text-orange-600 dark:text-orange-400">
                                Rp {{ number_format($remaining, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Footer: target date + actions --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-5">

                        <div>
                            @if($goal->target_date)
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    📅 {{ \Carbon\Carbon::parse($goal->target_date)->translatedFormat('d F Y') }}
                                </div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">
                                    @if($remainingTime === 'Deadline terlewati')
                                    <span class="text-red-600">
                                        ⚠️ Deadline terlewati
                                    </span>
                                @elseif($remainingTime)
                                    ⏳ {{ $remainingTime }} lagi
                                @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('goals.contribute', $goal->id) }}"
                               class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white mt-6 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150">
                                Tambah Dana
                            </a>

                            <form action="{{ route('goals.destroy', $goal->id) }}" method="POST"
                                  onsubmit="return confirm('Hapus goal ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white mt-6 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150">
                                    Hapus
                                </button>
                            </form>
                        </div>

                    </div>

                </div>

            @empty

                {{-- Empty State --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada goal</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Mulai buat target finansial pertamamu.</p>
                        <a href="{{ route('goals.create') }}"
                           class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                            + Buat goal pertama
                        </a>
                    </div>
                </div>

            @endforelse
        </div>

    </div>
</div>
@endsection