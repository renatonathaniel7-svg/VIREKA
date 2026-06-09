@extends('layouts.app')

@section('title', 'Budget')
@section('page-title', 'Budget Harian')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mx-4 mb-6 pt-2">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Anggaran Harian</h2>
        <p class="text-sm text-slate-500 mt-0.5">
            {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
        </p>
    </div>
    <div class="flex items-center gap-2 bg-slate-100 text-slate-600 text-xs font-medium px-3 py-1.5 rounded-lg">
        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
        Hari ini: {{ now()->translatedFormat('d F Y') }}
    </div>
</div>

{{-- ── Budget Table ─────────────────────────────────────────────────────────── --}}
@if($budgets->count() > 0)
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-6">
    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Monitor Pengeluaran Hari Ini</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Kategori</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-36">Limit Harian</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-36">Keluar Hari Ini</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-32">Sisa</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-48">Progress</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($budgets as $budget)
                <tr class="hover:bg-slate-50 transition-colors" x-data="{ editing: false }">

                    {{-- Category --}}
                    <td class="px-5 py-4">
                        @php
                            $catColors = [
                                'need'       => 'bg-blue-100 text-blue-700',
                                'want'       => 'bg-purple-100 text-purple-700',
                                'saving'     => 'bg-emerald-100 text-emerald-700',
                                'investment' => 'bg-amber-100 text-amber-700',
                            ];
                            $catSlug  = strtolower($budget->category->slug ?? $budget->category->name);
                            $catClass = $catColors[$catSlug] ?? 'bg-slate-100 text-slate-600';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $catClass }}">
                            {{ $budget->category->name }}
                        </span>
                    </td>

                    {{-- Daily Limit (editable inline) --}}
                    <td class="px-5 py-4 text-right">
                        <span x-show="!editing" class="font-semibold text-slate-700">
                            {{ rupiah($budget->daily_limit) }}
                        </span>
                        <div x-show="editing" x-cloak>
                            <form method="POST" action="{{ route('budgets.update', $budget->id) }}" class="flex items-center gap-1 justify-end">
                                @csrf
                                @method('PUT')
                                {{-- Pass category_id to satisfy BudgetRequest --}}
                                <input type="hidden" name="category_id" value="{{ $budget->category_id }}" />
                                <input type="number"
                                       name="daily_limit"
                                       value="{{ $budget->daily_limit }}"
                                       min="1000"
                                       class="w-28 text-right text-sm border border-emerald-400 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-emerald-300" />
                                <button type="submit"
                                        class="p-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition-colors">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="editing = false"
                                        class="p-1 bg-slate-200 text-slate-600 rounded hover:bg-slate-300 transition-colors">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </td>

                    {{-- Today spent --}}
                    <td class="px-5 py-4 text-right">
                        <span class="{{ $budget->percentage > 100 ? 'text-red-600 font-semibold' : 'text-slate-700' }}">
                            {{ rupiah($budget->today_spent) }}
                        </span>
                    </td>

                    {{-- Remaining --}}
                    <td class="px-5 py-4 text-right">
                        @if($budget->percentage > 100)
                            <span class="text-red-600 font-semibold text-xs">Melebihi limit!</span>
                        @else
                            <span class="text-emerald-600 font-medium">{{ rupiah($budget->remaining) }}</span>
                        @endif
                    </td>

                    {{-- Progress Bar --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-500 {{ $budget->status_class }}"
                                     style="width: {{ min($budget->percentage, 100) }}%"></div>
                            </div>
                            <span class="text-xs font-medium w-10 text-right
                                         {{ $budget->percentage > 100 ? 'text-red-600' : ($budget->percentage >= 75 ? 'text-orange-500' : 'text-slate-500') }}">
                                {{ $budget->percentage }}%
                            </span>
                        </div>

                        {{-- Status label --}}
                        @if($budget->percentage > 100)
                            <p class="text-xs text-red-600 mt-0.5 font-medium">🚨 Melebihi anggaran</p>
                        @elseif($budget->percentage >= 75)
                            <p class="text-xs text-orange-500 mt-0.5">⚠️ Hampir habis</p>
                        @elseif($budget->percentage >= 50)
                            <p class="text-xs text-yellow-600 mt-0.5">Cukup baik</p>
                        @else
                            <p class="text-xs text-emerald-600 mt-0.5">✓ Hemat</p>
                        @endif
                    </td>

                    {{-- Edit button --}}
                    <td class="px-5 py-4 text-center">
                        <button @click="editing = !editing"
                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors"
                                title="Edit limit">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-white border border-slate-200 rounded-xl px-6 py-10 text-center mb-6">
    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i data-lucide="target" class="w-6 h-6 text-slate-400"></i>
    </div>
    <p class="text-slate-600 font-medium">Belum ada budget bulan ini</p>
    <p class="text-slate-400 text-sm mt-1">Tambahkan budget per kategori di bawah untuk mulai tracking</p>
</div>
@endif

{{-- ── Add Budget Form ──────────────────────────────────────────────────────── --}}
@if($remainingCategories->count() > 0)
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Tambah Budget Kategori Baru</p>
    </div>
    <div class="px-5 py-4">
        <form method="POST" action="{{ route('budgets.store') }}"
              class="flex flex-wrap gap-3 items-end">
            @csrf

            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Kategori</label>
                <select name="category_id"
                        class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500 bg-white
                               {{ $errors->has('category_id') ? 'border-red-400' : '' }}">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($remainingCategories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Limit Harian (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500">Rp</span>
                    <input type="number" name="daily_limit"
                           value="{{ old('daily_limit') }}"
                           min="1000"
                           placeholder="contoh: 50000"
                           class="w-full pl-8 pr-3 py-2.5 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:border-emerald-500
                                  {{ $errors->has('daily_limit') ? 'border-red-400' : '' }}" />
                </div>
                @error('daily_limit')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah Budget
                </button>
            </div>
        </form>
    </div>
</div>
@else
<div class="bg-emerald-50 border border-emerald-200 rounded-xl px-5 py-3 flex items-center gap-3">
    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
    <p class="text-sm text-emerald-700">Semua kategori sudah memiliki budget bulan ini. 🎉</p>
</div>
@endif

@endsection
