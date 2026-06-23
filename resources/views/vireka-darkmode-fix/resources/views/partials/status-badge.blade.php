@php
    $colorMap = [
        'verified'   => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'pending'    => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'draft'      => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
        'flagged'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'unverified' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
        'rejected'   => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    ];

    $labelMap = [
        'verified'   => '✓ Verified',
        'pending'    => '⏳ Pending',
        'draft'      => '📝 Draft',
        'flagged'    => '🚩 Flagged',
        'unverified' => '— Unverified',
        'rejected'   => '✕ Rejected',
    ];

    $key    = $status ?? 'draft';
    $color  = $colorMap[$key]  ?? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300';
    $label  = $labelMap[$key]  ?? ucfirst($key);
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $color }}">
    {{ $label }}
</span>
