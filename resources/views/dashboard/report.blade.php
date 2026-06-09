{{-- =========================================================
     View: dashboard/report.blade.php
     Controller: DashboardController@report
     
     Fitur Laporan Bulanan — wajib untuk TA.
     Filter bulan/tahun via GET parameter.
     Dilengkapi print CSS: sidebar & tombol disembunyikan saat cetak.
     Bar chart pengeluaran harian via Chart.js.
     ========================================================= --}}

@extends('layouts.app')

@section('title', 'Laporan Bulanan — FinTrack')

@push('head')
<style>
    @media print {
        /* Sembunyikan navigasi, sidebar, tombol saat cetak */
        nav, aside, .no-print, .fintrack-sidebar,
        header, footer, .print-hide {
            display: none !important;
        }
        body {
            background: white !important;
            font-size: 12px;
        }
        .fintrack-card {
            border: 1px solid #e5e7eb !important;
            box-shadow: none !important;
            break-inside: avoid;
        }
        .print-full {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        canvas { max-width: 100% !important; }
    }
</style>
@endpush

@section('content')

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 print-full space-y-6">

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- HEADER + FILTER --}}
        {{-- ─────────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print-hide">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Laporan Bulanan
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $report['month_name'] }} — {{ auth()->user()->name }}
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                {{-- Filter bulan/tahun --}}
                <form method="GET" action="{{ route('dashboard.report') }}"
                      class="flex items-center gap-2">
                    <select name="month"
                            class="text-sm rounded-lg border border-gray-200 dark:border-gray-700
                                   bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200
                                   px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                    <select name="year"
                            class="text-sm rounded-lg border border-gray-200 dark:border-gray-700
                                   bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200
                                   px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @for($y = now()->year; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white
                                   text-sm font-semibold rounded-lg transition-colors">
                        Tampilkan
                    </button>
                </form>

                {{-- Print button --}}
                <button onclick="window.print()"
                        class="no-print inline-flex items-center gap-1.5 px-4 py-2
                               bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                               text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-lg
                               hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak
                </button>

                <a href="{{ route('dashboard') }}"
                   class="no-print inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-500
                          hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                    ← Dashboard
                </a>
            </div>
        </div>

        {{-- Print header (hanya muncul saat print) --}}
        <div class="hidden print:block text-center mb-6 border-b pb-4">
            <h1 class="text-xl font-bold">FinTrack — Laporan Bulanan</h1>
            <p class="text-sm text-gray-600">{{ $report['month_name'] }} | {{ auth()->user()->name }}</p>
        </div>

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- SECTION 1: RINGKASAN FINANSIAL --}}
        {{-- ─────────────────────────────────────────────────────── --}}
        <div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="text-indigo-500">01</span>
                Ringkasan Finansial
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left py-2 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                                Komponen
                            </th>
                            <th class="text-right py-2 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                                {{ $report['month_name'] }}
                            </th>
                            <th class="text-right py-2 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                                Bulan Lalu
                            </th>
                            <th class="text-right py-2 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">
                                Δ
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-750">
                        {{-- Total Income --}}
                        <tr>
                            <td class="py-3 pr-4 font-medium text-gray-700 dark:text-gray-200">
                                Total Pendapatan
                            </td>
                            <td class="py-3 pr-4 text-right font-semibold text-green-600 dark:text-green-400">
                                {{ rupiah($report['total_income']) }}
                            </td>
                            <td class="py-3 pr-4 text-right text-gray-500 dark:text-gray-400">
                                {{ rupiah($report['prev_income']) }}
                            </td>
                            <td class="py-3 text-right">
                                @if($report['delta_income_pct'] !== null)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full
                                        {{ $report['delta_income_pct'] >= 0
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                            : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                        {{ $report['delta_income_pct'] >= 0 ? '+' : '' }}{{ $report['delta_income_pct'] }}%
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Total Expense --}}
                        <tr>
                            <td class="py-3 pr-4 font-medium text-gray-700 dark:text-gray-200">
                                Total Pengeluaran
                            </td>
                            <td class="py-3 pr-4 text-right font-semibold text-red-500 dark:text-red-400">
                                {{ rupiah($report['total_expense']) }}
                            </td>
                            <td class="py-3 pr-4 text-right text-gray-500 dark:text-gray-400">
                                {{ rupiah($report['prev_expense']) }}
                            </td>
                            <td class="py-3 text-right">
                                @if($report['delta_expense_pct'] !== null)
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full
                                        {{ $report['delta_expense_pct'] <= 0
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                            : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                        {{ $report['delta_expense_pct'] >= 0 ? '+' : '' }}{{ $report['delta_expense_pct'] }}%
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Net --}}
                        <tr class="bg-gray-50 dark:bg-gray-900">
                            <td class="py-3 pr-4 font-bold text-gray-900 dark:text-white">
                                Net (Sisa)
                            </td>
                            <td class="py-3 pr-4 text-right font-bold
                                {{ $report['net'] >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-red-500' }}">
                                {{ rupiah($report['net']) }}
                            </td>
                            <td class="py-3 pr-4 text-right text-gray-500 dark:text-gray-400 font-medium">
                                {{ rupiah($report['prev_net']) }}
                            </td>
                            <td class="py-3 text-right">
                                <span class="text-xs text-gray-400">—</span>
                            </td>
                        </tr>

                        {{-- Saving Rate --}}
                        <tr>
                            <td class="py-3 pr-4 font-medium text-gray-700 dark:text-gray-200">
                                Saving Rate
                            </td>
                            <td class="py-3 pr-4 text-right font-semibold text-indigo-600 dark:text-indigo-400">
                                {{ $report['saving_rate'] }}%
                            </td>
                            <td class="py-3 pr-4 text-right text-gray-500 dark:text-gray-400">
                                {{ $report['prev_saving_rate'] }}%
                            </td>
                            <td class="py-3 text-right">
                                <span class="text-xs text-gray-400">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- SECTION 2: PENGELUARAN PER KATEGORI --}}
        {{-- ─────────────────────────────────────────────────────── --}}
        <div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="text-indigo-500">02</span>
                Pengeluaran per Kategori
            </h2>

            @if(empty($report['by_category']))
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">Tidak ada data kategori.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left py-2 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase">Kategori</th>
                                <th class="text-right py-2 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase">Budget Bulanan</th>
                                <th class="text-right py-2 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase">Realisasi</th>
                                <th class="text-right py-2 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase">Selisih</th>
                                <th class="text-right py-2 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-750">
                            @foreach($report['by_category'] as $cat)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $typePill = match($cat['category_type']) {
                                                    'want'       => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
                                                    'need'       => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                                                    'saving'     => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                                    'investment' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300',
                                                    default      => 'bg-gray-100 text-gray-600',
                                                };
                                            @endphp
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $typePill }}">
                                                {{ ucfirst($cat['category_type']) }}
                                            </span>
                                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ $cat['category_name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 text-right text-gray-600 dark:text-gray-300">
                                        {{ $cat['monthly_budget'] > 0 ? rupiah($cat['monthly_budget']) : '—' }}
                                    </td>
                                    <td class="py-3 pr-4 text-right font-semibold text-gray-800 dark:text-gray-100">
                                        {{ rupiah($cat['realisasi']) }}
                                    </td>
                                    <td class="py-3 pr-4 text-right font-medium
                                        {{ $cat['selisih'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                                        {{ $cat['monthly_budget'] > 0 ? rupiah($cat['selisih']) : '—' }}
                                    </td>
                                    <td class="py-3 text-right">
                                        @if($cat['monthly_budget'] > 0)
                                            <span class="text-xs font-bold
                                                {{ $cat['pct'] > 100 ? 'text-red-500' : ($cat['pct'] > 75 ? 'text-orange-500' : 'text-gray-600 dark:text-gray-300') }}">
                                                {{ $cat['pct'] }}%
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- SECTION 3: TOP 5 PENGELUARAN TERBESAR --}}
        {{-- ─────────────────────────────────────────────────────── --}}
        <div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="text-indigo-500">03</span>
                Top 5 Pengeluaran Terbesar
            </h2>

            @if($report['top_spending']->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">Belum ada data pengeluaran.</p>
            @else
                <div class="space-y-3">
                    @foreach($report['top_spending'] as $index => $item)
                        @php
                            $maxAmt   = $report['top_spending']->max('total');
                            $pctBar   = $maxAmt > 0 ? round(($item->total / $maxAmt) * 100) : 0;
                            $rankBg   = ['bg-red-500', 'bg-orange-400', 'bg-yellow-400', 'bg-blue-400', 'bg-gray-300'];
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white
                                         {{ $rankBg[$index] ?? 'bg-gray-300' }} shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">
                                        {{ $item->description ?: 'Tanpa Deskripsi' }}
                                    </span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-100 ml-3 shrink-0">
                                        {{ rupiah($item->total) }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $rankBg[$index] ?? 'bg-gray-300' }}"
                                         style="width: {{ $pctBar }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- SECTION 4: FINANCIAL HEALTH SCORE --}}
        {{-- ─────────────────────────────────────────────────────── --}}
        @php
            $hs          = $report['health_score'];
            $streak      = $report['streak_info'];
            $tierColors  = [
                'indigo' => 'text-indigo-600 dark:text-indigo-400',
                'green'  => 'text-green-600 dark:text-green-400',
                'blue'   => 'text-blue-600 dark:text-blue-400',
                'yellow' => 'text-yellow-600 dark:text-yellow-400',
                'red'    => 'text-red-600 dark:text-red-400',
            ];
            $tcls = $tierColors[$hs['tier_color']] ?? 'text-blue-600 dark:text-blue-400';
        @endphp

        <div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                <span class="text-indigo-500">04</span>
                Financial Health Score
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                {{-- Score --}}
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-xl">
                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider mb-1">Score</p>
                    <p class="text-3xl font-extrabold {{ $tcls }}">{{ $hs['final_score'] }}</p>
                    <p class="text-xs text-gray-400">/100</p>
                </div>
                {{-- Tier --}}
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-xl">
                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider mb-1">Tier</p>
                    <p class="text-3xl font-extrabold {{ $tcls }}">{{ $hs['tier'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $hs['tier_label'] }}</p>
                </div>
                {{-- Streak --}}
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-xl">
                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider mb-1">Best Streak</p>
                    <p class="text-3xl font-extrabold text-amber-500">{{ $streak['best_streak'] }}</p>
                    <p class="text-xs text-gray-400">hari</p>
                </div>
                {{-- Badges --}}
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-xl">
                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider mb-1">Badges</p>
                    <p class="text-3xl font-extrabold text-indigo-500">{{ $streak['total_badges'] }}</p>
                    <p class="text-xs text-gray-400">diraih</p>
                </div>
            </div>

            {{-- Komponen score --}}
            <div class="space-y-3">
                @foreach([
                    ['label' => 'BAR — Budget Adherence Rate', 'val' => $hs['BAR'], 'weight' => '40%', 'color' => 'bg-blue-500'],
                    ['label' => 'SR — Saving Rate',            'val' => $hs['SR'],  'weight' => '35%', 'color' => 'bg-emerald-500'],
                    ['label' => 'SC — Streak Consistency',     'val' => $hs['SC'],  'weight' => '25%', 'color' => 'bg-amber-500'],
                ] as $comp)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-600 dark:text-gray-300 font-medium">
                                {{ $comp['label'] }}
                                <span class="text-gray-400 ml-1">(bobot {{ $comp['weight'] }})</span>
                            </span>
                            <span class="font-bold text-gray-800 dark:text-gray-100">{{ $comp['val'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $comp['color'] }}"
                                 style="width: {{ min($comp['val'], 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-gray-300 dark:text-gray-600 mt-4 italic">
                * Bobot Score = keputusan desain sistem, bukan konstanta ilmiah yang telah dikalibrasi secara empiris.
                Formula: Score = (0.40 × BAR) + (0.35 × SR) + (0.25 × SC)
            </p>
        </div>

        {{-- ─────────────────────────────────────────────────────── --}}
        {{-- SECTION 5: GRAFIK TREN HARIAN (Bar Chart) --}}
        {{-- ─────────────────────────────────────────────────────── --}}
        <div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                <span class="text-indigo-500">05</span>
                Tren Harian Pengeluaran — {{ $report['month_name'] }}
            </h2>

            @if(array_sum($report['daily_data']) == 0)
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">
                    Tidak ada data pengeluaran harian bulan ini.
                </p>
            @else
                <div class="relative h-64">
                    <canvas id="chart-report-daily"></canvas>
                </div>
            @endif
        </div>

        {{-- Footer laporan --}}
        <div class="text-center py-4">
            <p class="text-xs text-gray-400 dark:text-gray-600">
                Digenerate oleh FinTrack — {{ now()->format('d/m/Y H:i') }}
                | Hanya mencakup transaksi dengan status <em>verified</em>
            </p>
        </div>

    </div>{{-- /container --}}
</div>

@endsection

@push('scripts')
<script>
(function() {
    const ctx = document.getElementById('chart-report-daily');
    if (!ctx) return;

    const isDark      = document.documentElement.classList.contains('dark');
    const gridColor   = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.05)';
    const tickColor   = isDark ? '#9ca3af' : '#6b7280';

    function formatRupiah(val) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
    }
    function formatRupiahShort(val) {
        if (Math.abs(val) >= 1_000_000_000) return (val / 1_000_000_000).toFixed(1) + ' M';
        if (Math.abs(val) >= 1_000_000)     return (val / 1_000_000).toFixed(1) + ' Jt';
        if (Math.abs(val) >= 1_000)         return Math.round(val / 1_000) + ' Rb';
        return val.toString();
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($report['daily_labels']),
            datasets: [{
                label: 'Pengeluaran',
                data: @json($report['daily_data']),
                backgroundColor: 'rgba(99, 102, 241, 0.75)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: (ctx) => `Tanggal ${ctx[0].label}`,
                        label: (ctx) => ` Pengeluaran: ${formatRupiah(ctx.parsed.y)}`,
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
                    grid: { display: false },
                    ticks: { color: tickColor, font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: tickColor,
                        font: { size: 10 },
                        callback: (v) => formatRupiahShort(v)
                    }
                }
            }
        }
    });
})();
</script>
@endpush
