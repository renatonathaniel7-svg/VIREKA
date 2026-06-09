@extends('layouts.app')

@section('title', 'Pengeluaran')
@section('page-title', 'Pengeluaran')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mx-4 mb-6 pt-2">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Pengeluaran</h2>
        <p class="text-sm text-slate-500 mt-0.5">Semua transaksi pengeluaranmu</p>
    </div>
    <a href="{{ route('expenses.create') }}"
       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors shadow-sm">
        ➕
        Tambah Pengeluaran
    </a>
</div>

{{-- ── Filter Bar ───────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('expenses.index') }}"
      class="bg-white border border-slate-200 rounded-xl px-4 py-3 mb-5 flex flex-wrap gap-3 items-end">

    {{-- Category --}}
    <div class="flex-1 min-w-[160px]">
        <label class="block text-xs font-medium text-slate-500 mb-1">Kategori</label>
        <select name="category_id"
                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 bg-white">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
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

    {{-- Actions --}}
    <div class="flex gap-2">
        <button type="submit"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
            Filter
        </button>
        <a href="{{ route('expenses.index') }}"
           class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition-colors">
            Reset
        </a>
    </div>

</form>

{{-- ── Table ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">

    @if($expenses->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Deskripsi</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Kategori</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-36">Nominal</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-28">Status</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($expenses as $expense)
                <tr class="hover:bg-slate-50 transition-colors">

                    {{-- Date --}}
                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}
                    </td>

                    {{-- Description --}}
                    <td class="px-4 py-3 text-slate-800 font-medium max-w-xs truncate">
                        {{ $expense->description }}
                    </td>

                    {{-- Category --}}
                    <td class="px-4 py-3">
                        @php
                            $catColors = [
                                'need'       => 'bg-blue-100 text-blue-700',
                                'want'       => 'bg-purple-100 text-purple-700',
                                'saving'     => 'bg-emerald-100 text-emerald-700',
                                'investment' => 'bg-amber-100 text-amber-700',
                            ];
                            $catSlug  = strtolower($expense->category->slug ?? $expense->category->name);
                            $catClass = $catColors[$catSlug] ?? 'bg-slate-100 text-slate-600';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $catClass }}">
                            {{ $expense->category->name }}
                        </span>
                    </td>

                    {{-- Amount --}}
                    <td class="px-4 py-3 text-right font-semibold text-slate-800 whitespace-nowrap">
                        {{ rupiah($expense->amount) }}
                    </td>

                    {{-- Status Badge --}}
                    <td class="px-4 py-3 text-center">
                        @include('partials.status-badge', ['status' => $expense->verified_status])
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-3">

                    {{-- Detail --}}
                    <a href="{{ route('expenses.show', $expense->id) }}"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                     title="Detail">
                       👁
                        </a>

                      {{-- Edit --}}
                    <a href="{{ route('expenses.edit', $expense->id) }}"
                    class="text-amber-600 hover:text-amber-800 font-medium"
                    title="Edit">
                         ✏️
                       </a>

        {{-- Delete --}}
        @if($expense->verified_status !== 'verified')
        <form method="POST"
              action="{{ route('expenses.destroy', $expense->id) }}"
              class="inline">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="text-red-600 hover:text-red-800 font-medium"
                    title="Hapus"
                    onclick="return confirm('Hapus pengeluaran ini? Tindakan tidak dapat dibatalkan.')">
                🗑️
            </button>
        </form>
        @else
        <span class="text-slate-300"
              title="Tidak bisa hapus — sudah terverifikasi">
            🔒
        </span>
        @endif

    </div>
</td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($expenses->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $expenses->links() }}
    </div>
    @endif

    @else
    {{-- Empty state --}}
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mb-4">
            <i data-lucide="wallet" class="w-6 h-6 text-slate-400"></i>
        </div>
        <p class="text-slate-600 font-medium">Belum ada pengeluaran</p>
        <p class="text-slate-400 text-sm mt-1">Tambahkan pengeluaran pertamamu untuk mulai tracking</p>
        <a href="{{ route('expenses.create') }}"
           class="mt-4 inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            ➕
            Tambah Sekarang
        </a>
    </div>
    @endif

</div>

@endsection
