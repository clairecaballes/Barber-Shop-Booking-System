@extends('layouts.app')
@section('title', 'Bookings')

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">{{ session('status') }}</div>
    @endif

    {{-- Filters --}}
    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Customer name..." class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="mt-1 block rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Status</label>
                <select name="status" class="mt-1 block rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    <option value="">All</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s->value)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Service</label>
                <select name="service_id" class="mt-1 block rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    <option value="">All</option>
                    @foreach ($services as $s)
                        <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:hover:bg-white">Filter</button>
            <a href="{{ route('bookings.index') }}" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50">Clear</a>
        </form>
    </div>

    {{-- Bookings table --}}
    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 text-xs font-medium text-slate-500 dark:text-slate-400 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $booking->customer->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $booking->service->name }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $booking->appointment_date->format('M j, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}</td>
                            <td class="px-4 py-3 font-semibold text-amber-600">{{ money($booking->price) }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$booking->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2" x-data="{ open: false }">
                                    <a href="{{ route('bookings.show', $booking) }}" class="text-xs text-blue-600 hover:underline">View</a>
                                    <a href="{{ route('bookings.edit', $booking) }}" class="text-xs text-amber-600 hover:underline">Edit</a>
                                    <div class="relative">
                                        <button @click="open = !open" title="Update status" aria-label="Update status" class="rounded-md border border-slate-200 p-1 text-slate-500 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-700 dark:text-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 z-10 mt-1 w-36 rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 py-1 shadow-lg" style="display:none;">
                                            <form method="POST" action="{{ route('bookings.status', $booking) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="booked">
                                                <button type="submit" class="block w-full px-3 py-1.5 text-left text-xs text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10">Booked</button>
                                            </form>
                                            <form method="POST" action="{{ route('bookings.status', $booking) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="block w-full px-3 py-1.5 text-left text-xs text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10">Pending</button>
                                            </form>
                                            <form method="POST" action="{{ route('bookings.status', $booking) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="block w-full px-3 py-1.5 text-left text-xs text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10">Complete</button>
                                            </form>
                                            <form method="POST" action="{{ route('bookings.status', $booking) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="block w-full px-3 py-1.5 text-left text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">Cancel</button>
                                            </form>
                                            <form method="POST" action="{{ route('bookings.status', $booking) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="no_show">
                                                <button type="submit" class="block w-full px-3 py-1.5 text-left text-xs text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-500/10">No-show</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-400">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 dark:border-slate-800 px-4 py-3">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
