{{-- investments/edit.blade.php --}}
{{-- Form update nilai investasi (current_value, return_pct, note, instrument) --}}
{{-- initial_amount TIDAK bisa diubah — design constraint --}}

@extends('layouts.app')

@section('title', 'Update Nilai — ' . ($investment->instrument ?? 'Instrumen'))

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
                Kembali ke Detail
            </a>
        </div>

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Update Nilai Investasi</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Perbarui nilai terkini instrumen Anda. Return % akan dihitung otomatis.
            </p>
        </div>

        {{-- ── Read-only Info Card ──────────────────────────────────────────────── --}}
        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 mb-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-0.5">Modal Awal (tidak bisa diubah)</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white font-mono">
                        Rp {{ number_format($investment->initial_amount, 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-0.5">Nilai Sekarang</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white font-mono">
                        Rp {{ number_format($investment->current_value, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    ⚠️ Nilai modal awal dikunci untuk menjaga integritas catatan keuangan.
                </p>
            </div>
        </div>

        {{-- ── Form ───────────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6"
             x-data="{
                currentValue: {{ $investment->current_value }},
                initialAmount: {{ $investment->initial_amount }},
                get returnAmount() { return this.currentValue - this.initialAmount; },
                get returnPct() {
                    if (this.initialAmount === 0) return 0;
                    return ((this.currentValue - this.initialAmount) / this.initialAmount * 100).toFixed(2);
                }
             }">
            <form action="{{ route('investments.update', $investment->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="update">

                {{-- ── Instrument Name ──────────────────────────────────────── --}}
                <div class="mb-5">
                    <label for="instrument" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Nama Instrumen
                    </label>
                    <input type="text"
                           id="instrument"
                           name="instrument"
                           value="{{ old('instrument', $investment->instrument) }}"
                           placeholder="e.g. BRI Deposito, BBCA Saham"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                    @error('instrument')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Allocation Type ──────────────────────────────────────── --}}
                <div class="mb-5">
                    <label for="allocation_type" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Jenis Alokasi <span class="text-red-500">*</span>
                    </label>
                    <select id="allocation_type"
                            name="allocation_type"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                        <option value="saving" {{ old('allocation_type', $investment->allocation_type) === 'saving' ? 'selected' : '' }}>🏦 Tabungan</option>
                        <option value="investment" {{ old('allocation_type', $investment->allocation_type) === 'investment' ? 'selected' : '' }}>📈 Investasi</option>
                    </select>
                    @error('allocation_type')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Current Value (with live preview) ──────────────────────── --}}
                <div class="mb-5">
                    <label for="current_value" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Nilai Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 inset-y-0 flex items-center text-gray-500 dark:text-gray-400 font-medium text-sm">Rp</span>
                        <input type="number"
                               id="current_value"
                               name="current_value"
                               x-model="currentValue"
                               value="{{ old('current_value', $investment->current_value) }}"
                               min="0"
                               step="1000"
                               class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                    </div>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Input manual — Return % akan dihitung otomatis.
                    </p>
                    @error('current_value')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Live Return Preview ───────────────────────────────────── --}}
                <div class="mb-5 p-4 rounded-xl border"
                     :class="returnAmount >= 0
                         ? 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800/50'
                         : 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800/50'">
                    <p class="text-xs font-semibold mb-2"
                       :class="returnAmount >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'">
                        Preview Return (otomatis)
                    </p>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-2xl font-bold font-mono"
                                  :class="returnAmount >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'">
                                <span x-text="returnAmount >= 0 ? '+' : ''"></span>Rp
                                <span x-text="Math.abs(returnAmount).toLocaleString('id-ID')"></span>
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xl font-bold"
                                  :class="returnAmount >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'">
                                <span x-text="returnAmount >= 0 ? '+' : ''"></span><span x-text="returnPct"></span>%
                            </span>
                        </div>
                    </div>
                </div>

                {{-- ── Note ─────────────────────────────────────────────────── --}}
                <div class="mb-6">
                    <label for="note" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <textarea id="note"
                              name="note"
                              rows="3"
                              placeholder="Update kondisi investasi, alasan perubahan nilai..."
                              class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm resize-none">{{ old('note', $investment->note) }}</textarea>
                    @error('note')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Submit ───────────────────────────────────────────────── --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="flex-1 sm:flex-none px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('investments.show', $investment->id) }}"
                       class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
