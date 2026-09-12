@extends('layouts.app')
@section('title', 'Bookings')

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <x-alert>{{ session('status') }}</x-alert>
    @endif

    {{-- Filters --}}
    <x-panel bodyClass="p-4">
        <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
            <x-field label="Search" for="filter-search" class="lg:col-span-4">
                <x-input id="filter-search" type="search" name="search" value="{{ request('search') }}" placeholder="Customer name" />
            </x-field>

            <x-field label="Date" for="filter-date" class="lg:col-span-3">
                <x-input id="filter-date" type="date" name="date" value="{{ request('date') }}" />
            </x-field>

            <x-field label="Status" for="filter-status" class="lg:col-span-2">
                <x-select id="filter-status" name="status">
                    <option value="">Any</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ ucfirst(str_replace('_', ' ', $s->value)) }}</option>
                    @endforeach
                </x-select>
            </x-field>

            <x-field label="Service" for="filter-service" class="lg:col-span-3">
                <x-select id="filter-service" name="service_id">
                    <option value="">Any</option>
                    @foreach ($services as $s)
                        <option value="{{ $s->id }}" @selected(request('service_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </x-select>
            </x-field>

            <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-12">
                <x-btn variant="accent" type="submit">
                    <x-icon name="search" class="h-4 w-4" />
                    Filter
                </x-btn>
                <x-btn variant="ghost" :href="route('bookings.index')">Clear</x-btn>
            </div>
        </form>
    </x-panel>

    {{-- Booking list --}}
    <x-panel bodyClass="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-line text-xs text-muted">
                        <th class="px-4 py-3 font-semibold">Customer</th>
                        <th class="px-4 py-3 font-semibold">Service</th>
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Time</th>
                        <th class="px-4 py-3 font-semibold">Price</th>
                        <th class="px-4 py-3 font-semibold">Status</th>
                        <th class="px-4 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($bookings as $booking)
                        <tr class="transition-colors hover:bg-accent-soft">
                            <td class="px-4 py-3 font-medium text-ink">{{ $booking->customer->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3 text-muted">{{ $booking->service->name }}</td>
                            <td class="numeral px-4 py-3 text-muted">{{ $booking->appointment_date->format('M j, Y') }}</td>
                            <td class="numeral px-4 py-3 text-muted">{{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}</td>
                            <td class="numeral px-4 py-3 font-semibold text-ink">{{ money($booking->price) }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$booking->status" /></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1" x-data="{ open: false }">
                                    <a href="{{ route('bookings.show', $booking) }}"
                                       class="rounded-[0.55rem] px-2 py-1 text-xs font-medium text-muted transition-colors hover:bg-accent-soft hover:text-ink">View</a>
                                    <a href="{{ route('bookings.edit', $booking) }}"
                                       class="rounded-[0.55rem] px-2 py-1 text-xs font-medium text-muted transition-colors hover:bg-accent-soft hover:text-ink">Edit</a>

                                    <div class="relative">
                                        <button type="button" @click="open = !open" title="Update status" aria-label="Update status"
                                                class="flex h-7 w-7 items-center justify-center rounded-[0.55rem] border border-line text-muted transition-colors hover:border-accent-line hover:text-ink">
                                            <x-icon name="dots" class="h-4 w-4" />
                                        </button>

                                        <div x-show="open" x-cloak @click.outside="open = false"
                                             class="bento absolute right-0 z-10 mt-1 w-40 overflow-hidden p-1.5">
                                            @foreach (['booked' => 'Booked', 'pending' => 'Pending', 'completed' => 'Complete', 'cancelled' => 'Cancel', 'no_show' => 'No-show'] as $value => $label)
                                                <form method="POST" action="{{ route('bookings.status', $booking) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ $value }}">
                                                    <button type="submit"
                                                            class="flex w-full items-center gap-2 rounded-[0.55rem] px-3 py-1.5 text-left text-xs text-muted transition-colors hover:bg-accent-soft hover:text-ink">
                                                        <span class="h-1.5 w-1.5 rounded-full" style="background-color: var(--st-{{ $value === 'no_show' ? 'noshow' : $value }});"></span>
                                                        {{ $label }}
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-muted">No bookings match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bookings->hasPages())
            <div class="border-t border-line px-4 py-3">{{ $bookings->withQueryString()->links() }}</div>
        @endif
    </x-panel>
</div>
@endsection
