@extends('layouts.app')
@section('title', 'Edit Booking')

@section('content')
<div class="mx-auto max-w-lg" x-data="editBooking()" x-init="init()">
    <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Edit Booking #{{ $booking->id }}</h2>
        </div>
        <form method="POST" action="{{ route('bookings.update', $booking) }}" class="space-y-5 px-6 py-5">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Customer</label>
                <select name="customer_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id', $booking->customer_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Service</label>
                <select name="service_id" required x-model="selectedService" @change="loadSlots()" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    @foreach ($services as $s)
                        <option value="{{ $s->id }}" {{ old('service_id', $booking->service_id) == $s->id ? 'selected' : '' }}>{{ $s->name }} — {{ money($s->price) }} ({{ $s->duration }}min)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Date</label>
                <input type="date" name="appointment_date" x-model="selectedDate" @change="loadSlots()" required value="{{ old('appointment_date', $booking->appointment_date->toDateString()) }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Time</label>
                <select name="appointment_time" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    <template x-for="slot in availableSlots" :key="slot">
                        <option :value="slot" x-text="formatTime(slot)" :selected="slot === '{{ old('appointment_time', $booking->appointment_time) }}'"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Notes</label>
                <textarea name="notes" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">{{ old('notes', $booking->notes) }}</textarea>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('bookings.index') }}" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:hover:bg-white">Update</button>
            </div>
        </form>
    </section>
</div>

<script>
function editBooking() {
    return {
        selectedDate: '{{ old("appointment_date", $booking->appointment_date->toDateString()) }}',
        selectedService: '{{ old("service_id", $booking->service_id) }}',
        availableSlots: [],
        async init() { await this.loadSlots(); },
        async loadSlots() {
            if (!this.selectedDate || !this.selectedService) return;
            const res = await fetch(`/api/availability?date=${this.selectedDate}&service_id=${this.selectedService}`);
            const data = await res.json();
            this.availableSlots = data.slots || [];
        },
        formatTime(time) {
            if (!time) return '';
            const [h, m] = time.split(':');
            return `${parseInt(h) % 12 || 12}:${m} ${parseInt(h) >= 12 ? 'PM' : 'AM'}`;
        },
    };
}
</script>
@endsection
