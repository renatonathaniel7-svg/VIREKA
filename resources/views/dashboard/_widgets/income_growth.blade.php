{{-- =========================================================
     Widget: Income Growth Alert
     Data: $incomeGrowth (array|null dari DashboardService::getIncomeGrowthAlert)
     
     Hanya ditampilkan jika $incomeGrowth tidak null.
     Komponen ini dipanggil dengan @if($incomeGrowth) dari parent view.
     ========================================================= --}}

@if($incomeGrowth)
    <div class="fintrack-card rounded-2xl shadow-sm border border-green-200 dark:border-green-800
                bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950 dark:to-emerald-950
                p-5 flex items-center gap-4"
         x-data="{ show: true }" x-show="show" x-transition>

        {{-- Icon --}}
        <div class="shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center shadow">
            <span class="text-lg">🎉</span>
        </div>

        {{-- Text --}}
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-green-800 dark:text-green-200">
                Pendapatanmu naik bulan ini!
            </p>
            <p class="text-xs text-green-700 dark:text-green-300 mt-0.5">
                +{{ rupiah($incomeGrowth['delta']) }}
                <span class="font-bold">(+{{ $incomeGrowth['pct'] }}%)</span>
                dari rata-rata {{ rupiah($incomeGrowth['prev_avg']) }} per bulan
            </p>
        </div>

        {{-- Dismiss --}}
        <button @click="show = false"
                class="shrink-0 text-green-400 hover:text-green-600 dark:hover:text-green-200 transition-colors p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

    </div>
@endif
