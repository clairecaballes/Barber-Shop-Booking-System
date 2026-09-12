@extends('layouts.app')
@section('title', 'Quick Booking')

@section('content')
<div class="mx-auto max-w-lg" x-data="quickBooking()" x-init="init()">

    @if (session('status'))
        <div class="mb-4"><x-alert>{{ session('status') }}</x-alert></div>
    @endif

    {{-- Success confirmation shown after an in-page save. --}}
    <div x-show="submitted" x-cloak class="mb-4">
        <x-alert>Booking added to the calendar. The slot is locked, the customer is on the chair.</x-alert>
        <div class="mt-3 flex justify-end gap-2">
            <x-btn variant="ghost" type="button" @click="bookAnother">Book another</x-btn>
            <x-btn variant="accent" :href="route('calendar.index')">View calendar</x-btn>
        </div>
    </div>

    {{-- Inline error from the AJAX save. --}}
    <div x-show="submitError" x-cloak class="mb-4">
        <x-alert tone="danger"><span x-text="submitError"></span></x-alert>
    </div>

    <x-panel title="Walk-in booking" subtitle="For Messenger and counter requests — two taps and it's on the chair." bodyClass="p-6">
        <form method="POST" action="{{ route('quick-bookings.store') }}" class="space-y-5" x-ref="form"
              x-show="!submitted" @submit.prevent="submitQuick($event)">
            @csrf

            <x-field label="Existing customer" for="customer_id" errorName="customer_id">
                <x-select id="customer_id" name="customer_id" x-model="customerId" @change="selectedCustomerChange">
                    <option value="">None — this is a new customer</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>
                            {{ $c->name }}{{ $c->messenger_id ? ' · Messenger' : '' }}
                        </option>
                    @endforeach
                </x-select>
            </x-field>

            <x-field label="New customer name" for="customer_name" hint="Leave blank if you picked someone above." errorName="customer_name">
                <x-input id="customer_name" name="customer_name" x-model="customerName" ::disabled="customerId"
                         placeholder="e.g. Marco Reyes" value="{{ old('customer_name') }}" />
            </x-field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Date" for="appointment_date">
                    <x-input id="appointment_date" type="date" name="appointment_date" required
                             x-model="selectedDate" @change="loadSlots()"
                             min="{{ now()->toDateString() }}" value="{{ old('appointment_date') }}" />
                </x-field>

                <x-field label="Service" for="service_id">
                    <x-select id="service_id" name="service_id" required x-model="selectedService" @change="loadSlots()">
                        @foreach ($services as $s)
                            <option value="{{ $s->id }}" @selected(old('service_id', request('service_id', $s->id)) == $s->id)>
                                {{ $s->name }} — {{ money($s->price) }} ({{ $s->duration }} min)
                            </option>
                        @endforeach
                    </x-select>
                </x-field>
            </div>

            <x-field label="Time" for="appointment_time" hint="Overlapping bookings are rejected." errorName="appointment_time">
                <x-input id="appointment_time" type="time" name="appointment_time" required
                         x-model="bookTime" min="00:01" value="{{ old('appointment_time') }}" />
            </x-field>

            <div class="flex justify-end gap-2 border-t border-line pt-5">
                <x-btn variant="ghost" :href="route('bookings.index')">Cancel</x-btn>
                <x-btn variant="accent" type="submit">Book it</x-btn>
            </div>
        </form>
    </x-panel>
</div>

@push('scripts')
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
        submitted: false,
        submitError: '',
        init() { this.loadSlots(); },
        selectedCustomerChange() {
            if (this.customerId) this.customerName = '';
        },
        async submitQuick(event) {
            this.submitError = '';
            const form = event.target;
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new FormData(form),
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    this.submitError = data.message
                        ?? (data.errors ? Object.values(data.errors).flat().join(' ') : 'Could not create this booking. Check the time and try again.');
                    return;
                }

                this.submitted = true;
            } catch (e) {
                this.submitError = 'Something went wrong. Please try again.';
            }
        },
        bookAnother() {
            this.submitted = false;
            this.submitError = '';
            if (this.$refs.form) this.$refs.form.reset();
            this.customerId = '';
            this.customerName = '';
            this.selectedDate = new Date().toISOString().split('T')[0];
            this.loadSlots();
        },
        async loadSlots() {
            if (!this.selectedDate || !this.selectedService) return;
            this.loadingSlots = true;
            try {
                const res = await fetch(`/api/availability?date=${this.selectedDate}&service_id=${this.selectedService}`);
                const data = await res.json();
                this.availableSlots = data.slots || [];
            } catch (e) { this.availableSlots = []; }
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
@endpush
@endsection
