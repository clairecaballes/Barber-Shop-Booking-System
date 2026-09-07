@extends('layouts.app')
@section('title', 'Customer Profile')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">{{ session('status') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $customer->name }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                @if ($customer->messenger_id) 💬 Messenger: {{ $customer->messenger_id }} · @endif
                {{ $customer->phone ?? '' }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('customers.edit', $customer) }}" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50">Edit</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-stat-card label="Total Bookings" value="{{ $stats['total_bookings'] }}" icon="bookings" accent="blue" />
        <x-stat-card label="Completed" value="{{ $stats['completed_bookings'] }}" icon="bookings" accent="green" />
        <x-stat-card label="Total Spent" value="{{ money($stats['total_spent']) }}" icon="sales" accent="amber" />
        <x-stat-card label="Last Haircut" value="{{ $stats['last_haircut']?->format('M j, Y') ?? 'Never' }}" icon="calendar" accent="violet" />
        <x-stat-card label="Cancellations" value="{{ $stats['cancellation_count'] }}" icon="bookings" accent="red" />
        @if ($stats['upcoming'])
            <x-stat-card label="Next Appointment" value="{{ $stats['upcoming']->appointment_date->format('M j') . ' ' . \Carbon\Carbon::parse($stats['upcoming']->appointment_time)->format('g:i A') }}" icon="calendar" accent="blue" />
        @endif
    </div>

    <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 px-5 py-4"><h3 class="text-sm font-semibold text-slate-900 dark:text-white">Booking History</h3></div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($bookings as $booking)
                <div class="flex items-center justify-between px-5 py-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $booking->service->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->appointment_date->format('M j, Y') }} · {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-amber-600">{{ money($booking->price) }}</span>
                        <x-status-badge :status="$booking->status" />
                    </div>
                </div>
            @empty
                <p class="px-5 py-6 text-center text-sm text-slate-400">No bookings yet.</p>
            @endforelse
        </div>
        <div class="border-t border-slate-100 dark:border-slate-800 px-5 py-3">{{ $bookings->links() }}</div>
    </section>
</div>
@endsection
