@extends('layouts.app')
@section('title', 'Edit Booking')

@section('content')
<div class="mx-auto max-w-lg" x-data="editBooking()" x-init="init()">
    <x-panel title="Edit booking #{{ $booking->id }}" subtitle="Moving the slot re-checks the chair for overlaps." bodyClass="p-6">
        <form method="POST" action="{{ route('bookings.update', $booking) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <x-field label="Customer" for="customer_id">
                <x-select id="customer_id" name="customer_id" required>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id', $booking->customer_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </x-select>
            </x-field>

            <x-field label="Service" for="service_id">
                <x-select id="service_id" name="service_id" required x-model="selectedService" @change="loadSlots()">
                    @foreach ($services as $s)
                        <option value="{{ $s->id }}" @selected(old('service_id', $booking->service_id) == $s->id)>
                            {{ $s->name }} — {{ money($s->price) }} ({{ $s->duration }} min)
                        </option>
                    @endforeach
                </x-select>
            </x-field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Date" for="appointment_date">
                    <x-input id="appointment_date" type="date" name="appointment_date" required
                             x-model="selectedDate" @change="loadSlots()"
                             value="{{ old('appointment_date', $booking->appointment_date->toDateString()) }}" />
                </x-field>

                <x-field label="Time" for="appointment_time" hint="Slots come from the shop's open hours.">
                    <x-select id="appointment_time" name="appointment_time" required x-model="selectedTime">
                        <template x-for="slot in availableSlots" :key="slot">
                            <option :value="slot" x-text="formatTime(slot)"></option>
                        </template>
                    </x-select>
                </x-field>
            </div>

            <x-field label="Notes" for="notes" errorName="notes">
                <x-textarea id="notes" name="notes" rows="2">{{ old('notes', $booking->notes) }}</x-textarea>
            </x-field>

            <div class="flex justify-end gap-2 border-t border-line pt-5">
                <x-btn variant="ghost" :href="route('bookings.index')">Cancel</x-btn>
                <x-btn variant="accent" type="submit">Save changes</x-btn>
            </div>
        </form>
    </x-panel>
</div>

@push('scripts')
<script>
function editBooking() {
    return {
        selectedDate: '{{ old("appointment_date", $booking->appointment_date->toDateString()) }}',
        selectedService: '{{ old("service_id", $booking->service_id) }}',
        selectedTime: '{{ old("appointment_time", $booking->appointment_time) }}'.slice(0, 5),
        availableSlots: [],
        async init() { await this.loadSlots(); },
        async loadSlots() {
            if (!this.selectedDate || !this.selectedService) return;
            const res = await fetch(`/api/availability?date=${this.selectedDate}&service_id=${this.selectedService}`);
            const data = await res.json();
            let slots = (data.slots || []).map(s => String(s).slice(0, 5));

            // Keep the booking's own current time in the list so saving without
            // touching the slot keeps the original time instead of silently jumping.
            if (this.selectedTime && !slots.includes(this.selectedTime)) {
                slots.unshift(this.selectedTime);
            }

            this.availableSlots = slots;
            if (!slots.includes(this.selectedTime)) {
                this.selectedTime = slots[0] || '';
            }
        },
        formatTime(time) {
            if (!time) return '';
            const [h, m] = time.split(':');
            return `${parseInt(h) % 12 || 12}:${m} ${parseInt(h) >= 12 ? 'PM' : 'AM'}`;
        },
    };
}
</script>
@endpush
@endsection
