{{-- resources/views/verifications/show.blade.php --}}
{{-- Halaman detail hasil verifikasi: perbandingan data AI vs input user --}}

@extends('layouts.app')

@section('title', 'Hasil Verifikasi')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 px-4">

    <div class="max-w-3xl mx-auto">

        {{-- ── Flash Messages ───────────────────────────────────────────── --}}
        @foreach(['success', 'warning', 'info', 'error'] as $msgType)
            @if(session($msgType))
            <div class="mb-6 p-4 rounded-lg border
                        {{ $msgType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : '' }}
                        {{ $msgType === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' : '' }}
                        {{ $msgType === 'info'    ? 'bg-blue-50 border-blue-200 text-blue-800'   : '' }}
                        {{ $msgType === 'error'   ? 'bg-red-50 border-red-200 text-red-800'     : '' }}">
                {{ session($msgType) }}
            </div>
            @endif
        @endforeach

        {{-- ── Header + Badge Besar ────────────────────────────────────── --}}
        <div class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-3">
            <div>
                <a href="{{ $verification->reference_type === 'expense'
                    ? route('expenses.show', $verification->reference_id)
                    : route('income.show', $verification->reference_id) }}"
                   class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali ke Detail Transaksi
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Hasil Verifikasi</h1>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">
                    ID Verifikasi #{{ $verification->id }} —
                    {{ \Carbon\Carbon::parse($verification->created_at)->isoFormat('D MMMM YYYY, HH:mm') }}
                </p>
            </div>
            <div class="flex-shrink-0 ml-6">
                @include('verifications._status_badge', [
                    'status' => $verification->status,
                    'size'   => 'lg',
                ])
            </div>
        </div>

        {{-- ── Status Result Box ───────────────────────────────────────── --}}
        @if($verification->status === 'verified')
        <div class="bg-green-500 dark:bg-green-800 border border-green-200 dark:border-green-950/60 rounded-xl p-5 mb-6 flex items-start gap-3">
            <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-green-800 dark:text-green-300 font-semibold">Transaksi Berhasil Diverifikasi</p>
                <p class="text-green-700 dark:text-green-400 text-sm mt-1">
                    Data yang kamu input sesuai dengan screenshot yang diupload.
                    Transaksi ini sudah masuk ke verified balance dan diperhitungkan dalam Financial Health Score.
                </p>
            </div>
        </div>

        @elseif($verification->status === 'flagged')
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="text-amber-800 font-semibold">Terdapat Perbedaan Data</p>
                    @if($verification->flag_reason)
                    <p class="text-amber-700 text-sm mt-1">{{ $verification->flag_reason }}</p>
                    @endif
                    <p class="text-amber-600 text-xs mt-2">
                        Transaksi ini masuk ke shadow balance dan tidak mempengaruhi score.
                        Pastikan kamu upload screenshot yang benar, lalu coba lagi.
                    </p>
                </div>
            </div>
            <div class="mt-4 flex gap-3">
                <form action="{{ route('verifications.retry', $verification->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Coba Lagi dengan Screenshot Sama
                    </button>
                </form>
                <a href="{{ route('verifications.create', [$verification->reference_type, $verification->reference_id]) }}"
                   class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Upload Screenshot Baru
                </a>
            </div>
        </div>

        @elseif($verification->status === 'pending')
        <div class="bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-800 rounded-xl p-5 mb-6 flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 0 12 0v4a8 8 0 00-8 8H4z"></path>
            </svg>
            <div>
                <p class="text-blue-800 font-semibold">Menunggu Review Manual</p>
                <p class="text-blue-700 text-sm mt-1">
                    Sistem AI tidak dapat membaca screenshot secara otomatis.
                    @if($verification->flag_reason)
                        {{ $verification->flag_reason }}
                    @else
                        Transaksi ini akan diproses secara manual.
                    @endif
                </p>
            </div>
        </div>

        @if($verification->status === 'pending')
