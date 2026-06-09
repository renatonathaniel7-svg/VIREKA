{{-- =========================================================
     Widget: Category Split — Donut Chart
     Data: $categorySplit (array dari DashboardService::getCategorySplit)
     Library: Chart.js via CDN
     ========================================================= --}}

@php
    $labels     = array_column($categorySplit, 'category_name');
    $amounts    = array_column($categorySplit, 'total_spent');
    $pcts       = array_column($categorySplit, 'pct_of_total');
    $totalSpent = array_sum($amounts);

    // Warna per tipe kategori (berurutan sesuai output query)
    $colorMap = [
        'want'       => '#f59e0b',
        'need'       => '#3b82f6',
        'saving'     => '#10b981',
        'investment' => '#6366f1',
    ];

    $bgColors = array_map(function($cat) use ($colorMap) {
        return $colorMap[$cat['category_type']] ?? '#9ca3af';
    }, $categorySplit);

    $chartId = 'chart-category-split-' . auth()->id();
@endphp

<div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 h-full">

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Distribusi Pengeluaran</h3>
        <span class="text-xs text-gray-400 dark:text-gray-500">
            {{ now()->translatedFormat('F Y') }}
        </span>
    </div>

    @if(empty($categorySplit) || $totalSpent == 0)
        <div class="text-center py-10">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
            </svg>
            <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada data pengeluaran.</p>
        </div>
    @else
        {{-- Canvas Donut --}}
        <div class="relative flex justify-center mb-4">
            <div class="relative w-44 h-44">
                <canvas id="{{ $chartId }}"></canvas>
                {{-- Center label --}}
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Total</p>
                    <p class="text-sm font-bold text-gray-800 dark:text-white leading-tight">
                        {{ rupiah($totalSpent) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Legend items --}}
        <div class="space-y-2">
            @foreach($categorySplit as $i => $cat)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0"
                              style="background-color: {{ $bgColors[$i] }}"></span>
                        <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $cat['category_name'] }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 dark:text-gray-400">{{ $cat['pct_of_total'] }}%</span>
                        <span class="font-bold text-gray-700 dark:text-gray-200">{{ rupiah($cat['total_spent']) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

@push('scripts')
<script>
(function() {
    const ctx = document.getElementById('{{ $chartId }}');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($labels),
            datasets: [{
                data: @json($amounts),
                backgroundColor: @json($bgColors),
                borderWidth: 2,
                borderColor: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const val = context.parsed;
                            const formatted = 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            const pcts = @json($pcts);
                            return ` ${formatted} (${pcts[context.dataIndex]}%)`;
                        }
                    }
                }
            }
        }
    });
})();
</script>
@endpush
