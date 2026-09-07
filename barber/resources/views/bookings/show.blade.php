@extends('layouts.app')
@section('title', 'Booking Details')

@section('content')
<div class="mx-auto max-w-lg space-y-6">
    <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Booking #{{ $booking->id }}</h2>
        </div>
        <div class="space-y-4 px-6 py-5">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500 dark:text-slate-400">Customer</span><p class="font-medium text-slate-900 dark:text-white">{{ $booking->customer->name ?? 'Walk-in' }}</p></div>
                <div><span class="text-slate-500 dark:text-slate-400">Service</span><p class="font-medium text-slate-900 dark:text-white">{{ $booking->service->name }}</p></div>
                <div><span class="text-slate-500 dark:text-slate-400">Date</span><p class="font-medium text-slate-900 dark:text-white">{{ $booking->appointment_date->format('M j, Y') }}</p></div>
                <div><span class="text-slate-500 dark:text-slate-400">Time</span><p class="font-medium text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}</p></div>
                <div><span class="text-slate-500 dark:text-slate-400">Price</span><p class="font-bold text-amber-600">{{ money($booking->price) }}</p></div>
                <div><span class="text-slate-500 dark:text-slate-400">Status</span><div class="mt-1"><x-status-badge :status="$booking->status" /></div></div>
            </div>
            @if ($booking->notes)
                <div><span class="text-sm text-slate-500 dark:text-slate-400">Notes</span><p class="mt-1 text-sm text-slate-700 dark:text-slate-200">{{ $booking->notes }}</p></div>
            @endif
        </div>
        <div class="border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex justify-between">
            <a href="{{ route('bookings.index') }}" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50">Back</a>
            <a href="{{ route('bookings.edit', $booking) }}" class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:hover:bg-white">Edit</a>
        </div>
    </section>
</div>
@endsection
