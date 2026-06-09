@extends('layouts.app')

@section('title', 'Tambah Pengeluaran')
@section('page-title', 'Tambah Pengeluaran')

@section('content')

<div class="max-w-xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-500 mt-4 mb-6">
        <a href="{{ route('expenses.index') }}" class="hover:text-emerald-600 transition-colors">Pengeluaran</a>
    <svg xmlns="http://www.w3.org/2000/svg"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="2"
         stroke-linecap="round"
         stroke-linejoin="round"
         class="w-4 h-4">
        <path d="m9 18 6-6-6-6"/>
    </svg>
</button>
        <span class="text-slate-800 font-medium">Tambah Baru</span>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <h2 class="text-lg font-bold text-slate-900 mb-5">Tambah Pengeluaran</h2>

        <form method="POST" action="{{ route('expenses.store') }}" x-data>
            @csrf

            {{-- Category --}}
            <div class="mb-4">
                <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select id="category_id" name="category_id"
                        class="w-full text-sm border rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 transition-colors
                               {{ $errors->has('category_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}
                               bg-white">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Amount --}}
            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nominal (Rp) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-500 font-medium">Rp</span>
                    <input type="number" id="amount" name="amount"
                           value="{{ old('amount') }}"
                           min="100" max="999999999"
                           placeholder="contoh: 50000"
                           class="w-full pl-10 pr-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-colors
                                  {{ $errors->has('amount') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}" />
                </div>
                @error('amount')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Deskripsi <span class="text-red-500">*</span>
                </label>
                <input type="text" id="description" name="description"
                       value="{{ old('description') }}"
                       maxlength="255"
                       placeholder="contoh: Makan siang di warteg"
                       class="w-full px-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-colors
                              {{ $errors->has('description') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}" />
                @error('description')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Date --}}
            <div class="mb-6">
                <label for="date" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" id="date" name="date"
                       value="{{ old('date', now()->format('Y-m-d')) }}"
                       max="{{ now()->format('Y-m-d') }}"
                       class="w-full px-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-colors
                              {{ $errors->has('date') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}" />
                @error('date')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info box --}}
            <div class="flex items-start gap-2.5 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-6">
               <svg xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-4 h-4 text-blue-500">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 16v-4"/>
                <path d="M12 8h.01"/>
</svg>
                <p class="text-xs text-blue-700">
                    Transaksi baru disimpan dengan status <strong>Draft</strong>.
                    Upload bukti transaksi untuk memulai proses verifikasi.
                </p>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
                    Simpan Pengeluaran
                </button>
                <a href="{{ route('expenses.index') }}"
                   class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-lg text-sm transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
