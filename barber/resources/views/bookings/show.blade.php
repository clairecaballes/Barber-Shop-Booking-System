@extends('layouts.app')
@section('title', 'Booking Details')

@section('content')
<div class="mx-auto max-w-2xl space-y-4">

    <x-panel bodyClass="p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="numeral text-xs text-muted">Ticket #{{ str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT) }}</p>
                <p class="mt-2 text-lg font-semibold text-ink">{{ $booking->customer->name ?? 'Walk-in' }}</p>
                <p class="text-sm text-muted">{{ $booking->service->name }}</p>
            </div>
            <div class="text-right">
                <p class="numeral text-3xl font-semibold text-ink">{{ money($booking->price) }}</p>
                <div class="mt-2 flex justify-end"><x-status-badge :status="$booking->status" /></div>
            </div>
        </div>

        <dl class="mt-6 grid grid-cols-2 gap-5 border-t border-line pt-5 sm:grid-cols-3">
            <div>
                <dt class="text-xs text-muted">Date</dt>
                <dd class="numeral mt-1 text-sm font-medium text-ink">{{ $booking->appointment_date->format('M j, Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-muted">Time</dt>
                <dd class="numeral mt-1 text-sm font-medium text-ink">{{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-muted">Duration</dt>
                <dd class="numeral mt-1 text-sm font-medium text-ink">{{ $booking->service->duration }} min</dd>
            </div>
        </dl>

        @if ($booking->notes)
            <div class="mt-6 bento-sunken p-4">
                <p class="text-xs text-muted">Notes</p>
                <p class="mt-1.5 text-sm text-ink">{{ $booking->notes }}</p>
            </div>
        @endif
    </x-panel>

    <div class="flex items-center justify-between gap-3">
        <x-btn variant="ghost" :href="route('bookings.index')">
            <x-icon name="chevron-left" class="h-4 w-4" />
            All bookings
        </x-btn>
        <x-btn variant="accent" :href="route('bookings.edit', $booking)">
            <x-icon name="edit" class="h-4 w-4" />
            Reschedule
        </x-btn>
    </div>
</div>
@endsection
