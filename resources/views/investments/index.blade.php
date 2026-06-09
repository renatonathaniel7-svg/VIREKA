{{-- investments/index.blade.php --}}
{{-- Portfolio Investasi & Tabungan — Overview dengan summary cards dan tabel per instrumen --}}

@extends('layouts.app')

@section('title', 'Portfolio Investasi & Tabungan')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Page Header ──────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Portfolio Investasi &amp; Tabungan
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Dana yang dialokasikan ke instrumen saving &amp; investasi
                </p>
            </div>
            <a href="{{ route('investments.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Instrumen
            </a>
        </div>

        {{-- ── Summary Cards ────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">

            {{-- Total Pool --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pool</span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format($totalCurrent, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    Modal: Rp {{ number_format($totalInitial, 0, ',', '.') }}
                </p>
            </div>

            {{-- Total Return --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 {{ $totalReturn >= 0 ? 'bg-green-100 dark:bg-green-900/40' : 'bg-red-100 dark:bg-red-900/40' }} rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $totalReturn >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($totalReturn >= 0)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            @endif
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Return</span>
                </div>
                <p class="text-2xl font-bold {{ $totalReturn >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $totalReturn >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalReturn), 0, ',', '.') }}
                </p>
                <p class="text-xs {{ $totalReturn >= 0 ? 'text-green-500' : 'text-red-500' }} mt-1">
                    {{ $totalReturnPct >= 0 ? '+' : '' }}{{ number_format($totalReturnPct, 2) }}% dari modal
                </p>
            </div>

            {{-- Jumlah Instrumen --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/40 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Instrumen</span>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $investments->count() }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    {{ $investments->where('allocation_type', 'investment')->count() }} investasi ·
                    {{ $investments->where('allocation_type', 'saving')->count() }} tabungan
                </p>
            </div>
        </div>

        {{-- ── Pending Withdrawals Banner ───────────────────────────────────── --}}
        @if($pendingWithdrawals->count() > 0)
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-amber-800 dark:text-amber-300">
                <span class="font-semibold">Pencairan dalam proses:</span>
                Rp {{ number_format($totalPending, 0, ',', '.') }}
                ({{ $pendingWithdrawals->count() }} permintaan menunggu verifikasi)
            </p>
        </div>
        @endif

        {{-- ── Main Table ────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            @if($investments->isEmpty())
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada instrumen</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Mulai alokasikan dana ke tabungan atau investasi.</p>
                    <a href="{{ route('investments.create') }}"
                       class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                        + Tambah instrumen pertama
                    </a>
                </div>
            @else
                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Instrumen</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Modal Awal</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nilai Sekarang</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Return</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @foreach($investments as $investment)
                            @php
                                $returnAmount = $investment->current_value - $investment->initial_amount;
                                $returnPct    = $investment->return_pct ?? 0;
                                $returnClass  = $returnAmount > 0
                                    ? 'text-green-600 dark:text-green-400'
                                    : ($returnAmount < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400 dark:text-gray-500');
                                $returnPrefix = $returnAmount > 0 ? '+' : ($returnAmount < 0 ? '-' : '');
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $investment->instrument ?? 'Tidak disebutkan' }}
                                    </div>
                                    @if($investment->note)
                                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate max-w-xs">
                                        {{ $investment->note }}
                                    </div>
                                    @endif
                                    {{-- Pending badge --}}
                                    @php
                                        $isPending = $pendingWithdrawals->where('investment_entry_id', $investment->id)->isNotEmpty();
                                    @endphp
                                    @if($isPending)
                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        ⏳ Pencairan pending
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $investment->allocation_type === 'investment'
                                            ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300'
                                            : 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300' }}">
                                        {{ $investment->allocation_type === 'investment' ? '📈 Investasi' : '🏦 Tabungan' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-gray-700 dark:text-gray-300">
                                    Rp {{ number_format($investment->initial_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($investment->current_value, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="font-mono font-semibold {{ $returnClass }}">
                                        {{ $returnPrefix }}Rp {{ number_format(abs($returnAmount), 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs {{ $returnClass }} opacity-75">
                                        {{ $returnPct >= 0 ? '+' : '' }}{{ number_format($returnPct, 2) }}%
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('investments.show', $investment->id) }}"
                                           class="text-xs px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium">
                                            Detail
                                        </a>
                                        <a href="{{ route('investments.edit', $investment->id) }}"
                                           class="text-xs px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors font-medium">
                                            Update
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Table Footer Summary --}}
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ $investments->count() }} instrumen terdaftar
                        </span>
                        <div class="flex items-center gap-6">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Total modal: </span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($totalInitial, 0, ',', '.') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Total nilai: </span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($totalCurrent, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Note tentang Investment Pool ─────────────────────────────────── --}}
        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/50 rounded-xl">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-300">
                    <p class="font-semibold mb-1">Tentang Investment Pool</p>
                    <p class="text-blue-700 dark:text-blue-400">
                        Dana di portfolio ini terpisah dari liquid balance. Untuk memindahkan dana kembali ke liquid,
                        gunakan fitur <strong>Request Pencairan</strong> dan upload bukti transaksi.
                        Pencairan memerlukan verifikasi screenshot sebelum dana tercatat masuk ke balance.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
