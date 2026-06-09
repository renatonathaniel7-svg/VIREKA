@extends('layouts.app')

@section('title', 'Pendapatan')
@section('page-title', 'Pendapatan')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mx-4 mb-6 pt-2">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Pendapatan</h2>
        <p class="text-sm text-slate-500 mt-0.5">Semua entri pendapatannmu</p>
    </div>
    <a href="{{ route('income.create') }}"
       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors shadow-sm">
        ➕
        Tambah Pendapatan
    </a>
</div>

{{-- ── Verified Income Summary Bar ─────────────────────────────────────────── --}}
<div class="bg-gradient-to-r from-emerald-600 to-emerald-500 rounded-xl px-5 py-4 mb-5 flex items-center justify-between shadow-sm">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg"
     width="24"
     height="24"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="2">
    <path d="M22 7L13.5 15.5L8.5 10.5L2 17"/>
    <path d="M16 7H22V13"/>
</svg>
        </div>
        <div>
            <p class="text-xs font-medium text-emerald-100">Total Income Terverifikasi</p>
            <p class="text-xs text-emerald-200">
                {{ \Carbon\Carbon::createFromDate(explode('-', $currentMonth)[0], explode('-', $currentMonth)[1], 1)->translatedFormat('F Y') }}
            </p>
        </div>
    </div>
    <div class="text-right">
        <p class="text-2xl font-bold text-white">{{ rupiah($verifiedMonthlyTotal) }}</p>
        <p class="text-xs text-emerald-200">Hanya transaksi verified</p>
    </div>
</div>

{{-- ── Filter Bar ───────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('income.index') }}"
      class="bg-white border border-slate-200 rounded-xl px-4 py-3 mb-5 flex flex-wrap gap-3 items-end">

    {{-- Source --}}
    <div class="flex-1 min-w-[160px]">
        <label class="block text-xs font-medium text-slate-500 mb-1">Sumber Income</label>
        <select name="source_id"
                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 bg-white">
            <option value="">Semua Sumber</option>
            @foreach($sources as $src)
                <option value="{{ $src->id }}" {{ request('source_id') == $src->id ? 'selected' : '' }}>
                    {{ $src->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Status --}}
    <div class="flex-1 min-w-[160px]">
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status"
                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 bg-white">
            <option value="">Semua Status</option>
            <option value="verified"   {{ request('status') == 'verified'   ? 'selected' : '' }}>Verified</option>
            <option value="pending"    {{ request('status') == 'pending'    ? 'selected' : '' }}>Pending</option>
            <option value="draft"      {{ request('status') == 'draft'      ? 'selected' : '' }}>Draft</option>
            <option value="flagged"    {{ request('status') == 'flagged'    ? 'selected' : '' }}>Flagged</option>
            <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Unverified</option>
        </select>
    </div>

    {{-- Month --}}
    <div class="flex-1 min-w-[160px]">
        <label class="block text-xs font-medium text-slate-500 mb-1">Bulan</label>
        <input type="month" name="month" value="{{ $currentMonth }}"
               class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500" />
    </div>

    <div class="flex gap-2">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
            Filter
        </button>
        <a href="{{ route('income.index') }}"
           class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition-colors">
            Reset
        </a>
    </div>

</form>

{{-- ── Table ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">

    @if($incomes->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Sumber</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-36">Nominal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Catatan</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Status</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($incomes as $income)
                <tr class="hover:bg-slate-50 transition-colors">

                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($income->date)->format('d M Y') }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                               <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 text-xs">

                                @switch($income->source?->name)

                                    @case('Gaji')
                                        💼
                                        @break

                                    @case('Freelance')
                                        💻
                                        @break

                                    @case('Bonus')
                                        🎁
                                        @break

                                    @case('Investasi Cair')
                                        📈
                                        @break

                                    @case('Usaha Sampingan')
                                        🏪
                                        @break

                                    @default
                                        💰

                                @endswitch

</div>
                            </div>
                            <span class="font-medium text-slate-800">{{ $income->source?->name ?? 'Tanpa Sumber' }}</span>
                        </div>
                    </td>

                    <td class="px-4 py-3 text-right font-semibold text-emerald-700 whitespace-nowrap">
                        +{{ rupiah($income->amount) }}
                    </td>

                    <td class="px-4 py-3 text-slate-500 max-w-xs truncate">
                        {{ $income->note ?? '—' }}
                    </td>

                    <td class="px-4 py-3 text-center">
                        @include('partials.status-badge', ['status' => $income->verified_status])
                    </td>

<td class="px-4 py-3">
    <div class="flex items-center justify-center gap-2">

        <a href="{{ route('income.show', $income->id) }}"
           class="text-blue-600 hover:underline">
            👁
        </a>

        <a href="{{ route('income.edit', $income->id) }}"
           class="text-amber-600 hover:underline">
            ✏
        </a>

        @if($income->verified_status !== 'verified')
        <form method="POST"
              action="{{ route('income.destroy', $income->id) }}"
              class="inline">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="text-red-600 hover:underline">
                🗑
            </button>
        </form>
        @endif

    </div>
</td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($incomes->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $incomes->links() }}
    </div>
    @endif

    @else
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center mb-4">
            <i data-lucide="arrow-up-circle" class="w-6 h-6 text-emerald-400"></i>
        </div>
        <p class="text-slate-600 font-medium">Belum ada data pendapatan</p>
        <p class="text-slate-400 text-sm mt-1">Catat pendapatanmu untuk melacak total pendapatan</p>
        <a href="{{ route('income.create') }}"
           class="mt-4 inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            ➕
            Tambah Sekarang
        </a>
    </div>
    @endif

</div>

@endsection
