{{--
    resources/views/notifications/index.blade.php
    Halaman Notifikasi — list appreciation_logs dengan read/unread state.

    DATA DARI CONTROLLER:
    - $notifications : LengthAwarePaginator<AppreciationLog>
    - $unreadCount   : int
--}}

@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 space-y-4">

    {{-- ══════════════════════════════════════════
        HEADER + MARK ALL READ
    ══════════════════════════════════════════ --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                🔔 Notifikasi
                @if($unreadCount > 0)
                <span class="text-sm font-bold bg-red-500 text-white px-2 py-0.5 rounded-full">
                    {{ $unreadCount }}
                </span>
                @endif
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Aktivitas streak, badge, dan ringkasan bulananmu
            </p>
        </div>

        @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button
                type="submit"
                class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors px-3 py-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20"
            >
                ✓ Tandai Semua Dibaca
            </button>
        </form>
        @endif
    </div>

    {{-- Flash message --}}
    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 text-sm rounded-xl px-4 py-3">
        ✅ {{ session('success') }}
    </div>
    @endif

    {{-- ══════════════════════════════════════════
        EMPTY STATE
    ══════════════════════════════════════════ --}}
    @if($notifications->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center">
        <div class="text-5xl mb-4">🔕</div>
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">Belum ada notifikasi</h3>
        <p class="text-sm text-gray-400 dark:text-gray-500">
            Mulai catat transaksi harianmu untuk mendapatkan apresiasi streak dan badge pertamamu!
        </p>
        <a href="{{ route('expenses.create') }}" class="inline-block mt-4 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
            + Catat transaksi →
        </a>
    </div>

    @else
    {{-- ══════════════════════════════════════════
        LIST NOTIFIKASI
    ══════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="divide-y divide-gray-50 dark:divide-gray-700">
            @foreach($notifications as $notif)
            @php
                $isUnread = is_null($notif->read_at);

                // Konfigurasi ikon dan warna per type
                $typeConfig = [
                    'daily_appreciation' => [
                        'icon'    => '🌟',
                        'label'   => 'Apresiasi Harian',
                        'color'   => 'text-emerald-600 dark:text-emerald-400',
                        'bg_icon' => 'bg-emerald-50 dark:bg-emerald-900/30',
                    ],
                    'daily_warning' => [
                        'icon'    => '⚠️',
                        'label'   => 'Peringatan Harian',
                        'color'   => 'text-yellow-600 dark:text-yellow-400',
                        'bg_icon' => 'bg-yellow-50 dark:bg-yellow-900/30',
                    ],
                    'streak_badge' => [
                        'icon'    => '🏆',
                        'label'   => 'Badge Baru!',
                        'color'   => 'text-indigo-600 dark:text-indigo-400',
                        'bg_icon' => 'bg-indigo-50 dark:bg-indigo-900/30',
                    ],
                    'monthly_summary' => [
                        'icon'    => '📊',
                        'label'   => 'Laporan Bulanan',
                        'color'   => 'text-blue-600 dark:text-blue-400',
                        'bg_icon' => 'bg-blue-50 dark:bg-blue-900/30',
                    ],
                    'income_growth' => [
                        'icon'    => '📈',
                        'label'   => 'Pertumbuhan Income',
                        'color'   => 'text-teal-600 dark:text-teal-400',
                        'bg_icon' => 'bg-teal-50 dark:bg-teal-900/30',
                    ],
                ];
                $tc = $typeConfig[$notif->type] ?? [
                    'icon'    => '🔔',
                    'label'   => 'Notifikasi',
                    'color'   => 'text-gray-500 dark:text-gray-400',
                    'bg_icon' => 'bg-gray-50 dark:bg-gray-700',
                ];
            @endphp

            <div class="
                px-5 py-4 flex items-start gap-4 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors
                {{ $isUnread ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}
            ">

                {{-- Icon type --}}
                <div class="flex-shrink-0 w-10 h-10 rounded-full {{ $tc['bg_icon'] }} flex items-center justify-center text-lg">
                    {{ $tc['icon'] }}
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-xs font-semibold {{ $tc['color'] }} uppercase tracking-wider">
                            {{ $tc['label'] }}
                        </span>
                        @if($isUnread)
                        <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        {{ $notif->message }}
                    </p>
                    <div class="flex items-center gap-3 mt-1.5">
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $notif->created_at->diffForHumans() }}
                        </span>
                        @if($notif->streak_count)
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            · 🔥 Streak: {{ $notif->streak_count }} hari
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Mark read button --}}
                @if($isUnread)
                <form method="POST" action="{{ route('notifications.read', $notif->id) }}" class="flex-shrink-0">
                    @csrf
                    <button
                        type="submit"
                        title="Tandai sudah dibaca"
                        class="text-xs text-gray-400 dark:text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors px-2 py-1 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20"
                    >
                        ✓ Baca
                    </button>
                </form>
                @else
                <div class="flex-shrink-0 w-8">
                    <span class="text-xs text-gray-300 dark:text-gray-600">✓</span>
                </div>
                @endif

            </div>
            @endforeach
        </div>
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="flex justify-center">
        {{ $notifications->links() }}
    </div>
    @endif

    @endif {{-- end empty state --}}

</div>
@endsection