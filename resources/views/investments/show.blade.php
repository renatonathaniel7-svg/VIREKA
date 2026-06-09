@extends('layouts.app')

@section('title', 'Detail Investasi ')
@section('page-title', 'Detail Investasi')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Back Button ──────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <a href="{{ route('investments.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Portfolio
            </a>
        </div>

        {{-- ── Main Detail Card ─────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $investment->allocation_type === 'investment'
                                ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300'
                                : 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300' }}">
                            {{ $investment->allocation_type === 'investment' ? '📈 Investasi' : '🏦 Tabungan' }}
                        </span>
                        @if($hasPendingWithdrawal)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                            ⏳ Pencairan Pending
                        </span>
                        @endif
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $investment->instrument ?? 'Instrumen Tidak Disebutkan' }}
                    </h1>
                    @if($investment->note)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $investment->note }}</p>
                    @endif
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        Ditambahkan {{ $investment->invested_at ? \Carbon\Carbon::parse($investment->invested_at)->format('d M Y') : $investment->created_at->format('d M Y') }}
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 flex-shrink-0">
                    <a href="{{ route('investments.edit', $investment->id) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Update Nilai
                    </a>

                    @if($hasPendingWithdrawal)
                    <button disabled
                            title="Sudah ada pencairan pending"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-sm font-medium rounded-xl cursor-not-allowed opacity-60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Request Pencairan
                    </button>
                    @else
                    <a href="{{ route('withdrawals.create', $investment->id) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Request Pencairan
                    </a>
                    @endif
                </div>
            </div>

            {{-- ── Stats Grid ─────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Modal Awal</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white font-mono">
                        Rp {{ number_format($investment->initial_amount, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Nilai Sekarang</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white font-mono">
                        Rp {{ number_format($investment->current_value, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Return</p>
                    <p class="text-lg font-bold {{ $returnAmount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-mono">
                        {{ $returnAmount >= 0 ? '+' : '-' }}Rp {{ number_format(abs($returnAmount), 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/40 rounded-xl p-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Return %</p>
                    <p class="text-lg font-bold {{ ($investment->return_pct ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ ($investment->return_pct ?? 0) >= 0 ? '+' : '' }}{{ number_format($investment->return_pct ?? 0, 2) }}%
                    </p>
                </div>
            </div>

            {{-- ── Progress Bar: Nilai Sekarang vs Modal Awal ─────────────────── --}}
            <div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Progress Nilai Investasi
                    </span>
                    <span class="text-sm font-semibold {{ $returnAmount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ number_format($progressPct, 1) }}% dari modal
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                    {{-- Baseline (100% = modal awal) --}}
                    <div class="relative h-3">
                        <div class="absolute inset-0 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                        <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-500
                            {{ $progressPct >= 100 ? 'bg-green-500' : 'bg-red-500' }}"
                             style="width: {{ min(100, $progressPct) }}%">
                        </div>
                        {{-- Baseline marker at 100% --}}
                        <div class="absolute inset-y-0 left-[100%] w-0.5 bg-gray-900/20 dark:bg-white/20"
                             style="max-width: 100%"></div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-gray-400 dark:text-gray-500 mt-1">
                    <span>Rp 0</span>
                    <span>Modal: Rp {{ number_format($investment->initial_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ── Withdrawal History ───────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                    Riwayat Pencairan
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Semua permintaan pencairan dari instrumen ini
                </p>
            </div>

            @if($withdrawals->isEmpty())
            <div class="px-6 py-10 text-center">
                <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada riwayat pencairan.</p>
                @if(!$hasPendingWithdrawal)
                <a href="{{ route('withdrawals.create', $investment->id) }}"
                   class="inline-flex items-center gap-1 mt-3 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                    Ajukan pencairan pertama
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endif
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/30">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diminta</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diterima</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                {{ $withdrawal->created_at->format('d M Y') }}
                                <div class="text-xs text-gray-400">{{ $withdrawal->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-gray-700 dark:text-gray-300">
                                Rp {{ number_format($withdrawal->amount_requested, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-mono text-gray-700 dark:text-gray-300">
                                {{ $withdrawal->amount_received
                                    ? 'Rp ' . number_format($withdrawal->amount_received, 0, ',', '.')
                                    : '—' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusConfig = [
                                        'pending'   => ['bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300', '⏳ Pending'],
                                        'verified'  => ['bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300', '✅ Terverifikasi'],
                                        'completed' => ['bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300', '✓ Selesai'],
                                        'rejected'  => ['bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300', '✕ Ditolak'],
                                    ];
                                    [$statusClass, $statusLabel] = $statusConfig[$withdrawal->status] ?? ['bg-gray-100 text-gray-800', $withdrawal->status];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($withdrawal->status === 'verified')
                                <form action="{{ route('withdrawals.complete', $withdrawal->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Konfirmasi pencairan Rp {{ number_format($withdrawal->amount_requested, 0, ',', '.') }}?')"
                                            class="text-xs px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                        Selesaikan
                                    </button>
                                </form>
                                @elseif($withdrawal->status === 'pending')
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('verifications.create', ['type' => 'withdrawal','id' => $withdrawal->id]) }}"
                                       class="text-xs px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg font-medium hover:bg-blue-100 transition-colors">
                                        Upload Bukti
                                    </a>
                                    <form action="{{ route('withdrawals.reject', $withdrawal->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('Batalkan permintaan pencairan ini?')"
                                                class="text-xs px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg font-medium hover:bg-red-100 transition-colors">
                                            Batal
                                        </button>
                                    </form>
                                </div>
                                @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ── Danger Zone: Delete Investment ──────────────────────────────── --}}
        <div class="mt-6 p-4 border border-red-200 dark:border-red-800/50 rounded-xl bg-red-50 dark:bg-red-900/10">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-red-800 dark:text-red-300">Hapus Instrumen</p>
                    <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">
                        Hanya bisa dihapus jika tidak ada riwayat pencairan.
                    </p>
                </div>
                @if($withdrawals->isEmpty())
                <form action="{{ route('investments.destroy', $investment->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            onclick="return confirm('Yakin ingin menghapus instrumen ini?')"
                            class="text-xs px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                        Hapus Instrumen
                    </button>
                </form>
                @else
                <button disabled
                        title="Tidak bisa dihapus — ada riwayat pencairan"
                        class="text-xs px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-400 rounded-lg font-medium cursor-not-allowed">
                    Hapus Instrumen
                </button>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
