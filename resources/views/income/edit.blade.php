@extends('layouts.app')

@section('title', 'Edit Pendapatan')
@section('page-title', 'Edit Pendapatan')

@section('content')

<div class="max-w-xl mx-auto">

    <div class="flex items-center gap-2 text-sm text-slate-500 mb-6">
        <a href="{{ route('income.index') }}" class="hover:text-emerald-600 transition-colors">Pendapatan</a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <a href="{{ route('income.show', $income->id) }}" class="hover:text-emerald-600 transition-colors">Detail</a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span class="text-slate-800 font-medium">Edit</span>
    </div>

    @if($income->verified_status === 'verified')
    <div class="flex items-start gap-3 bg-amber-50 border border-amber-300 rounded-xl px-4 py-3 mb-5">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm font-semibold text-amber-800">Pendapatan Terverifikasi</p>
            <p class="text-xs text-amber-700 mt-0.5">
                Mengedit pendapatan ini akan mereset statusnya ke <strong>Draft</strong>.
                Upload ulang bukti untuk verifikasi kembali.
            </p>
        </div>
    </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-slate-900">Edit Pendapatan</h2>
            @include('partials.status-badge', ['status' => $income->verified_status])
        </div>

        <form method="POST" action="{{ route('income.update', $income->id) }}" x-data>
            @csrf
            @method('PUT')

            {{-- Source --}}
            <div class="mb-4">
                <label for="source_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Sumber Pendapatan <span class="text-red-500">*</span>
                </label>
                <select id="source_id" name="source_id"
                        class="w-full text-sm border rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 transition-colors bg-white
                               {{ $errors->has('source_id') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}">
                    <option value="">— Pilih Sumber —</option>
                    @foreach($sources as $src)
                        <option value="{{ $src->id }}" {{ old('source_id', $income->source_id) == $src->id ? 'selected' : '' }}>
                            {{ $src->name }}
                        </option>
                    @endforeach
                </select>
                @error('source_id')
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
                           value="{{ old('amount', $income->amount) }}"
                           min="100" max="999999999"
                           class="w-full pl-10 pr-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-colors
                                  {{ $errors->has('amount') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}" />
                </div>
                @error('amount')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Date --}}
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Tanggal Diterima <span class="text-red-500">*</span>
                </label>
                <input type="date" id="date" name="date"
                       value="{{ old('date', \Carbon\Carbon::parse($income->date)->format('Y-m-d')) }}"
                       max="{{ now()->format('Y-m-d') }}"
                       class="w-full px-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 transition-colors
                              {{ $errors->has('date') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}" />
                @error('date')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Note --}}
            <div class="mb-6">
                <label for="note" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Catatan <span class="text-slate-400 font-normal text-xs">(opsional)</span>
                </label>
                <textarea id="note" name="note"
                          rows="3"
                          maxlength="500"
                          class="w-full px-3.5 py-2.5 text-sm border rounded-lg resize-none focus:outline-none focus:ring-2 transition-colors
                                 {{ $errors->has('note') ? 'border-red-400 bg-red-50 focus:ring-red-300' : 'border-slate-300 focus:border-emerald-500 focus:ring-emerald-100' }}">{{ old('note', $income->note) }}</textarea>
                @error('note')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <p class="text-xs text-slate-400 mb-6">
                <i data-lucide="lock" class="w-3 h-3 inline mr-1"></i>
                Status verifikasi dikelola melalui modul Verifikasi AI.
            </p>

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
                <a href="{{ route('income.show', $income->id) }}"
                   class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-lg text-sm transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
