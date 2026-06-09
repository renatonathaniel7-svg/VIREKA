{{-- =========================================================
     Widget: Budget Burndown Harian
     Data: $dailyBudgetStatus (array dari DashboardService::getDailyBudgetStatus)
     
     Menampilkan progress bar per kategori dengan warna adaptif.
     Warna: <50% hijau | 50-75% kuning | 75-100% oranye | >100% merah
     Survive level mempengaruhi tampilan kategori 'want'.
     ========================================================= --}}

@php
    $categories = $dailyBudgetStatus;

    $surviveLevel = $categories->first()['survive_level'] ?? 'normal';

    $today = now()->translatedFormat('l, d F Y');

    $hasAnyBudget  = collect($categories)->sum('daily_limit') > 0;
    $hasAnySpend   = collect($categories)->sum('today_spent') > 0;
@endphp

<div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Anggaran Hari Ini</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $today }}</p>
        </div>
        <a href="{{ route('budgets.index') ?? '#' }}"
           class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
            Kelola Budget
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Empty state: belum ada budget --}}
    @if(!$hasAnyBudget)
        <div class="text-center py-8">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Belum ada budget yang dikonfigurasi.</p>
            <a href="{{ route('budgets.index') ?? '#' }}"
               class="inline-block mt-3 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                + Atur Budget Sekarang
            </a>
        </div>
    @else
        {{-- Survive level notice --}}
        @if($surviveLevel !== 'normal')
            @php
                $surviveNotice = match($surviveLevel) {
                    'caution'  => ['msg' => 'Mode Caution aktif — budget Want dikurangi 20%.', 'class' => 'bg-yellow-50 dark:bg-yellow-950 border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300'],
                    'survive'  => ['msg' => 'Mode Survive aktif — kategori Want di-freeze.', 'class' => 'bg-orange-50 dark:bg-orange-950 border-orange-200 dark:border-orange-800 text-orange-700 dark:text-orange-300'],
                    'critical' => ['msg' => 'Mode Critical — hanya pengeluaran esensial yang diizinkan.', 'class' => 'bg-red-50 dark:bg-red-950 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300'],
                    default    => null,
                };
            @endphp
            @if($surviveNotice)
                <div class="mb-4 px-3 py-2 rounded-lg border text-xs font-medium {{ $surviveNotice['class'] }}">
                    ⚡ {{ $surviveNotice['msg'] }}
                </div>
            @endif
        @endif

        {{-- Budget items per kategori --}}
        <div class="space-y-4">
            @foreach($categories as $cat)
                @php
                    $isFrozen = in_array($surviveLevel, ['survive', 'critical']) && $cat['category_type'] === 'want';
                    $isBlocked = $surviveLevel === 'critical' && $cat['category_type'] === 'want';

                    // Cap progress bar di 100% untuk tampilan, tapi tetap tampilkan label overflow
                    $barWidth = min($cat['percentage'], 100);
                    $isOverBudget = $cat['percentage'] > 100;
                @endphp
                <div class="{{ $isFrozen ? 'opacity-60' : '' }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            {{-- Badge kategori --}}
                            @php
                                $typeBadge = match($cat['category_type']) {
                                    'want'       => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
                                    'need'       => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                                    'saving'     => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                    'investment' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300',
                                    default      => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $typeBadge }}">
                                {{ ucfirst($cat['category_type']) }}
                            </span>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ $cat['category_name'] }}
                            </span>
                            @if($isFrozen)
                                <span class="text-xs text-orange-500 font-semibold">🔒 Frozen</span>
                            @endif
                            @if($isBlocked)
                                <span class="text-xs text-red-500 font-semibold">🚫 Blocked</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ 'Rp ' . number_format($cat['today_spent'], 0, ',', '.') }} / {{ 'Rp ' . number_format($cat['daily_limit'], 0, ',', '.') }}
                            </span>
                            {{-- Warning icon jika >= 75% --}}
                            @if($cat['percentage'] >= 75 && $cat['percentage'] < 100)
                                <svg class="w-3.5 h-3.5 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            @elseif($isOverBudget)
                                <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                            <span class="text-xs font-bold w-10 text-right
                                {{ $isOverBudget ? 'text-red-500' : ($cat['percentage'] >= 75 ? 'text-orange-500' : 'text-gray-600 dark:text-gray-300') }}">
                                {{ $cat['percentage'] }}%
                            </span>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        @if($cat['daily_limit'] > 0)
                            <div class="h-2 rounded-full transition-all duration-500 {{ $cat['progress_color'] }}"
                                 style="width: {{ $barWidth }}%">
                            </div>
                        @else
                            <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-600 w-full"></div>
                        @endif
                    </div>

                    {{-- Over budget label --}}
                    @if($isOverBudget)
                        <p class="text-xs text-red-500 mt-1 font-medium">
                            Melebihi budget {{ 'Rp ' . number_format(abs($cat['today_spent']), 0, ',', '.') }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>
