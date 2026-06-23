@extends('layouts.app')

@section('title', 'Tambah Pendapatan')
@section('page-title', 'Tambah Pendapatan')

@section('content')

<div class="max-w-xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mt-4 mb-6">
        <a href="{{ route('income.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Pendapatan</a>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
            <path d="m9 18 6-6-6-6"/>
        </svg>
        <span class="text-slate-800 dark:text-slate-200 font-medium">Tambah Baru</span>
    </div>

    {{-- Card --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5">Tambah Pendapatan</h2>

        <form method="POST" action="{{ route('income.store') }}" x-data>
            @csrf

            {{-- Source --}}
            <div class="mb-4">
                <label for="source_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Sumber Pendapatan <span class="text-red-500">*</span>
                </label>
                <select id="source_id" name="source_id"
                        class="w-full text-sm border rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 transition-colors
                               text-slate-900 dark:text-white
                               placeholder:text-slate-400 dark:placeholder:text-slate-500
                               {{ $errors->has('source_id')
                                    ? 'border-red-400 bg-red-50 dark:bg-red-900/20 focus:ring-red-300'
                                    : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:border-emerald-500 focus:ring-emerald-100 dark:focus:ring-emerald-900' }}">
                    <option value="">— Pilih Sumber —</option>
                    @foreach($sources as $src)
                        <option value="{{ $src->id }}" {{ old('source_id') == $src->id ? 'selected' : '' }}>
                            {{ $src->name }}
                        </option>
                    @endforeach
                </select>
                @error('source_id')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Amount --}}
            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Nominal (Rp) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-500 dark:text-slate-400 font-medium">Rp</span>
                    <input type="number" id="amount" name="amount"
                           value="{{ old('amount') }}"
                           min="100" max="999999999"
                           placeholder="contoh: 3000000"
                           class="w-full pl-10 pr-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-colors
                                  text-slate-900 dark:text-white
                                  placeholder:text-slate-400 dark:placeholder:text-slate-500
                                  {{ $errors->has('amount')
                                       ? 'border-red-400 bg-red-50 dark:bg-red-900/20 focus:ring-red-300'
                                       : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:border-emerald-500 focus:ring-emerald-100 dark:focus:ring-emerald-900' }}" />
                </div>
                @error('amount')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Date --}}
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Tanggal Diterima <span class="text-red-500">*</span>
                </label>
                <input type="date" id="date" name="date"
                       value="{{ old('date', now()->format('Y-m-d')) }}"
                       max="{{ now()->format('Y-m-d') }}"
                       class="w-full px-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-colors
                              text-slate-900 dark:text-white
                              {{ $errors->has('date')
                                   ? 'border-red-400 bg-red-50 dark:bg-red-900/20 focus:ring-red-300'
                                   : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:border-emerald-500 focus:ring-emerald-100 dark:focus:ring-emerald-900' }}" />
                @error('date')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Note --}}
            <div class="mb-6">
                <label for="note" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Catatan <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">(opsional)</span>
                </label>
                <textarea id="note" name="note"
                          rows="3"
                          maxlength="500"
                          placeholder="contoh: Gaji bulan Januari, bonus proyek, dsb."
                          class="w-full px-3.5 py-2.5 text-sm border rounded-lg resize-none focus:outline-none focus:ring-2 transition-colors
                                 text-slate-900 dark:text-white
                                 placeholder:text-slate-400 dark:placeholder:text-slate-500
                                 {{ $errors->has('note')
                                      ? 'border-red-400 bg-red-50 dark:bg-red-900/20 focus:ring-red-300'
                                      : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:border-emerald-500 focus:ring-emerald-100 dark:focus:ring-emerald-900' }}">{{ old('note') }}</textarea>
                @error('note')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info box --}}
            <div class="flex items-start gap-2.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-4 py-3 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-blue-500 dark:text-blue-400 flex-shrink-0 mt-0.5">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 16v-4"/>
                    <path d="M12 8h.01"/>
                </svg>
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    Pendapatan baru disimpan dengan status <strong>Draft</strong>.
                    Upload bukti (slip gaji, mutasi rekening) untuk verifikasi.
                </p>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
                    Simpan Pendapatan
                </button>
                <a href="{{ route('income.index') }}"
                   class="flex-1 text-center bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-semibold py-2.5 rounded-lg text-sm transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
