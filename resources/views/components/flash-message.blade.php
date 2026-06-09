{{-- resources/views/components/flash-message.blade.php --}}
{{--
    x-flash-message Blade Component
    Menampilkan flash message dari session.

    Supports:
      session('success') → hijau
      session('error')   → merah
      session('warning') → kuning
      session('info')    → biru

    Penggunaan: taruh <x-flash-message /> di layouts/app.blade.php
    setelah opening <body> atau sebelum @yield('content').

    Auto-dismiss setelah 5 detik via Alpine.js.
    User juga bisa dismiss manual dengan tombol ×.
--}}

@php
    $types = [
        'success' => [
            'session_key' => 'success',
            'bg'          => 'bg-green-50 dark:bg-green-900/20',
            'border'      => 'border-green-400 dark:border-green-600',
            'text'        => 'text-green-800 dark:text-green-300',
            'icon_bg'     => 'bg-green-100 dark:bg-green-900/40',
            'icon_color'  => 'text-green-500 dark:text-green-400',
            'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
        ],
        'error' => [
            'session_key' => 'error',
            'bg'          => 'bg-red-50 dark:bg-red-900/20',
            'border'      => 'border-red-400 dark:border-red-600',
            'text'        => 'text-red-800 dark:text-red-300',
            'icon_bg'     => 'bg-red-100 dark:bg-red-900/40',
            'icon_color'  => 'text-red-500 dark:text-red-400',
            'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
        ],
        'warning' => [
            'session_key' => 'warning',
            'bg'          => 'bg-yellow-50 dark:bg-yellow-900/20',
            'border'      => 'border-yellow-400 dark:border-yellow-600',
            'text'        => 'text-yellow-800 dark:text-yellow-300',
            'icon_bg'     => 'bg-yellow-100 dark:bg-yellow-900/40',
            'icon_color'  => 'text-yellow-500 dark:text-yellow-400',
            'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.924-.834-2.694 0L3.17 16.5c-.77.833.192 2.5 1.732 2.5z"/>',
        ],
        'info' => [
            'session_key' => 'info',
            'bg'          => 'bg-blue-50 dark:bg-blue-900/20',
            'border'      => 'border-blue-400 dark:border-blue-600',
            'text'        => 'text-blue-800 dark:text-blue-300',
            'icon_bg'     => 'bg-blue-100 dark:bg-blue-900/40',
            'icon_color'  => 'text-blue-500 dark:text-blue-400',
            'icon'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
    ];

    // Validation errors juga ditampilkan sebagai 'error'
    $hasValidationErrors = $errors->any();
@endphp

{{-- Validation Errors --}}
@if($hasValidationErrors)
<div x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="fixed top-4 right-4 z-50 max-w-sm w-full">
    <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-400 dark:border-red-600 rounded-xl shadow-lg">
        <div class="flex-shrink-0 w-8 h-8 bg-red-100 dark:bg-red-900/40 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-red-800 dark:text-red-300 mb-1">
                Ada {{ $errors->count() }} kesalahan input
            </p>
            <ul class="text-xs text-red-700 dark:text-red-400 space-y-0.5">
                @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button @click="show = false"
                class="flex-shrink-0 text-red-400 hover:text-red-600 dark:hover:text-red-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif

{{-- Session Flash Messages --}}
@foreach($types as $type => $config)
    @if(session($config['session_key']))
    <div x-data="{
             show: true,
             init() {
                 // Auto-dismiss setelah 5 detik
                 setTimeout(() => { this.show = false; }, 5000);
             }
         }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-4"
         class="fixed top-4 right-4 z-50 max-w-sm w-full"
         style="{{ $loop->index > 0 ? 'margin-top: ' . ($loop->index * 80) . 'px' : '' }}">
        <div class="flex items-start gap-3 p-4 {{ $config['bg'] }} border {{ $config['border'] }} rounded-xl shadow-lg">
            {{-- Icon --}}
            <div class="flex-shrink-0 w-8 h-8 {{ $config['icon_bg'] }} rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 {{ $config['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $config['icon'] !!}
                </svg>
            </div>

            {{-- Message --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium {{ $config['text'] }} leading-relaxed">
                    {{ session($config['session_key']) }}
                </p>
                {{-- Progress bar auto-dismiss --}}
                <div class="mt-2 h-0.5 bg-current opacity-20 rounded-full overflow-hidden">
                    <div class="h-full bg-current opacity-60 rounded-full"
                         x-data
                         x-init="$el.style.width = '100%'; $el.style.transition = 'width 5s linear'; setTimeout(() => $el.style.width = '0%', 50)">
                    </div>
                </div>
            </div>

            {{-- Close button --}}
            <button @click="show = false"
                    class="flex-shrink-0 {{ $config['icon_color'] }} hover:opacity-70 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif
@endforeach
