{{-- investments/withdrawal/create.blade.php --}}
{{-- Form request pencairan dari investment pool --}}

@extends('layouts.app')

@section('title', 'Request Pencairan')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Back Button ──────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <a href="{{ route('investments.show', $investment->id) }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke {{ $investment->instrument ?? 'Detail Investasi' }}
            </a>
        </div>

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Request Pencairan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Ajukan pencairan dari investment pool ke liquid balance.
            </p>
        </div>

        {{-- ── Pending Warning ─────────────────────────────────────────────── --}}
        @if($pendingWithdrawal)
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 rounded-xl">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.924-.834-2.694 0L3.17 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Pencairan Sudah Pending</p>
                    <p class="text-sm text-amber-700 dark:text-amber-400 mt-0.5">
                        Ada permintaan pencairan sebesar
                        <strong>Rp {{ number_format($pendingWithdrawal->amount_requested, 0, ',', '.') }}</strong>
                        yang sedang diproses. Tunggu hingga selesai sebelum mengajukan permintaan baru.
                    </p>
                    <a href="{{ route('verifications.create', ['type' => 'withdrawal','id' => $pendingWithdrawal->id]) }}"
                       class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-amber-700 dark:text-amber-300 hover:underline">
                        Upload bukti pencairan
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Investment Info Card ─────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl flex items-center justify-center text-xl">
                    {{ $investment->allocation_type === 'investment' ? '📈' : '🏦' }}
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $investment->instrument ?? 'Instrumen' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $investment->allocation_type === 'investment' ? 'Investasi' : 'Tabungan' }}
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-0.5">Modal Awal</p>
                    <p class="font-semibold text-gray-900 dark:text-white font-mono text-sm">
                        Rp {{ number_format($investment->initial_amount, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-0.5">Nilai Sekarang (maks. cairkan)</p>
                    <p class="font-semibold text-green-700 dark:text-green-400 font-mono text-sm">
                        Rp {{ number_format($investment->current_value, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        @if(!$pendingWithdrawal)
        {{-- ── Withdrawal Form ─────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <form action="{{ route('withdrawals.store', $investment->id) }}" method="POST"
                  x-data="{
                      amount: 0,
                      maxAmount: {{ $investment->current_value }},
                      get remaining() { return this.maxAmount - this.amount; }
                  }">
                @csrf

                {{-- ── Amount Requested ─────────────────────────────────────── --}}
                <div class="mb-5">
                    <label for="amount_requested" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Jumlah Pencairan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 inset-y-0 flex items-center text-gray-500 dark:text-gray-400 font-medium text-sm">Rp</span>
                        <input type="number"
                               id="amount_requested"
                               name="amount_requested"
                               x-model="amount"
                               value="{{ old('amount_requested') }}"
                               min="10000"
                               max="{{ $investment->current_value }}"
                               step="1000"
                               placeholder="0"
                               class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                    </div>

                    {{-- Quick amount buttons --}}
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach([25, 50, 75, 100] as $pct)
                        <button type="button"
                                @click="amount = Math.round(maxAmount * {{ $pct/100 }})"
                                class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/30 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors font-medium">
                            {{ $pct }}%
                        </button>
                        @endforeach
                    </div>

                    {{-- Sisa setelah pencairan --}}
                    <div class="mt-2 text-xs text-gray-400 dark:text-gray-500" x-show="amount > 0">
                        Sisa di portfolio:
                        <span class="font-semibold text-gray-700 dark:text-gray-300">
                            Rp <span x-text="Math.max(0, remaining).toLocaleString('id-ID')"></span>
                        </span>
                    </div>

                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Minimal Rp 10.000 · Maksimal Rp {{ number_format($investment->current_value, 0, ',', '.') }}
                    </p>

                    @error('amount_requested')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Note ─────────────────────────────────────────────────── --}}
                <div class="mb-6">
                    <label for="note" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Alasan Pencairan <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <textarea id="note"
                              name="note"
                              rows="3"
                              placeholder="Contoh: kebutuhan mendesak, rebalancing portfolio, dll."
                              class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm resize-none">{{ old('note') }}</textarea>
                    @error('note')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Process Info ──────────────────────────────────────────── --}}
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/50 rounded-xl">
                    <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-2">Proses Pencairan</p>
                    <ol class="text-xs text-blue-700 dark:text-blue-400 space-y-1">
                        <li>1. Submit request ini → status: <strong>Pending</strong></li>
                        <li>2. Lakukan pencairan di platform investasi Anda</li>
                        <li>3. Upload screenshot bukti pencairan</li>
                        <li>4. AI verifikasi screenshot (atau manual jika gagal)</li>
                        <li>5. Konfirmasi → dana masuk ke <strong>Liquid Balance</strong></li>
                    </ol>
                </div>

                {{-- ── Submit ───────────────────────────────────────────────── --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="flex-1 sm:flex-none px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        Ajukan Pencairan
                    </button>
                    <a href="{{ route('investments.show', $investment->id) }}"
                       class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
        @endif

    </div>
</div>
@endsection
