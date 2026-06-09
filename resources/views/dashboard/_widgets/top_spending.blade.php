@php
    $topSpending = $topSpending ?? $topCategories ?? collect();
@endphp

{{-- =========================================================
     Widget: Top 5 Pengeluaran Terbesar
     Data: $topSpending (Collection dari DashboardService::getTopSpending)
     ========================================================= --}}

<div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 h-full">

    <div class="flex items-center justify-between mb-5">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Top 5 Pengeluaran</h3>
        <span class="text-xs text-gray-400 dark:text-gray-500">
            {{ now()->translatedFormat('F Y') }}
        </span>
    </div>

    @if($topSpending->isEmpty())
        <div class="text-center py-8">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 12H4m8-8v16"/>
            </svg>
            <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada pengeluaran bulan ini.</p>
        </div>
    @else
        @php
            $maxAmount = $topSpending->max('total');
        @endphp
        <div class="space-y-3">
            @foreach($topSpending as $index => $item)
                @php
                    $pct      = $maxAmount > 0 ? round(($item->total / $maxAmount) * 100) : 0;
                    $rankColors = ['bg-red-500', 'bg-orange-400', 'bg-yellow-400', 'bg-blue-400', 'bg-gray-300'];
                    $barColor   = $rankColors[$index] ?? 'bg-gray-300';
                @endphp
                <div class="group">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-xs font-bold text-gray-400 dark:text-gray-500 w-4 shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm text-gray-700 dark:text-gray-200 truncate font-medium">
                                {{ $item->category->name ?? 'Kategori Tidak Diketahui' }}
                            </span>
                        </div>
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100 shrink-0 ml-2">
                            {{ rupiah($item->total) }}
                        </span>
                    </div>
                    {{-- Mini bar relatif terhadap item terbesar --}}
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div class="h-1.5 rounded-full {{ $barColor }} transition-all duration-700"
                             style="width: {{ $pct }}%">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
