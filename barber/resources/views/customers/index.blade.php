@extends('layouts.app')
@section('title', 'Customers')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">{{ session('status') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customers..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
            <button type="submit" class="rounded-lg bg-slate-900 px-3 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:hover:bg-white">Search</button>
        </form>
        <a href="{{ route('customers.create') }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">+ Add Customer</a>
    </div>

    <div class="space-y-3">
        @forelse ($customers as $customer)
            <a href="{{ route('customers.show', $customer) }}" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-5 py-4 shadow-sm transition-colors hover:border-amber-200">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $customer->name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        @if ($customer->messenger_id) 💬 Messenger · @endif
                        {{ $customer->phone ?? 'No phone' }}
                    </p>
                </div>
                <span class="text-xs text-slate-400">{{ $customer->bookings()->count() }} bookings</span>
            </a>
        @empty
            <p class="py-8 text-center text-sm text-slate-400">No customers found.</p>
        @endforelse
    </div>
    <div>{{ $customers->links() }}</div>
</div>
@endsection
