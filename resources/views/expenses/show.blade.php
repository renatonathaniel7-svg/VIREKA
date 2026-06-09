@extends('layouts.app')

@section('title', 'Detail Pengeluaran')
@section('page-title', 'Detail Pengeluaran')

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
        <span class="text-slate-800 font-medium">Detail</span>
    </div>

    {{-- Main card --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">

        {{-- Card Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 mb-1">Pengeluaran</p>
                <p class="text-2xl font-bold text-slate-900">{{ rupiah($expense->amount) }}</p>
            </div>
            @include('partials.status-badge', ['status' => $expense->verified_status])
        </div>

        {{-- Details --}}
        <dl class="divide-y divide-slate-100">

            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500">Deskripsi</dt>
                <dd class="text-sm font-medium text-slate-800 text-right max-w-xs">{{ $expense->description }}</dd>
            </div>

            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500">Kategori</dt>
                <dd class="text-sm font-medium text-slate-800">{{ $expense->category->name }}</dd>
            </div>

            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500">Tanggal</dt>
                <dd class="text-sm font-medium text-slate-800">
                    {{ \Carbon\Carbon::parse($expense->date)->translatedFormat('l, d F Y') }}
                </dd>
            </div>

            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500">Status Verifikasi</dt>
                <dd>@include('partials.status-badge', ['status' => $expense->verified_status])</dd>
            </div>

            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500">Dicatat pada</dt>
                <dd class="text-sm text-slate-500">
                    {{ $expense->created_at->format('d M Y, H:i') }}
                </dd>
            </div>

            @if($expense->updated_at && $expense->updated_at->ne($expense->created_at))
            <div class="px-6 py-4 flex items-center justify-between">
                <dt class="text-sm text-slate-500">Terakhir diubah</dt>
                <dd class="text-sm text-slate-500">
                    {{ $expense->updated_at->format('d M Y, H:i') }}
                </dd>
            </div>
            @endif

        </dl>

        {{-- Verification info panel --}}
        @if($expense->verified_status === 'draft')
        <div class="mx-6 my-4 flex items-start gap-3 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3">
            <svg xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-5 h-5">
                <path d="M16 16l-4-4-4 4"/>
                <path d="M12 12v9"/>
                <path d="M20.39 18.39A5.5 5.5 0 0 0 18 8h-1.26A8 8 0 1 0 4 16.3"/>
                <path d="M16 16l-4-4-4 4"/>
            </svg>
            <div>
                <p class="text-xs font-semibold text-slate-700">Belum diverifikasi</p>
                <p class="text-xs text-slate-500 mt-0.5">
                    Upload bukti transaksi melalui modul Verifikasi AI agar transaksi ini masuk ke saldo terverifikasi.
                </p>
            </div>
        </div>
        @elseif($expense->verified_status === 'verified')
        <div class="mx-6 my-4 flex items-start gap-3 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3">
            <svg xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-5 h-5 text-green-500">
                <path d="M20 13c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V5l8-3 8 3z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
            <div>
                <p class="text-xs font-semibold text-emerald-700">Terverifikasi</p>
                <p class="text-xs text-emerald-600 mt-0.5">
                    Transaksi ini sudah masuk ke saldo terverifikasi dan mempengaruhi health score.
                </p>
            </div>
        </div>
        @elseif($expense->verified_status === 'flagged')
        <div class="mx-6 my-4 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
           <svg xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="w-5 h-5">
                <path d="M4 22V4"/>
                <path d="M4 4h11l-1.5 3L15 10H4"/>
            </svg>
            <div>
                <p class="text-xs font-semibold text-amber-700">Perlu Perhatian</p>
                <p class="text-xs text-amber-600 mt-0.5">
                    AI mendeteksi perbedaan data. Tinjau ulang dan upload bukti yang sesuai.
                </p>
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="px-6 py-4 border-t border-slate-100 flex gap-3">
            <a href="{{ route('expenses.edit', $expense->id) }}"
               class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                ✏ Edit
            </a>
            @if(in_array($expense->verified_status, ['draft', 'unverified']))
            <a href="{{ route('verifications.create', ['expense', $expense->id]) }}"
                 class="flex-1 flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                🔍 Verifikasi
             </a>
                @endif
            @if(in_array($expense->verified_status, ['pending', 'verified', 'flagged']))
                @php
                        $verification = $expense->verification;
                     @endphp
                @if($verification)
                    <a href="{{ route('verifications.show', $verification->id) }}"
                    class="flex-1 flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                        👁 Lihat Verifikasi
                         </a>
                     @endif
            @endif
            @if($expense->verified_status !== 'verified')
            <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold py-2.5 rounded-lg transition-colors border border-red-200"
                        x-on:click.prevent="if(confirm('Hapus pengeluaran ini? Tindakan tidak dapat dibatalkan.')) $el.closest('form').submit()">
                    🗑 Hapus
                </button>
            </form>
            @else
            <div class="flex-1 flex items-center justify-center gap-2 bg-slate-50 text-slate-400 text-sm font-medium py-2.5 rounded-lg border border-slate-200 cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
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
        <a href="{{ route('expenses.index') }}" class="text-sm text-slate-500 hover:text-emerald-600 transition-colors">
            ← Kembali ke daftar pengeluaran
        </a>
    </div>
</div>

<div class="mt-6 flex gap-3">


@endsection