<div class="mb-6 flex flex-wrap gap-3">

    {{-- Konfirmasi --}}
    <form action="{{ route('verifications.confirm', $verification->id) }}"
          method="POST">
        @csrf
        <button type="submit"
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg transition-colors">
            ✅ Konfirmasi
        </button>
    </form>

    {{-- Upload Ulang --}}
    <a href="{{ route('verifications.reupload', $verification->id) }}"
       class="bg-amber-500 hover:bg-amber-600 text-white font-medium px-4 py-2 rounded-lg transition-colors">
        🔄 Upload Ulang
    </a>

    {{-- Tidak Sesuai --}}
    <form action="{{ route('verifications.flag', $verification->id) }}"
          method="POST">
        @csrf
        <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-colors">
            🚩 Tidak Sesuai
        </button>
    </form>

    </div>
    @endif

        @elseif($verification->status === 'unverified')
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6 flex items-start gap-3">
            <svg class="w-6 h-6 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-gray-700 font-semibold">Ditandai sebagai Transaksi Tunai</p>
                <p class="text-gray-500 text-sm mt-1">
                    Transaksi ini dicatat sebagai shadow balance.
                    Tidak mempengaruhi verified balance dan Financial Health Score.
                </p>
            </div>
        </div>
        @endif

        {{-- ── Perbandingan Data: Input User vs AI ─────────────────────── --}}
        @if($aiData)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">Perbandingan Data</h2>

            <div class="grid grid-cols-2 gap-6">
                {{-- Kolom Kiri: Data Input User --}}
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                        <p class="text-sm font-semibold text-gray-700">Data yang Kamu Input</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-400">Nominal</p>
                            <p class="text-base font-bold text-gray-900">
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Tanggal</p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($transaction->date)->isoFormat('D MMMM YYYY') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Data Terdeteksi AI --}}
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        <p class="text-sm font-semibold text-gray-700">Data Terdeteksi AI</p>
                    </div>

                    <div class="space-y-3">
                        {{-- Nominal --}}
                        <div>
                            <p class="text-xs text-gray-400">Nominal</p>
                            @php
                                $amountMatch = isset($aiData['amount']) &&
                                    abs($aiData['amount'] - $transaction->amount) / max($transaction->amount, 1) * 100 <= 5;
                            @endphp
                            <p class="text-base font-bold {{ $amountMatch ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ isset($aiData['amount']) ? number_format($aiData['amount'], 0, ',', '.') : '—' }}
                                @if($amountMatch)
                                    <span class="text-green-500 text-sm">✓</span>
                                @else
                                    <span class="text-red-500 text-sm">✗</span>
                                @endif
                            </p>
                        </div>

                        {{-- Tanggal --}}
                        <div>
                            <p class="text-xs text-gray-400">Tanggal</p>
                            @php
                                $aiDate   = $aiData['date'] ?? null;
                                $userDate = \Carbon\Carbon::parse($transaction->date)->format('Y-m-d');
                                $dateMatch = $aiDate && \Carbon\Carbon::parse($aiDate)->format('Y-m-d') === $userDate;
                            @endphp
                            <p class="text-sm font-medium {{ $dateMatch ? 'text-green-600' : 'text-red-600' }}">
                                {{ $aiDate ? \Carbon\Carbon::parse($aiDate)->isoFormat('D MMMM YYYY') : '—' }}
                                @if($dateMatch)
                                    <span class="text-green-500 text-sm">✓</span>
                                @elseif($aiDate)
                                    <span class="text-red-500 text-sm">✗</span>
                                @endif
                            </p>
                        </div>

                        {{-- Sumber (hanya dari AI) --}}
                        @if(isset($aiData['source']))
                        <div>
                            <p class="text-xs text-gray-400">Sumber Terdeteksi</p>
                            <p class="text-sm font-medium text-gray-700">{{ $aiData['source'] }}</p>
                        </div>
                        @endif

                        {{-- Confidence --}}
                        @if(isset($aiData['confidence']))
                        <div>
                            <p class="text-xs text-gray-400">Confidence AI</p>
                            @php $confidencePct = round($aiData['confidence'] * 100); @endphp
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full
                                                {{ $confidencePct >= 70 ? 'bg-green-500' : 'bg-amber-400' }}"
                                         style="width: {{ $confidencePct }}%"></div>
                                </div>
                                <span class="text-sm font-semibold {{ $confidencePct >= 70 ? 'text-green-600' : 'text-amber-600' }}">
                                    {{ $confidencePct }}%
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Screenshot Preview ──────────────────────────────────────── --}}
        @if($verification->screenshot_path)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Screenshot yang Diupload</h2>
            @php
                $screenshotUrl = '/' . ltrim($verification->screenshot_path, '/');
                $ext = pathinfo($verification->screenshot_path, PATHINFO_EXTENSION);
            @endphp
            @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
            <img src="{{ $screenshotUrl }}"
                 alt="Screenshot verifikasi"
                 class="max-w-full h-auto rounded-lg border border-gray-200 dark:border-gray-700 max-h-80 object-contain mx-auto block">
            @else
            <a href="{{ $screenshotUrl }}" target="_blank"
               class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Lihat File Screenshot
            </a>
            @endif
        </div>
        @endif

        {{-- ── Info Catatan Sistem ─────────────────────────────────────── --}}
        <div class="text-center text-xs text-gray-400 pb-4">
            Sistem verifikasi FinTrack menggunakan Gemini 1.5 Flash + Tesseract OCR.
            Toleransi discrepancy: 5%. Akurasi OCR bervariasi tergantung format mutasi bank.
        </div>

    </div>
</div>
@endsection
