@extends('layouts.app')

@section('title', 'Budget')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
{{--HEADER --}}
     <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
         <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                   Budget
               </h1>
             <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                 Kelola dan pantau batas pengeluaran harian setiap kategori
              </p>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
            Periode {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
        </p>
    </div>

<div class="inline-flex items-center gap-2 self-start sm:self-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 shadow-sm">
    <span class="text-lg">🐷</span>
    <span class="font-semibold text-slate-800 dark:text-white">
        {{ $budgets->count() }} Budget Aktif
    </span>
</div>
</div>

@if($budgets->count() > 0)
    @php
        $totalBudgetToday = $budgets->sum('daily_limit');
        $totalSpentToday  = $budgets->sum('today_spent');
        $totalRemaining   = $totalBudgetToday - $totalSpentToday;
    @endphp

    {{-- ════════════════════════════════════════════════════════════════════
         KPI SUMMARY CARDS
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

        {{-- Total Budget --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-2xl shrink-0">
                💰
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total Budget Hari Ini</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5 truncate">{{ rupiah($totalBudgetToday) }}</p>
            </div>
        </div>

        {{-- Pengeluaran Hari Ini --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center text-2xl shrink-0">
                📉
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Pengeluaran Hari Ini</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-0.5 truncate">{{ rupiah($totalSpentToday) }}</p>
            </div>
        </div>

        {{-- Sisa Budget --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-2xl shrink-0">
                ✅
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Sisa Budget</p>
                <p class="text-xl font-bold mt-0.5 truncate {{ $totalRemaining < 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">
                    {{ rupiah($totalRemaining) }}
                </p>
            </div>
        </div>

    </div>

    {{-- ════════════════════════════════════════════════════════════════════
         BUDGET TABLE
    ════════════════════════════════════════════════════════════════════ --}}
    @php
        // Maps category slug/name fragments to a representative emoji.
        $categoryIconMap = [
            'makanan'      => '🍔',
            'food'         => '🍔',
            'transportasi' => '🚗',
            'transport'    => '🚗',
            'belanja'      => '🛍️',
            'shopping'     => '🛍️',
            'fashion'      => '🛍️',
            'kesehatan'    => '🏥',
            'health'       => '🏥',
            'hiburan'      => '🎮',
            'entertainment'=> '🎮',
            'tagihan'      => '💡',
            'utilitas'     => '💡',
            'utilities'    => '💡',
            'bills'        => '💡',
            'pendidikan'   => '📚',
            'education'    => '📚',
            'tabungan'     => '💰',
            'saving'       => '💰',
            'investasi'    => '📈',
            'investment'   => '📈',
            'need'         => '🛒',
            'want'         => '🎯',
        ];
    @endphp

    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Monitor Pengeluaran Hari Ini</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full table-fixed">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-700">
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w 1/4">Kategori</th>
                        <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-40">Limit Harian</th>
                        <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-40">Keluar Hari Ini</th>
                        <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-40">Sisa</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-1/4">Progress</th>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($budgets as $budget)
                        @php
                            // Resolve a representative emoji for this category.
                            $catSlugRaw = strtolower($budget->category->slug ?? $budget->category->name);
                            $catIcon = $categoryIconMap[$catSlugRaw] ?? null;
                            if (!$catIcon) {
                                foreach ($categoryIconMap as $needle => $emoji) {
                                    if (str_contains($catSlugRaw, $needle)) {
                                        $catIcon = $emoji;
                                        break;
                                    }
                                }
                            }
                            $catIcon = $catIcon ?? '🏷️';

                            // Progress color + status rules: 0-50% hijau, 51-80% kuning, 81-100% oranye, >100% merah.
                            $pct = $budget->percentage;
                            if ($pct > 100) {
                                $barColor = 'bg-red-500';
                                $pctColor = 'text-red-600 dark:text-red-400';
                                $statusIcon = '🔴';
                                $statusLabel = 'Melebihi Budget';
                                $statusColor = 'text-red-600 dark:text-red-400';
                            } elseif ($pct > 80) {
                                $barColor = 'bg-orange-500';
                                $pctColor = 'text-orange-600 dark:text-orange-400';
                                $statusIcon = '🟠';
                                $statusLabel = 'Hampir Habis';
                                $statusColor = 'text-orange-600 dark:text-orange-400';
                            } elseif ($pct > 50) {
                                $barColor = 'bg-yellow-400';
                                $pctColor = 'text-yellow-600 dark:text-yellow-400';
                                $statusIcon = '🟡';
                                $statusLabel = 'Waspada';
                                $statusColor = 'text-yellow-600 dark:text-yellow-400';
                            } else {
                                $barColor = 'bg-emerald-500';
                                $pctColor = 'text-emerald-600 dark:text-emerald-400';
                                $statusIcon = '🟢';
                                $statusLabel = 'Aman';
                                $statusColor = 'text-emerald-600 dark:text-emerald-400';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors" x-data="{ editing: false }">

                            {{-- Category badge --}}
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold">
                                    <span class="text-sm leading-none">{{ $catIcon }}</span>
                                    {{ $budget->category->name }}
                                </span>
                            </td>

                            {{-- Daily Limit (editable inline) --}}
                            <td class="px-6 py-5 text-right">
                                <span x-show="!editing" class="font-semibold text-slate-700 dark:text-slate-200">
                                    {{ rupiah($budget->daily_limit) }}
                                </span>
                                <div x-show="editing" x-cloak>
                                    <form method="POST" action="{{ route('budgets.update', $budget->id) }}" class="flex items-center gap-1.5 justify-end">
                                        @csrf
                                        @method('PUT')
                                        {{-- Pass category_id to satisfy BudgetRequest --}}
                                        <input type="hidden" name="category_id" value="{{ $budget->category_id }}" />
                                        <input type="number"
                                               name="daily_limit"
                                               value="{{ $budget->daily_limit }}"
                                               min="1000"
                                               class="w-28 text-right text-sm border border-emerald-400 dark:border-emerald-500 dark:bg-slate-900 dark:text-white rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-emerald-300" />
                                        <button type="submit"
                                                class="p-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button type="button" @click="editing = false"
                                                class="p-1.5 bg-slate-200 dark:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-500 transition-colors">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                            {{-- Today spent --}}
                            <td class="px-6 py-5 text-right">
                                <span class="font-semibold {{ $budget->percentage > 100 ? 'text-red-600 dark:text-red-400' : 'text-slate-700 dark:text-slate-200' }}">
                                    {{ rupiah($budget->today_spent) }}
                                </span>
                            </td>

                            {{-- Remaining --}}
                            <td class="px-6 py-5 text-right">
                                @if($budget->percentage > 100)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 font-semibold text-xs">
                                        Melebihi limit!
                                    </span>
                                @else
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ rupiah($budget->remaining) }}</span>
                                @endif
                            </td>

                            {{-- Progress --}}
                            <td class="px-6 py-5">
                                <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                                        <div class="h-3 rounded-full transition-all duration-500 {{ $barColor }}"
                                            style="width: {{ min($budget->percentage, 100) }}%">
                                        </div>
                                    </div>
                                    <span class="text-sm font-bold {{ $pctColor }}">
                                        {{ $budget->percentage }}%
                                    </span>
                                </div>
                            </div>
                                <p class="text-xs font-semibold mt-1.5 {{ $statusColor }}">
                                    {{ $statusIcon }} {{ $statusLabel }}
                                </p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    {{-- ════════════════════════════════════════════════════════════════════
         EMPTY STATE — no budgets yet
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-slate-800 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-14 text-center mb-6">
        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
            📊
        </div>
        <p class="text-slate-700 dark:text-white font-semibold text-base">Belum ada budget harian</p>
        <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">Tambahkan budget pertama untuk mulai memantau pengeluaran.</p>

        @if($remainingCategories->count() > 0)
        <a href="#add-budget"
           class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Budget
        </a>
        @endif
    </div>
@endif

{{-- ════════════════════════════════════════════════════════════════════════
     ADD BUDGET FORM
════════════════════════════════════════════════════════════════════════ --}}
@if($remainingCategories->count() > 0)
<div id="add-budget" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden mt-2">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 flex items-center gap-2">
        <i data-lucide="circle-plus" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tambah Budget Kategori</p>
    </div>
    <div class="px-6 py-5">
        <form method="POST" action="{{ route('budgets.store') }}"
              class="flex flex-wrap gap-4 items-end">
            @csrf

            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Kategori</label>
                <select name="category_id"
                        class="w-full text-sm border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 bg-white
                               {{ $errors->has('category_id') ? 'border-red-400' : '' }}">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($remainingCategories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Limit Harian (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-500 dark:text-slate-400">Rp</span>
                    <input type="number" name="daily_limit"
                           value="{{ old('daily_limit') }}"
                           min="1000"
                           placeholder="contoh: 50000"
                           class="w-full pl-9 pr-3.5 py-2.5 text-sm border border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500
                                  {{ $errors->has('daily_limit') ? 'border-red-400' : '' }}" />
                </div>
                @error('daily_limit')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                     </svg>
                    Tambah Budget
                </button>
            </div>
        </form>
    </div>
</div>
@else
<div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl px-5 py-4 flex items-center gap-3 mt-2">
    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
    <p class="text-sm text-emerald-700 dark:text-emerald-300">Semua kategori sudah memiliki budget bulan ini. 🎉</p>
</div>
@endif
</div>
</div>
@endsection