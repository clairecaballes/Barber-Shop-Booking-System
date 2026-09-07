@props(['status'])

@php
    $value = $status instanceof \App\Enums\BookingStatus ? $status->value : (string) $status;

    $classes = match($value) {
        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-400 dark:border-yellow-500/30',
        'booked' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30',
        'completed' => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/30',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30',
        'no_show' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/30',
        default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
    };
    $label = match($value) {
        'pending' => 'Pending',
        'booked' => 'Booked',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No-show',
        default => ucfirst($value),
    };
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $classes }}">
    {{ $label }}
</span>
