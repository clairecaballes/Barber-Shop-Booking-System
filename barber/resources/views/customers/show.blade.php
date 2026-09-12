@extends('layouts.app')
@section('title', 'Customer Profile')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">

    @if (session('status'))
        <x-alert>{{ session('status') }}</x-alert>
    @endif

    {{-- Profile hero --}}
    <section class="bento bento-raised bento-lit relative overflow-hidden p-6 sm:p-8">
        <div class="pointer-events-none absolute -right-12 -top-16 h-44 w-44 rounded-full"
             style="background-image: radial-gradient(circle, var(--accent-glow), transparent 70%);"
             aria-hidden="true"></div>

        <div class="relative flex flex-wrap items-center justify-between gap-6">
            <div class="flex flex-wrap items-center gap-5">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full text-xl font-bold"
                      style="background-image: linear-gradient(180deg, var(--accent-hi), var(--accent)); color: var(--accent-ink);">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </span>
                <div>
                    <h2 class="text-xl font-semibold text-ink">{{ $customer->name }}</h2>
                    <p class="mt-1 text-sm text-muted">
                        {{ $customer->phone ?? 'No phone on file' }}
                        @if ($customer->messenger_id) · Messenger {{ $customer->messenger_id }} @endif
                    </p>
                    @if ($stats['upcoming'])
                        <p class="mt-1 text-xs font-semibold text-accent">
                            Next appointment
                            {{ $stats['upcoming']->appointment_date->format('M j') }} ·
                            {{ \Carbon\Carbon::parse($stats['upcoming']->appointment_time)->format('g:i A') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($stats['cancellation_count'] > 0)
                    <span class="chip text-muted">
                        {{ $stats['cancellation_count'] }} cancellation{{ $stats['cancellation_count'] === 1 ? '' : 's' }}
                    </span>
                @endif
                <x-btn variant="metal" :href="route('customers.edit', $customer)">
                    <x-icon name="edit" class="h-4 w-4" />
                    Edit
                </x-btn>
            </div>
        </div>

        <dl class="relative mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="bento-sunken px-4 py-3">
                <dt class="text-xs text-muted">Total spent</dt>
                <dd class="numeral mt-1 text-lg font-semibold text-ink">{{ money($stats['total_spent']) }}</dd>
            </div>
            <div class="bento-sunken px-4 py-3">
                <dt class="text-xs text-muted">Bookings</dt>
                <dd class="numeral mt-1 text-lg font-semibold text-ink">{{ $stats['total_bookings'] }}</dd>
            </div>
            <div class="bento-sunken px-4 py-3">
                <dt class="text-xs text-muted">Completed</dt>
                <dd class="numeral mt-1 text-lg font-semibold text-ink">{{ $stats['completed_bookings'] }}</dd>
            </div>
            <div class="bento-sunken px-4 py-3">
                <dt class="text-xs text-muted">Last haircut</dt>
                <dd class="numeral mt-1 text-lg font-semibold text-ink">{{ $stats['last_haircut']?->format('M j, Y') ?? 'Never' }}</dd>
            </div>
        </dl>
    </section>

    {{-- Booking history --}}
    <x-panel title="Booking history" bodyClass="p-0" class="bento-raised bento-lift">
        <div class="divide-y divide-line">
            @forelse ($bookings as $booking)
                <div class="flex items-center justify-between gap-4 px-5 py-3.5 transition-colors hover:bg-accent-soft">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-ink">{{ $booking->service->name }}</p>
                        <p class="numeral truncate text-xs text-muted">
                            {{ $booking->appointment_date->format('M j, Y') }} · {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <span class="numeral text-xs font-semibold text-ink">{{ money($booking->price) }}</span>
                        <x-status-badge :status="$booking->status" />
                    </div>
                </div>
            @empty
                <p class="px-5 py-10 text-center text-sm text-muted">No bookings for this customer yet.</p>
            @endforelse
        </div>

        @if ($bookings->hasPages())
            <div class="border-t border-line px-5 py-3">{{ $bookings->withQueryString()->links() }}</div>
        @endif
    </x-panel>
</div>
@endsection