@extends('layouts.app')
@section('title', 'Customers')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    @if (session('status'))
        <x-alert>{{ session('status') }}</x-alert>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex flex-1 items-center gap-2 sm:max-w-md">
            <x-input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or phone" />
            <x-btn variant="metal" type="submit">
                <x-icon name="search" class="h-4 w-4" />
                <span class="sr-only sm:not-sr-only">Search</span>
            </x-btn>
        </form>

        <x-btn variant="accent" :href="route('customers.create')">
            <x-icon name="plus" class="h-4 w-4" />
            Add customer
        </x-btn>
    </div>

    <div class="space-y-3">
        @forelse ($customers as $customer)
            <a href="{{ route('customers.show', $customer) }}"
               class="bento bento-hover flex items-center justify-between gap-4 rounded-tile px-5 py-4">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-line text-xs font-bold text-muted"
                          style="background-image: linear-gradient(180deg, rgba(255,255,255,0.06), transparent);">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-ink">{{ $customer->name }}</p>
                        <p class="truncate text-xs text-muted">
                            {{ $customer->phone ?? 'No phone on file' }}
                            @if ($customer->messenger_id) · Messenger linked @endif
                        </p>
                    </div>
                </div>

                <span class="numeral shrink-0 text-xs text-muted">{{ $customer->bookings()->count() }} bookings</span>
            </a>
        @empty
            <x-panel bodyClass="p-10 text-center">
                <p class="text-sm text-muted">No customers match that search.</p>
                <div class="mt-4 flex justify-center">
                    <x-btn variant="accent" :href="route('customers.create')">Add the first one</x-btn>
                </div>
            </x-panel>
        @endforelse
    </div>

    @if ($customers->hasPages())
        <div>{{ $customers->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
