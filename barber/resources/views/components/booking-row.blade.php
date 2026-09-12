@props(['booking', 'showDate' => false])

<div class="bento bento-hover flex items-center justify-between gap-4 rounded-tile px-4 py-3">
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <p class="truncate text-sm font-semibold text-ink">{{ $booking->customer->name ?? 'Walk-in' }}</p>
            <x-status-badge :status="$booking->status" />
        </div>
        <p class="mt-1 text-xs text-muted">{{ $booking->service->name ?? 'N/A' }}</p>
    </div>

    <div class="shrink-0 text-right">
        <p class="numeral text-sm font-semibold text-ink">{{ money($booking->price) }}</p>
        <p class="numeral mt-1 text-xs text-muted">
            @if ($showDate)
                {{ $booking->appointment_date->format('M j') }}
            @endif
            {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}
        </p>
    </div>
</div>
