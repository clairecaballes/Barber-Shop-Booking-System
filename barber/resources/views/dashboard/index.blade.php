@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    {{-- Stats grid --}}
    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card
            label="Today's Bookings"
            value="{{ $metrics['today_bookings'] }}"
            icon="bookings"
            accent="blue"
        />
        <x-stat-card
            label="Today's Completed"
            value="{{ $metrics['today_completed'] }}"
            icon="bookings"
            accent="green"
        />
        <x-stat-card
            label="Today's Sales"
            value="{{ money($metrics['today_sales']) }}"
            icon="sales"
            accent="amber"
        />
        <x-stat-card
            label="This Week's Sales"
            value="{{ money($metrics['week_sales']) }}"
            icon="sales"
            accent="violet"
        />
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-stat-card
            label="This Month's Sales"
            value="{{ money($metrics['month_sales']) }}"
            icon="sales"
            accent="amber"
        />
        <x-stat-card
            label="This Year's Sales"
            value="{{ money($metrics['year_sales']) }}"
            icon="sales"
            accent="blue"
        />
        <x-stat-card
            label="Overall Sales"
            value="{{ money($metrics['overall_sales']) }}"
            icon="sales"
            accent="green"
        />
    </div>

    {{-- Schedule + Upcoming + Recent --}}
    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Today's schedule --}}
        <section class="lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="border-b border-slate-100 dark:border-slate-800 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Today's Schedule</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800 px-2 py-2">
                    @forelse ($schedule['today'] as $booking)
                        <div class="flex items-center gap-3 px-3 py-2">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $booking->customer->name ?? 'Walk-in' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->service->name ?? 'N/A' }}</p>
                            </div>
                            <x-status-badge :status="$booking->status" />
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-slate-400">No bookings today</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Upcoming appointments --}}
        <section class="lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="border-b border-slate-100 dark:border-slate-800 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Upcoming Appointments</h2>
                </div>
                <div class="space-y-2 px-2 py-2">
                    @forelse ($schedule['upcoming'] as $booking)
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $booking->customer->name ?? 'Walk-in' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->appointment_date->format('M j') }} · {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}</p>
                            </div>
                            <span class="text-xs font-semibold text-amber-600">{{ money($booking->price) }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-slate-400">No upcoming bookings</p>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Recent bookings --}}
        <section class="lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
                <div class="border-b border-slate-100 dark:border-slate-800 px-5 py-4">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Recent Bookings</h2>
                </div>
                <div class="space-y-2 px-2 py-2">
                    @forelse ($schedule['recent'] as $booking)
                        <div class="flex items-center justify-between gap-3 px-3 py-2">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $booking->customer->name ?? 'Walk-in' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Created {{ $booking->created_at->diffForHumans() }}</p>
                            </div>
                            <x-status-badge :status="$booking->status" />
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-sm text-slate-400">No bookings yet</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection