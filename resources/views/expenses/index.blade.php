@extends('layouts.app')

@section('title', 'Pengeluaran')
@section('page-title', 'Pengeluaran')

@section('content')

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Header ──────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Pengeluaran
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Semua Transaksi Pengeluaranmu
                </p>
            </div>
            <a href="{{ route('expenses.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pengeluaran
            </a>
        </div>

        {{-- ── Filter Bar ────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('expenses.index') }}"
              class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 mb-5 flex flex-wrap gap-3 items-end">

            {{-- Category --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Kategori</label>
                <select name="category_id"
                        class="w-full text-sm border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2
                               bg-white dark:bg-slate-700 text-slate-900 dark:text-white
                               focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500">
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
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
                <select name="status"
                        class="w-full text-sm border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2
                               bg-white dark:bg-slate-700 text-slate-900 dark:text-white
                               focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500">
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
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Bulan</label>
                <input type="month" name="month" value="{{ $currentMonth }}"
                       class="w-full text-sm border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2
                              bg-white dark:bg-slate-700 text-slate-900 dark:text-white
                              focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500" />
            </div>

            {{-- Actions --}}
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Filter
                </button>
                <a href="{{ route('expenses.index') }}"
                   class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg transition-colors">
                    Reset
                </a>
            </div>
        </form>

        {{-- ── Table ─────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">

            @if($expenses->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-28">Tanggal</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Deskripsi</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-28">Kategori</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-36">Nominal</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-28">Status</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($expenses as $expense)
                        <tr class="transition-colors hover:bg-slate-100 dark:hover:bg-slate-700/40">

                            {{-- Date --}}
                            <td class="px-4 py-3 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}
                            </td>

                            {{-- Description --}}
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 font-medium max-w-xs truncate">
                                {{ $expense->description }}
                            </td>

                            {{-- Category --}}
                            <td class="px-4 py-3">
                                @php
                                    $catColors = [
                                        'need'       => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                        'want'       => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                        'saving'     => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                        'investment' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                    ];
                                    $catSlug  = strtolower($expense->category->slug ?? $expense->category->name);
                                    $catClass = $catColors[$catSlug] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $catClass }}">
                                    {{ $expense->category->name }}
                                </span>
                            </td>

                            {{-- Amount --}}
                            <td class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                {{ rupiah($expense->amount) }}
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-3 text-center">
                                @include('partials.status-badge', ['status' => $expense->verified_status])
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-3">

                                    <a href="{{ route('expenses.show', $expense->id) }}"
                                       class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium"
                                       title="Detail">👁</a>

                                    <a href="{{ route('expenses.edit', $expense->id) }}"
                                       class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 font-medium"
                                       title="Edit">✏️</a>

                                    @if($expense->verified_status !== 'verified')
                                    <form method="POST"
                                          action="{{ route('expenses.destroy', $expense->id) }}"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium"
                                                title="Hapus"
                                                onclick="return confirm('Hapus pengeluaran ini? Tindakan tidak dapat dibatalkan.')">
                                            🗑️
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-slate-300 dark:text-slate-600"
                                          title="Tidak bisa hapus — sudah terverifikasi">🔒</span>
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
            <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">
                {{ $expenses->links() }}
            </div>
            @endif

            @else
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="wallet" class="w-6 h-6 text-slate-400 dark:text-slate-500"></i>
                </div>
                <p class="text-slate-600 dark:text-slate-300 font-medium">Belum ada pengeluaran</p>
                <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">Tambahkan pengeluaran pertamamu untuk mulai tracking</p>
                <a href="{{ route('expenses.create') }}"
                   class="mt-4 inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Sekarang
                </a>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
