@extends('layouts.app')

@section('title', 'Detail Pendapatan')
@section('page-title', 'Detail Pendapatan')

@section('content')

<div class="max-w-xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-6">
        <a href="{{ route('income.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Pendapatan</a>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
            <path d="m9 18 6-6-6-6"/>
        </svg>
        <span class="text-slate-800 dark:text-slate-200 font-medium">Detail</span>
    </div>

    {{-- Main Card --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">

        {{-- Card Header --}}
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between
                    bg-gradient-to-r from-emerald-50 to-white dark:from-emerald-900/20 dark:to-slate-800">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Pendapatan</p>
                <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">+{{ rupiah($income->amount) }}</p>
            </div>
            @include('partials.status-badge', ['status' => $income->verified_status])
        </div>

        {{-- Detail List --}}
        <dl class="divide-y divide-slate-100 dark:divide-slate-700">

            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500 dark:text-slate-400">Sumber</dt>
                <dd class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    {{ optional($income->source)->name ?? 'Tanpa Sumber' }}
                </dd>
            </div>

            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500 dark:text-slate-400">Tanggal Diterima</dt>
                <dd class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    {{ \Carbon\Carbon::parse($income->date)->translatedFormat('l, d F Y') }}
                </dd>
            </div>

            @if($income->note)
            <div class="px-6 py-4">
                <dt class="text-sm text-slate-500 dark:text-slate-400 mb-1.5">Catatan</dt>
                <dd class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 rounded-lg px-3 py-2">
                    {{ $income->note }}
                </dd>
            </div>
            @endif

            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500 dark:text-slate-400">Status Verifikasi</dt>
                <dd>@include('partials.status-badge', ['status' => $income->verified_status])</dd>
            </div>

            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500 dark:text-slate-400">Dicatat pada</dt>
                <dd class="text-sm text-slate-500 dark:text-slate-400">{{ $income->created_at->format('d M Y, H:i') }}</dd>
            </div>

        </dl>

        {{-- Status info panel --}}
        @if($income->verified_status === 'draft')
        <div class="mx-6 my-4 flex items-start gap-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="w-5 h-5 text-slate-500 dark:text-slate-400 flex-shrink-0">
                <path d="M16 16l-4-4-4 4"/>
                <path d="M12 12v9"/>
                <path d="M20.39 18.39A5.5 5.5 0 0 0 18 8h-1.26A8 8 0 1 0 4 16.3"/>
            </svg>
            <p class="text-xs text-slate-600 dark:text-slate-300">
                Upload bukti pendapatan (slip gaji, mutasi rekening) untuk verifikasi dan memasukkan ke saldo terverifikasi.
            </p>
        </div>
        @elseif($income->verified_status === 'verified')
        <div class="mx-6 my-4 flex items-start gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg px-4 py-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0">
                <path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
            <p class="text-xs text-emerald-700 dark:text-emerald-300">
                Pendapatan ini sudah masuk ke saldo terverifikasi dan diperhitungkan dalam health score.
            </p>
        </div>
        @endif

        {{-- Actions --}}
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700 flex gap-3">

            <a href="{{ route('income.edit', $income->id) }}"
               class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                ✏ Edit
            </a>

            @if(in_array($income->verified_status, ['draft', 'unverified']))
                <a href="{{ route('verifications.create', ['type' => 'income', 'id' => $income->id]) }}"
                   class="flex-1 flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                    🔍 Verifikasi
                </a>
            @endif

            @if(in_array($income->verified_status, ['pending', 'verified', 'flagged']))
                @if($income->verification)
                    <a href="{{ route('verifications.show', $income->verification->id) }}"
                       class="flex-1 flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                        👁 Lihat Verifikasi
                    </a>
                @endif
            @endif

            @if($income->verified_status !== 'verified')
                <form method="POST"
                      action="{{ route('income.destroy', $income->id) }}"
                      class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 text-sm font-semibold py-2.5 rounded-lg transition-colors border border-red-200 dark:border-red-800"
                            x-on:click.prevent="if(confirm('Hapus pendapatan ini?')) $el.closest('form').submit()">
                        🗑 Hapus
                    </button>
                </form>
            @else
                <div class="flex-1 flex items-center justify-center gap-2 bg-slate-50 dark:bg-slate-700 text-slate-400 dark:text-slate-500 text-sm font-medium py-2.5 rounded-lg border border-slate-200 dark:border-slate-600 cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="w-5 h-5">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Tidak bisa dihapus
                </div>
            @endif

        </div>

    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('income.index') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
            ← Kembali ke daftar pendapatan
        </a>
    </div>
</div>

@endsection
