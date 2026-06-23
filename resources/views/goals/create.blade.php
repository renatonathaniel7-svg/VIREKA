@extends('layouts.app')

@section('title', 'Tambah Goal')

@section('content')

<div class="max-w-xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mt-4 mb-6">
        <a href="{{ route('goals.index') }}"
           class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            Goals
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
            <path d="m9 18 6-6-6-6"/>
        </svg>
        <span class="text-slate-800 dark:text-slate-200 font-medium">Tambah Baru</span>
    </div>

    {{-- Card --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6">

        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5">
            🎯 Buat Goal Baru
        </h2>

        <form action="{{ route('goals.store') }}" method="POST">
            @csrf

            {{-- Nama Goal --}}
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Nama Goal <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Contoh: Axioo Hype 7"
                       required
                       class="w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3.5 py-2.5 text-sm
                              bg-white dark:bg-slate-700 text-slate-900 dark:text-white
                              placeholder:text-slate-400 dark:placeholder:text-slate-500
                              focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-100 dark:focus:ring-indigo-900 transition-colors
                              {{ $errors->has('name') ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : '' }}">
                @error('name')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Target Dana --}}
            <div class="mb-4">
                <label for="target_amount" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Target Dana (Rp) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-500 dark:text-slate-400 font-medium">Rp</span>
                    <input type="text"
                           id="target_amount"
                           name="target_amount"
                           value="{{ old('target_amount') }}"
                           placeholder="contoh: 5000000"
                           required
                           class="w-full pl-10 pr-3.5 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                  bg-white dark:bg-slate-700 text-slate-900 dark:text-white
                                  placeholder:text-slate-400 dark:placeholder:text-slate-500
                                  focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-100 dark:focus:ring-indigo-900 transition-colors
                                  {{ $errors->has('target_amount') ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : '' }}">
                </div>
                @error('target_amount')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Target Tanggal --}}
            <div class="mb-4">
                <label for="target_date" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Target Tanggal <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">(opsional)</span>
                </label>
                <input type="date"
                       id="target_date"
                       name="target_date"
                       value="{{ old('target_date') }}"
                       class="w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3.5 py-2.5 text-sm
                              bg-white dark:bg-slate-700 text-slate-900 dark:text-white
                              focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-100 dark:focus:ring-indigo-900 transition-colors">
                @error('target_date')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                    Deskripsi <span class="text-slate-400 dark:text-slate-500 font-normal text-xs">(opsional)</span>
                </label>
                <textarea id="description"
                          name="description"
                          rows="4"
                          placeholder="Contoh: Laptop untuk kuliah, coding, dan pengerjaan TA"
                          class="w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3.5 py-2.5 text-sm resize-none
                                 bg-white dark:bg-slate-700 text-slate-900 dark:text-white
                                 placeholder:text-slate-400 dark:placeholder:text-slate-500
                                 focus:outline-none focus:ring-2 focus:border-indigo-500 focus:ring-indigo-100 dark:focus:ring-indigo-900 transition-colors">{{ old('description') }}</textarea>
                @error('description')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info box --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3 mb-6">
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    🎯 Goal membantu kamu menyisihkan dana secara bertahap untuk kebutuhan besar
                    seperti laptop, smartphone, kendaraan, dana darurat, atau liburan impian.
                </p>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 justify-end">
                <a href="{{ route('goals.index') }}"
                   class="px-4 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300
                          bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600
                          rounded-lg text-sm font-medium transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    Simpan Goal
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
