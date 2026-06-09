@if($topGoals->count())

<div class="fintrack-card p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">

    <div class="flex items-center justify-between mb-4">

        <h3 class="text-sm font-bold text-gray-900 dark:text-white">
            🎯 Goals & Wishlist
        </h3>

        <a href="{{ route('goals.index') }}"
           class="text-xs text-indigo-600 hover:text-indigo-700">
            Lihat Semua →
        </a>

    </div>

    <div class="space-y-4">

        @foreach($topGoals as $goal)

            <div>

                <div class="flex justify-between mb-1">

                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ $goal->name }}
                    </span>

                    <span class="text-xs font-semibold text-indigo-600">
                        {{ $goal->progress }}%
                    </span>

                </div>

                <div class="w-full bg-gray-200 rounded-full h-2">

                    <div
                        class="bg-indigo-600 h-2 rounded-full"
                        style="width: {{ $goal->progress }}%">
                    </div>

                </div>

                <div class="flex justify-between mt-1 text-xs text-gray-500">

                    <span>
                        Rp {{ number_format($goal->collected_amount,0,',','.') }}
                    </span>

                    <span>
                        Rp {{ number_format($goal->target_amount,0,',','.') }}
                    </span>

                </div>

            </div>

        @endforeach

    </div>

</div>

@endif