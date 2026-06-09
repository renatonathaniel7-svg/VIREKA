{{-- =========================================================
     Widget: Financial Health Score
     Data: $healthScore (array dari DashboardService::getFinancialHealthScore)
     
     Tampilan: score gauge visual + tier badge + breakdown komponen
     Catatan: bobot adalah keputusan desain, bukan konstanta ilmiah
     ========================================================= --}}

    @php
    $score = $healthScore['score'];

    $tier = $healthScore['tier']['code'];
    $tierLabel = $healthScore['tier']['label'];

    $tierColor = match ($tier) {
        'S' => 'indigo',
        'A' => 'blue',
        'B' => 'green',
        'C' => 'yellow',
        default => 'red',
    };

    $BAR = $healthScore['components']['bar'];
    $SR  = $healthScore['components']['sr'];
    $SC  = $healthScore['components']['sc'];

    $tierStyles = [
        'indigo' => [
            'badge'   => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300',
            'score'   => 'text-indigo-600 dark:text-indigo-400',
            'ring'    => 'stroke-indigo-500',
            'bar'     => 'bg-indigo-500',
        ],
        'green'  => [
            'badge'   => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
            'score'   => 'text-green-600 dark:text-green-400',
            'ring'    => 'stroke-green-500',
            'bar'     => 'bg-green-500',
        ],
        'blue'   => [
            'badge'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
            'score'   => 'text-blue-600 dark:text-blue-400',
            'ring'    => 'stroke-blue-500',
            'bar'     => 'bg-blue-500',
        ],
        'yellow' => [
            'badge'   => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
            'score'   => 'text-yellow-600 dark:text-yellow-400',
            'ring'    => 'stroke-yellow-500',
            'bar'     => 'bg-yellow-500',
        ],
        'red'    => [
            'badge'   => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
            'score'   => 'text-red-600 dark:text-red-400',
            'ring'    => 'stroke-red-500',
            'bar'     => 'bg-red-500',
        ],
    ];

    $style = $tierStyles[$tierColor] ?? $tierStyles['blue'];

    // SVG arc untuk gauge — circumference = 2π × 36 ≈ 226
    $circumference = 226;
    $dashOffset    = $circumference - ($score / 100) * $circumference;
@endphp

<div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 h-full">

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Health Score</h3>
        <span class="text-xs text-gray-400 dark:text-gray-500">
            {{ now()->translatedFormat('F Y') }}
        </span>
    </div>

    {{-- Score gauge (SVG circle) + Tier badge --}}
    <div class="flex flex-col items-center mb-5">
        <div class="relative w-28 h-28">
            <svg viewBox="0 0 80 80" class="w-full h-full -rotate-90">
                {{-- Track --}}
                <circle cx="40" cy="40" r="36"
                        fill="none" stroke="currentColor"
                        class="text-gray-100 dark:text-gray-700"
                        stroke-width="7" />
                {{-- Progress --}}
                <circle cx="40" cy="40" r="36"
                        fill="none"
                        class="{{ $style['ring'] }}"
                        stroke-width="7"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $dashOffset }}"
                        style="transition: stroke-dashoffset 1s ease;" />
            </svg>
            {{-- Center text --}}
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-2xl font-extrabold {{ $style['score'] }}">{{ $score }}</span>
                <span class="text-xs text-gray-400 dark:text-gray-500 -mt-0.5">/100</span>
            </div>
        </div>

        {{-- Tier badge --}}
        <div class="mt-2 text-center">
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold {{ $style['badge'] }}">
                Tier {{ $tier }} — {{ $tierLabel }}
            </span>
        </div>
    </div>

    {{-- Divider --}}
    <div class="border-t border-gray-100 dark:border-gray-700 mb-4"></div>

    {{-- Komponen breakdown --}}
    <div class="space-y-3">
        {{-- BAR --}}
        <div>
            <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-500 dark:text-gray-400 font-medium">
                    BAR
                    <span class="text-gray-400 dark:text-gray-600 font-normal">(Budget Adherence × 40%)</span>
                </span>
                <span class="font-bold text-gray-700 dark:text-gray-200">{{ $BAR }}%</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                <div class="h-1.5 rounded-full bg-blue-500 transition-all duration-700"
                     style="width: {{ min($BAR, 100) }}%"></div>
            </div>
        </div>

        {{-- SR --}}
        <div>
            <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-500 dark:text-gray-400 font-medium">
                    SR
                    <span class="text-gray-400 dark:text-gray-600 font-normal">(Saving Rate × 35%)</span>
                </span>
                <span class="font-bold text-gray-700 dark:text-gray-200">{{ $SR }}%</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                <div class="h-1.5 rounded-full bg-emerald-500 transition-all duration-700"
                     style="width: {{ min($SR, 100) }}%"></div>
            </div>
        </div>

        {{-- SC --}}
        <div>
            <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-500 dark:text-gray-400 font-medium">
                    SC
                    <span class="text-gray-400 dark:text-gray-600 font-normal">(Streak Cons. × 25%)</span>
                </span>
                <span class="font-bold text-gray-700 dark:text-gray-200">{{ $SC }}%</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                <div class="h-1.5 rounded-full bg-amber-500 transition-all duration-700"
                     style="width: {{ min($SC, 100) }}%"></div>
            </div>
        </div>
    </div>

    {{-- Streak info --}}
    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center gap-1.5">
            <span class="text-base">🔥</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">Streak saat ini</span>
        </div>
        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">
            {{ auth()->user()->current_streak ?? 0 }} hari
        </span>
    </div>

    {{-- Disclaimer --}}
    <p class="mt-3 text-xs text-gray-300 dark:text-gray-600 italic text-center">
        * Bobot merupakan keputusan desain, bukan konstanta empiris
    </p>

</div>
