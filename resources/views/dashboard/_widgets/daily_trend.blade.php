{{-- =========================================================
     Widget: Tren 7 Hari Terakhir — Line Chart
     Data: $dailyTrend (array dari DashboardService::getDailyTrend)
     Library: Chart.js via CDN
     ========================================================= --}}

@php
    $chartId = 'chart-daily-trend-' . auth()->id();
    $hasData = collect($dailyTrend['spending'])->sum() > 0
            || collect($dailyTrend['income'])->sum() > 0;
@endphp

<div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tren 7 Hari Terakhir</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Pengeluaran vs Pendapatan (verified)</p>
        </div>
        {{-- Mini legend --}}
        <div class="flex items-center gap-4 text-xs">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-0.5 bg-red-400 rounded inline-block"></span>
                <span class="text-gray-500 dark:text-gray-400">Pengeluaran</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-0.5 bg-emerald-400 rounded inline-block"></span>
                <span class="text-gray-500 dark:text-gray-400">Pendapatan</span>
            </div>
        </div>
    </div>

    @if(!$hasData)
        <div class="text-center py-10">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
            </svg>
            <p class="text-sm text-gray-400 dark:text-gray-500">
                Belum ada transaksi dalam 7 hari terakhir.
            </p>
            <a href="{{ route('expenses.create') ?? '#' }}"
               class="inline-block mt-3 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                + Tambah Pengeluaran
            </a>
        </div>
    @else
        <div class="relative h-52">
            <canvas id="{{ $chartId }}" class="w-full h-full"></canvas>
        </div>
    @endif

</div>

@push('scripts')
<script>
(function() {
    const ctx = document.getElementById('{{ $chartId }}');
    if (!ctx) return;

    const isDark = document.documentElement.classList.contains('dark');
    const gridColor   = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.05)';
    const tickColor   = isDark ? '#9ca3af' : '#6b7280';

    // Format Rupiah ringkas untuk Y-axis
    function formatRupiahShort(val) {
        if (Math.abs(val) >= 1_000_000_000) return (val / 1_000_000_000).toFixed(1) + ' M';
        if (Math.abs(val) >= 1_000_000)     return (val / 1_000_000).toFixed(1) + ' Jt';
        if (Math.abs(val) >= 1_000)         return Math.round(val / 1_000) + ' Rb';
        return val.toString();
    }

    // Format Rupiah lengkap untuk tooltip
    function formatRupiah(val) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($dailyTrend['labels']),
            datasets: [
                {
                    label: 'Pengeluaran',
                    data: @json($dailyTrend['spending']),
                    borderColor: '#f87171',
                    backgroundColor: 'rgba(248, 113, 113, 0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#f87171',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: 'Pendapatan',
                    data: @json($dailyTrend['income']),
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(52, 211, 153, 0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#34d399',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.dataset.label}: ${formatRupiah(context.parsed.y)}`;
                        }
                    },
                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                    titleColor: isDark ? '#f9fafb' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#374151',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    padding: 10,
                }
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { color: tickColor, font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: tickColor,
                        font: { size: 11 },
                        callback: (v) => formatRupiahShort(v)
                    }
                }
            }
        }
    });
})();
</script>
@endpush
