@props(['booking', 'showDate' => false])

<div class="flex items-center justify-between gap-4 rounded-lg border border-slate-100 bg-white px-4 py-3 shadow-sm transition-colors hover:border-slate-200 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700">
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->customer->name ?? 'Walk-in' }}</p>
            <x-status-badge :status="$booking->status" />
        </div>
        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $booking->service->name ?? 'N/A' }}</p>
    </div>

    <div class="shrink-0 text-right">
        <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ money($booking->price) }}</p>
        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
            @if ($showDate)
                {{ $booking->appointment_date->format('M j') }}
            @endif
            {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}
        </p>
    </div>
</div>