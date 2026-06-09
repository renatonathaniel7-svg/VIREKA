@php
    $color = match($status ?? '') {
        'verified' => 'bg-green-100 text-green-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'rejected' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="px-2 py-1 text-xs rounded-full {{ $color }}">
    {{ ucfirst($status ?? 'unknown') }}
</span>