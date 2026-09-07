@extends('layouts.app')
@section('title', 'Quick Booking')

@section('content')
<div class="mx-auto max-w-lg" x-data="quickBooking()" x-init="init()">

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">{{ session('status') }}</div>
    @endif

    <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Quick Booking</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Fast entry for Messenger conversations.</p>
        </div>
        <form method="POST" action="{{ route('quick-bookings.store') }}" class="space-y-5 px-6 py-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Customer</label>
                <select name="customer_id" x-model="customerId" @change="selectedCustomerChange" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    <option value="">Select existing customer...</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} {{ $c->messenger_id ? '💬' : '' }}</option>
                    @endforeach
                </select>
                @error('customer_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Or new customer name</label>
                <input type="text" name="customer_name" x-model="customerName" :disabled="customerId" placeholder="Type a new customer name" value="{{ old('customer_name') }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white placeholder-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200 disabled:bg-slate-50 disabled:text-slate-400 dark:disabled:bg-slate-800 dark:disabled:text-slate-500 dark:text-slate-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Date</label>
                <input type="date" name="appointment_date" x-model="selectedDate" @change="loadSlots()" min="{{ now()->toDateString() }}" required value="{{ old('appointment_date') }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Service</label>
                <select name="service_id" required x-model="selectedService" @change="loadSlots()" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    @foreach ($services as $s)
                        <option value="{{ $s->id }}" {{ old('service_id', request('service_id', $s->id)) == $s->id ? 'selected' : '' }}>{{ $s->name }} — {{ money($s->price) }} ({{ $s->duration }}min)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Time</label>
                <input type="time" name="appointment_time" x-model="bookTime" required min="00:01" value="{{ old('appointment_time') }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                <p class="mt-1 text-xs text-slate-400">Pick a time; overlapping bookings are rejected.</p>
                @error('appointment_time') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('bookings.index') }}" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">Book Now</button>
            </div>
        </form>
    </section>
</div>

<script>
function quickBooking() {
    return {
        selectedDate: '{{ old("appointment_date", now()->toDateString()) }}',
        selectedService: '{{ old("service_id", $services->first()?->id) }}',
        availableSlots: [],
        loadingSlots: false,
        customerId: '{{ old("customer_id") }}',
        customerName: '{{ old("customer_name") }}',
        bookTime: '{{ old("appointment_time") }}',
        init() { this.loadSlots(); },
        selectedCustomerChange() {
            if (this.customerId) this.customerName = '';
        },
        async loadSlots() {
            if (!this.selectedDate || !this.selectedService) return;
            this.loadingSlots = true;
            try {
                const res = await fetch(`/api/availability?date=${this.selectedDate}&service_id=${this.selectedService}`);
                const data = await res.json();
                this.availableSlots = data.slots || [];
            } catch(e) { this.availableSlots = []; }
            this.loadingSlots = false;
        },
        formatTime(time) {
            if (!time) return '';
            const [h, m] = time.split(':');
            const hour = parseInt(h);
            return `${hour % 12 || 12}:${m} ${hour >= 12 ? 'PM' : 'AM'}`;
        },
    };
}
</script>
@endsection
