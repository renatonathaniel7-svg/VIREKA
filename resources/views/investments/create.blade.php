{{-- investments/create.blade.php --}}
{{-- Form tambah instrumen investasi/tabungan baru --}}

@extends('layouts.app')

@section('title', 'Tambah Instrumen Investasi')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

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

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Instrumen</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Dana yang dialokasikan akan dipindahkan dari liquid balance ke investment pool.
            </p>
        </div>

        {{-- ── Form ───────────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <form action="{{ route('investments.store') }}" method="POST">
                @csrf

                {{-- Hidden field untuk conditional validation --}}
                <input type="hidden" name="action" value="create">

                {{-- ── Allocation Type ──────────────────────────────────────── --}}
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        Jenis Alokasi <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3" x-data="{ selected: '{{ old('allocation_type', 'saving') }}' }">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="allocation_type" value="saving"
                                   x-model="selected"
                                   class="sr-only peer">
                            <div class="border-2 rounded-xl p-4 transition-all
                                peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/20
                                border-gray-200 dark:border-gray-700 hover:border-teal-300">
                                <div class="text-2xl mb-2">🏦</div>
                                <div class="font-semibold text-gray-900 dark:text-white text-sm">Tabungan</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Deposito, tabungan bank</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="allocation_type" value="investment"
                                   x-model="selected"
                                   class="sr-only peer">
                            <div class="border-2 rounded-xl p-4 transition-all
                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20
                                border-gray-200 dark:border-gray-700 hover:border-indigo-300">
                                <div class="text-2xl mb-2">📈</div>
                                <div class="font-semibold text-gray-900 dark:text-white text-sm">Investasi</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Saham, reksa dana, kripto</div>
                            </div>
                        </label>
                    </div>
                    @error('allocation_type')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Instrument Name ──────────────────────────────────────── --}}
                <div class="mb-5">
                    <label for="instrument" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Nama Instrumen
                    </label>
                    <input type="text"
                           id="instrument"
                           name="instrument"
                           value="{{ old('instrument') }}"
                           placeholder="e.g. BRI Deposito, BBCA Saham, Reksa Dana Schroder"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-shadow">
                    @error('instrument')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Initial Amount ───────────────────────────────────────── --}}
                <div class="mb-5">
                    <label for="initial_amount" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Jumlah Modal Awal <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 inset-y-0 flex items-center text-gray-500 dark:text-gray-400 font-medium text-sm">Rp</span>
                        <input type="number"
                               id="initial_amount"
                               name="initial_amount"
                               value="{{ old('initial_amount') }}"
                               min="10000"
                               step="1000"
                               placeholder="0"
                               class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-shadow">
                    </div>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                        Minimal Rp 10.000. Nilai ini tidak bisa diubah setelah disimpan.
                    </p>
                    @error('initial_amount')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Note ─────────────────────────────────────────────────── --}}
                <div class="mb-6">
                    <label for="note" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <textarea id="note"
                              name="note"
                              rows="3"
                              placeholder="Tujuan investasi, target return, atau info tambahan..."
                              class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm resize-none transition-shadow">{{ old('note') }}</textarea>
                    @error('note')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Info Box ──────────────────────────────────────────────── --}}
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/50 rounded-xl">
                    <p class="text-xs text-blue-800 dark:text-blue-300">
                        <strong>ℹ️ Catatan:</strong> Dana yang Anda alokasikan akan masuk ke <strong>Investment Pool</strong>
                        dan terpisah dari Liquid Balance. Untuk mencairkan kembali, gunakan fitur Request Pencairan
                        dan upload bukti transaksi.
                    </p>
                </div>

                {{-- ── Submit Buttons ───────────────────────────────────────── --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="flex-1 sm:flex-none px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        Simpan Instrumen
                    </button>
                    <a href="{{ route('investments.index') }}"
                       class="px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
