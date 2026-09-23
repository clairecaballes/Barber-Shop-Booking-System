@extends('layouts.app')
@section('title', 'Calendar')

@section('content')
<div x-data="calendarApp()" x-init="init()">

    @if (session('status'))
        <div class="mb-4"><x-alert>{{ session('status') }}</x-alert></div>
    @endif

    {{-- Toast --}}
    <div x-show="toast" x-transition.opacity.duration.300ms x-cloak
         class="glass fixed left-1/2 top-4 z-[60] flex -translate-x-1/2 items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold shadow-lg"
         style="display:none; border:1px solid var(--accent-line); color: var(--accent);">
        <span class="h-2 w-2 rounded-full" style="background-color: var(--accent); box-shadow: 0 0 10px var(--accent);"></span>
        <span x-text="toast"></span>
    </div>

    {{-- Controls --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <label for="calendar-month-picker" class="sr-only">Choose month</label>
            <input id="calendar-month-picker" type="month" x-model="monthPicker" @change="changeMonth($event.target.value)"
                   class="calendar-month-control">

            <x-btn variant="ghost" @click="calendar.today()" class="border border-line bg-surface/60">
                <x-icon name="calendar" class="h-4 w-4" />
                Today
            </x-btn>
        </div>

        <div class="flex items-center gap-2.5">
            <div class="chrome chrome-sheen flex items-center gap-1 rounded-[0.85rem] border border-white/10 p-1.5 shadow-lg">
                @foreach (['dayGridMonth' => 'Month', 'timeGridWeek' => 'Week', 'timeGridDay' => 'Day'] as $view => $label)
                    <button @click="changeView('{{ $view }}')"
                            :class="currentView === '{{ $view }}' ? 'is-active' : ''"
                            class="view-tab">{{ $label }}</button>
                @endforeach
            </div>

            <x-btn variant="accent" @click="downloadCalendar()">
                <x-icon name="download" class="h-4 w-4" />
                Export
            </x-btn>
        </div>
    </div>

    {{-- Day key: frosted chips so the legend reads as part of the board. --}}
    <div class="bento glass mb-5 inline-flex flex-wrap items-center gap-1 rounded-full px-4 py-2.5 text-xs text-muted">
        @foreach (['pending' => 'Pending', 'booked' => 'Booked', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No-shows'] as $key => $label)
            <span class="chip !border-transparent">
                <span class="h-2 w-2 rounded-full" style="background-color: var(--st-{{ $key === 'no_show' ? 'noshow' : $key }}); box-shadow: 0 0 8px var(--st-{{ $key === 'no_show' ? 'noshow' : $key }});"></span>
                {{ $label }}
            </span>
        @endforeach
        <span class="chip !border-transparent">
            <span class="h-2 w-2 rounded-full" style="background-color: var(--st-blocked); box-shadow: 0 0 8px var(--st-blocked);"></span>
            Blocked
        </span>
    </div>

    {{-- Board --}}
    <x-panel bodyClass="p-3 sm:p-4" class="bento-raised">
        <div id="calendar-wrapper" class="h-[calc(100svh-16rem)] lg:h-[42rem]">
            <div id="calendar"></div>
        </div>
    </x-panel>

    {{-- Booking detail --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
         @keydown.escape.window="showModal = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="showModal = false"></div>

        <div class="bento bento-accent-edge glass-lite modal-card relative flex w-full max-w-md flex-col overflow-y-auto overscroll-contain p-6">
            <div class="flex items-start justify-between gap-4">
                <h3 class="text-base font-semibold text-ink" x-text="selectedBooking?.title"></h3>
                <button type="button" @click="showModal = false" aria-label="Close booking details"
                        class="flex h-8 w-8 items-center justify-center rounded-[0.65rem] border border-line text-muted transition-all duration-200 hover:border-accent-line hover:text-ink">
                    <x-icon name="close" class="h-4 w-4" />
                </button>
            </div>

            {{-- On-leave day: the full window and the reason. --}}
            <template x-if="selectedBooking?.extendedProps?.blocked">
                <dl class="mt-5 space-y-3 rounded-tile border border-line bg-surface/40 p-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Date</dt>
                        <dd class="font-medium text-ink" x-text="formatDate(selectedBooking?.extendedProps?.date)"></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Leave starts</dt>
                        <dd class="numeral font-semibold text-ink" x-text="formatWindowStart()"></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Leave ends</dt>
                        <dd class="numeral font-semibold text-ink" x-text="formatWindowEnd()"></dd>
                    </div>
                    <template x-if="selectedBooking?.extendedProps?.reason">
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted">Reason</dt>
                            <dd class="font-medium text-ink" x-text="selectedBooking?.extendedProps?.reason"></dd>
                        </div>
                    </template>
                </dl>
            </template>

            {{-- Regular booking detail. --}}
            <template x-if="!selectedBooking?.extendedProps?.blocked">
                <dl class="mt-5 space-y-3 rounded-tile border border-line bg-surface/40 p-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Customer</dt>
                        <dd class="font-medium text-ink" x-text="selectedBooking?.extendedProps?.customerName"></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Service</dt>
                        <dd class="font-medium text-ink" x-text="selectedBooking?.extendedProps?.serviceName"></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Status</dt>
                        <dd class="uppercase font-medium tracking-wide" x-text="selectedBooking?.extendedProps?.status"></dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Price</dt>
                        <dd class="numeral font-semibold text-ink" x-text="formatMoney(selectedBooking?.extendedProps?.price)"></dd>
                    </div>
                </dl>
            </template>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-2 border-t border-line pt-5">
                <template x-if="selectedBooking?.extendedProps?.blocked">
                    <div class="flex w-full flex-wrap items-center justify-end gap-2">
                        <x-btn variant="metal" @click="editLeave()">Edit time</x-btn>
                        <x-btn variant="danger" @click="cancelLeave()" x-bind:disabled="saving">
                            <span x-show="!saving">Cancel leave</span>
                            <span x-show="saving">Cancelling…</span>
                        </x-btn>
                    </div>
                </template>
                <template x-if="!selectedBooking?.extendedProps?.blocked">
                    <x-btn variant="danger" @click="deleteBooking()" x-bind:disabled="saving">
                        <x-icon name="trash" class="h-4 w-4" />
                        <span x-text="saving ? 'Deleting…' : 'Delete'"></span>
                    </x-btn>
                    <x-btn variant="ghost" @click="openBooking()">Open booking</x-btn>
                </template>
            </div>
        </div>
    </div>

    {{-- Edit leave window --}}
    <div x-show="showEditLeaveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
         @keydown.escape.window="closeEditLeave()">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="closeEditLeave()"></div>

        <div class="bento bento-accent-edge glass-lite modal-card relative flex w-full max-w-md flex-col overflow-y-auto overscroll-contain p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-ink">Edit barber leave</h3>
                    <p class="mt-1 text-sm text-muted" x-text="formatDate(editLeaveDate)"></p>
                </div>
                <button type="button" @click="closeEditLeave()" aria-label="Close edit leave"
                        class="flex h-8 w-8 items-center justify-center rounded-[0.65rem] border border-line text-muted transition-all duration-200 hover:border-accent-line hover:text-ink">
                    <x-icon name="close" class="h-4 w-4" />
                </button>
            </div>

            <form @submit.prevent="saveLeaveEdit()" class="mt-5 space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Leave from" for="edit_leave_start">
                        <x-input id="edit_leave_start" type="time" x-model="editLeaveStart" />
                    </x-field>
                    <x-field label="Leave until" for="edit_leave_end" hint="Blank times use the day's opening hours.">
                        <x-input id="edit_leave_end" type="time" x-model="editLeaveEnd" />
                    </x-field>
                </div>

                <p x-show="editLeaveError" x-text="editLeaveError" class="text-xs font-medium text-red-500"></p>

                <div class="flex flex-wrap justify-end gap-2 border-t border-line pt-5">
                    <x-btn variant="ghost" type="button" @click="closeEditLeave()">Cancel</x-btn>
                    <x-btn variant="accent" type="submit" x-bind:disabled="saving">
                        <span x-show="!saving">Save leave</span>
                        <span x-show="saving">Saving…</span>
                    </x-btn>
                </div>
            </form>
        </div>
    </div>

    {{-- Reschedule --}}
    <div x-show="showRescheduleModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
         @keydown.escape.window="closeReschedule()">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="closeReschedule()"></div>

        <div class="bento bento-accent-edge glass-lite modal-card relative flex w-full max-w-md flex-col overflow-y-auto overscroll-contain p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-ink">Reschedule booking</h3>
                    <p class="mt-1 text-sm text-muted" x-text="selectedBooking?.extendedProps?.customerName"></p>
                </div>
                <button type="button" @click="closeReschedule()" aria-label="Close reschedule"
                        class="flex h-8 w-8 items-center justify-center rounded-[0.65rem] border border-line text-muted transition-all duration-200 hover:border-accent-line hover:text-ink">
                    <x-icon name="close" class="h-4 w-4" />
                </button>
            </div>

            <form @submit.prevent="saveReschedule()" class="mt-5 space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Date" for="reschedule_date">
                        <x-input id="reschedule_date" type="date" name="appointment_date"
                                 x-model="rescheduleDate" min="{{ now()->toDateString() }}" @change="loadRescheduleSlots()" />
                    </x-field>

                    <x-field label="Time" for="reschedule_time" hint="Save keeps the slot locked until you change it.">
                        <x-select id="reschedule_time" name="appointment_time" x-model="rescheduleTime">
                            <template x-for="slot in rescheduleSlots" :key="slot">
                                <option :value="slot" x-text="formatTime(slot)"></option>
                            </template>
                        </x-select>
                    </x-field>
                </div>

                <p x-show="rescheduleError" x-text="rescheduleError" class="text-xs font-medium text-red-500"></p>

                <div class="flex flex-wrap justify-end gap-2 border-t border-line pt-5">
                    <x-btn variant="ghost" type="button" @click="closeReschedule()">Cancel</x-btn>
                    <x-btn variant="accent" type="submit" x-bind:disabled="saving">
                        <span x-show="!saving">Save changes</span>
                        <span x-show="saving">Saving…</span>
                    </x-btn>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick book --}}
    <div x-show="showBookModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md" @click="showBookModal = false"></div>

        <div class="bento bento-accent-edge glass-lite modal-card relative flex w-full max-w-md flex-col overflow-y-auto overscroll-contain p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-ink">Quick book</h3>
                    <p class="numeral mt-1 text-xs text-muted" x-text="bookDate"></p>
                </div>
                <button type="button" @click="showBookModal = false" aria-label="Close"
                        class="flex h-8 w-8 items-center justify-center rounded-[0.65rem] border border-line text-muted transition-all duration-200 hover:border-accent-line hover:text-ink">
                    <x-icon name="close" class="h-4 w-4" />
                </button>
            </div>

            <form @submit.prevent="submitQuickBook()" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="appointment_date" x-bind:value="bookDate">

                <x-field label="Existing customer">
                    <x-select name="customer_id" x-model="customerId" @change="customerSelected">
                        <option value="">None — new customer</option>
                        @foreach (\App\Models\Customer::orderBy('name')->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </x-select>
                </x-field>

                <x-field label="New customer name">
                    <x-input name="customer_name" x-model="customerName" @input="typedCustomer()" placeholder="e.g. Marco Reyes" />
                </x-field>

                <x-field label="Service">
                    <x-select name="service_id" required x-model="bookServiceId">
                        @foreach (\App\Models\Service::where('active', true)->orderBy('name')->get() as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} — {{ money($s->price) }} ({{ $s->duration }} min)</option>
                        @endforeach
                    </x-select>
                </x-field>

                <x-field label="Time" hint="Overlapping bookings are rejected.">
                    <x-input type="time" name="appointment_time" x-model="bookTime" required />
                </x-field>

                <p x-show="quickError" x-text="quickError" class="text-xs font-medium text-red-500"></p>

                <div class="grid grid-cols-2 gap-2 border-t border-line pt-5 sm:flex sm:justify-end sm:gap-2">
                    <x-btn variant="ghost" type="button" @click="showBookModal = false" class="w-full sm:w-auto">Cancel</x-btn>
                    <x-btn variant="accent" type="submit" x-bind:disabled="saving" class="w-full sm:w-auto">
                        <span x-show="!saving" class="inline-flex items-center gap-1.5">
                            <x-icon name="plus" class="h-4 w-4" />
                            <span class="sm:hidden">Add</span>
                            <span class="hidden sm:inline">Add to calendar</span>
                        </span>
                        <span x-show="saving" class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M22 12a10 10 0 0 1-10 10" stroke-linecap="round"/></svg>
                            Saving…
                        </span>
                    </x-btn>
                </div>
            </form>

            {{-- Its own form so blocking the day never requires the booking fields above. --}}
            <form @submit.prevent="submitBlock()" class="mt-5 space-y-4 border-t border-line pt-5">
                @csrf
                <input type="hidden" name="date" x-bind:value="bookDate">

                <div>
                    <p class="text-xs font-semibold text-ink">Barber on leave</p>
                    <p class="mt-1 text-xs text-muted">Set the window the barber is away, then block the day. Blank times use the day's opening hours.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Leave from" for="block_start">
                        <x-input id="block_start" type="time" x-model="blockStart" />
                    </x-field>
                    <x-field label="Leave until" for="block_end">
                        <x-input id="block_end" type="time" x-model="blockEnd" />
                    </x-field>
                </div>

                <p x-show="blockError" x-text="blockError" class="text-xs font-medium text-red-500"></p>

                <div class="flex justify-end">
                    <x-btn variant="danger" type="submit" x-bind:disabled="saving" class="sm:ml-auto">
                        <span x-show="!saving" class="inline-flex items-center gap-1.5">
                            <x-icon name="clock" class="h-4 w-4" />
                            <span class="sm:hidden">Block</span>
                            <span class="hidden sm:inline">Block the day</span>
                        </span>
                        <span x-show="saving" class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M22 12a10 10 0 0 1-10 10" stroke-linecap="round"/></svg>
                            Saving…
                        </span>
                    </x-btn>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('calendarApp', () => ({
        calendar: null,
        currentView: 'dayGridMonth',
        calendarTitle: '',
        showModal: false,
        selectedBooking: null,
        showEditLeaveModal: false,
        editLeaveBlockId: '',
        editLeaveDate: '',
        editLeaveStart: '',
        editLeaveEnd: '',
        editLeaveError: '',
        showRescheduleModal: false,
        rescheduleDate: '',
        rescheduleTime: '',
        rescheduleSlots: [],
        rescheduleError: '',
        showBookModal: false,
        bookDate: '',
        bookServiceId: '',
        availableSlots: [],
        customerId: '',
        customerName: '',
        bookTime: '',
        blockStart: '',
        blockEnd: '',
        monthPicker: '',
        saving: false,
        quickError: '',
        blockError: '',
        toast: '',

        init() {
            const self = this;
            const FC = window.FullCalendar;
            const calendarEl = document.getElementById('calendar');

            this.calendar = new FC.Calendar(calendarEl, {
                plugins: [FC.dayGridPlugin, FC.timeGridPlugin, FC.interactionPlugin],
                initialView: 'dayGridMonth',
                headerToolbar: false,
                editable: false,
                selectable: true,
                nowIndicator: true,
                displayEventTime: true,
                eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
                height: '100%',
                expandRows: true,
                dayMaxEvents: 4,
                eventSources: [{
                    url: '{{ route("api.calendar.events") }}',
                    method: 'GET',
                    extraParams: {
                        _token: '{{ csrf_token() }}',
                    },
                }],
                dateClick(info) {
                    self.quickError = '';
                    self.blockError = '';
                    self.bookDate = info.dateStr.split('T')[0];
                    self.customerId = '';
                    self.customerName = '';
                    self.bookTime = '';
                    self.blockStart = '';
                    self.blockEnd = '';
                    self.bookServiceId = @json(\App\Models\Service::where('active', true)->value('id')) ?? '';
                    self.showBookModal = true;
                    self.loadSlots();
                },
                eventClick(info) {
                    self.selectedBooking = info.event;
                    self.showModal = true;
                },
                datesSet(info) {
                    self.calendarTitle = info.view.title;
                    self.currentView = info.view.type;
                    self.syncMonthPicker();
                },
            });

            this.calendar.render();
            this.syncMonthPicker();
        },

        syncMonthPicker() {
            const date = this.calendar?.getDate ? this.calendar.getDate() : new Date();
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            this.monthPicker = `${year}-${month}`;
        },

        changeMonth(value) {
            if (!value) return;
            const [year, month] = value.split('-').map(Number);
            this.calendar?.gotoDate(new Date(year, month - 1, 1));
            this.syncMonthPicker();
        },

        changeView(view) {
            this.calendar.changeView(view);
            this.syncMonthPicker();
        },

        openBooking() {
            const id = this.selectedBooking?.id;
            if (!id) return;
            window.location.href = '/bookings/' + id;
        },

        // --- Leave window ---------------------------------------------------

        editLeave() {
            const booking = this.selectedBooking;
            if (!booking) return;
            const props = booking.extendedProps || {};

            this.editLeaveBlockId = props.blockId;
            this.editLeaveDate = props.date;
            this.editLeaveStart = props.startTime || '';
            this.editLeaveEnd = props.endTime || '';
            this.editLeaveError = '';
            this.showModal = false;
            this.showEditLeaveModal = true;
        },

        closeEditLeave() {
            this.showEditLeaveModal = false;
            this.editLeaveError = '';
        },

        async saveLeaveEdit() {
            this.editLeaveError = '';
            if (!this.editLeaveBlockId) return;

            if (this.editLeaveStart && this.editLeaveEnd && this.editLeaveEnd <= this.editLeaveStart) {
                this.editLeaveError = 'The leave end time must be after the start time.';
                return;
            }

            this.saving = true;
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                if (this.editLeaveStart) formData.append('start_time', this.editLeaveStart);
                if (this.editLeaveEnd) formData.append('end_time', this.editLeaveEnd);

                const res = await fetch(`{{ url('calendar/blocked-slots') }}/${this.editLeaveBlockId}`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    this.editLeaveError = data.message || 'Could not update this leave.';
                    return;
                }

                this.showEditLeaveModal = false;
                this.toast = data.message || 'Leave updated.';
                this.calendar.refetchEvents();
                setTimeout(() => { this.toast = ''; }, 3500);
            } catch (e) {
                this.editLeaveError = 'Something went wrong. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        formatWindowStart() {
            const props = this.selectedBooking?.extendedProps || {};
            if (!props.startTime) return 'All day';
            return this.formatTime(props.startTime);
        },

        formatWindowEnd() {
            const props = this.selectedBooking?.extendedProps || {};
            if (!props.endTime) return 'All day';
            return this.formatTime(props.endTime);
        },

        async cancelLeave() {
            const blockId = this.selectedBooking?.extendedProps?.blockId;
            if (!blockId) return;

            this.saving = true;
            try {
                const res = await fetch(`{{ url('calendar/blocked-slots') }}/${blockId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    this.toast = data.message || 'Could not cancel this leave.';
                    return;
                }

                this.showModal = false;
                this.toast = data.message || 'Leave cancelled.';
                this.calendar.refetchEvents();
                setTimeout(() => { this.toast = ''; }, 3500);
            } catch (e) {
                this.toast = 'Something went wrong. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        // --- Booking actions ------------------------------------------------

        async deleteBooking() {
            const booking = this.selectedBooking;
            if (!booking) return;
            if (!window.confirm('Delete this booking? This cannot be undone.')) return;

            this.saving = true;
            try {
                const res = await fetch(`{{ url('bookings') }}/${booking.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    this.toast = data.message || 'Could not delete this booking.';
                    return;
                }

                this.showModal = false;
                this.toast = data.message || 'Booking deleted.';
                this.calendar.refetchEvents();
                setTimeout(() => { this.toast = ''; }, 3500);
            } catch (e) {
                this.toast = 'Something went wrong. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        formatDate(value) {
            if (!value) return '';
            const d = new Date(String(value).slice(0, 10) + 'T00:00:00');
            if (isNaN(d)) return value;
            return d.toLocaleString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        },

        rescheduleBooking() {
            const booking = this.selectedBooking;
            if (!booking) return;
            const props = booking.extendedProps || {};

            this.rescheduleDate = this.toDateInput(props.appointmentDate || props.start || booking.start || '');
            this.rescheduleTime = this.toTimeInput(props.appointmentTime || props.start || booking.start || '');

            this.rescheduleError = '';
            this.showRescheduleModal = true;
            this.loadRescheduleSlots();
        },

        toDateInput(value) {
            if (!value) return '';
            if (typeof value === 'string') {
                const iso = value.match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (iso) return iso[0];
            }
            return this.padDate(value);
        },

        toTimeInput(value) {
            if (!value) return '';
            if (typeof value === 'string') {
                const match = value.match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/);
                if (match) return match[4] + ':' + match[5];
                const hm = value.match(/^(\d{2}):(\d{2})/);
                if (hm) return hm[1] + ':' + hm[2];
            }
            if (value instanceof Date && !isNaN(value)) {
                return String(value.getHours()).padStart(2, '0') + ':' + String(value.getMinutes()).padStart(2, '0');
            }
            return '';
        },

        padDate(value) {
            if (!(value instanceof Date) || isNaN(value)) return '';
            const y = value.getFullYear();
            const m = String(value.getMonth() + 1).padStart(2, '0');
            const d = String(value.getDate()).padStart(2, '0');
            return y + '-' + m + '-' + d;
        },

        closeReschedule() {
            this.showRescheduleModal = false;
            this.rescheduleError = '';
        },

        async loadRescheduleSlots() {
            this.rescheduleError = '';
            this.rescheduleSlots = [];
            const serviceId = this.selectedBooking?.extendedProps?.serviceId;
            if (!serviceId || !this.rescheduleDate) return;

            const res = await fetch(`{{ route('api.availability.index') }}?date=${this.rescheduleDate}&service_id=${serviceId}`);
            const data = await res.json().catch(() => ({}));
            let slots = (data.slots || []).map(s => String(s).slice(0, 5));

            // Keep the booking's own current time selectable so it can be saved unchanged.
            const current = String(this.selectedBooking?.extendedProps?.appointmentTime || '').slice(0, 5);
            if (current && !slots.includes(current)) slots.unshift(current);

            this.rescheduleSlots = slots;
            if (!this.rescheduleTime || !slots.includes(this.rescheduleTime)) {
                this.rescheduleTime = slots[0] || '';
            }
        },

        async saveReschedule() {
            this.rescheduleError = '';
            const booking = this.selectedBooking;
            if (!booking) return;
            if (!this.rescheduleDate) {
                this.rescheduleError = 'Please choose the date to move this booking to.';
                return;
            }
            if (!this.rescheduleTime) {
                this.rescheduleError = 'This date has no open slots. Pick another date or free the schedule first.';
                return;
            }

            this.saving = true;
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('appointment_date', this.rescheduleDate);
                formData.append('appointment_time', this.rescheduleTime.slice(0, 5));

                const res = await fetch(`{{ url('bookings') }}/${booking.id}/reschedule`, {
                    method: 'PATCH',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData,
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    this.rescheduleError = data.message || 'Could not reschedule this booking. Please try again.';
                    return;
                }

                this.showRescheduleModal = false;
                this.showModal = false;
                this.toast = data.message || 'Booking rescheduled.';
                this.calendar.refetchEvents();
                setTimeout(() => { this.toast = ''; }, 3500);
            } catch (e) {
                this.rescheduleError = 'Something went wrong. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        typedCustomer() {
            if (this.customerName) {
                this.customerId = '';
            }
        },

        customerSelected() {
            if (this.customerId) {
                this.customerName = '';
            }
        },

        async submitQuickBook() {
            this.quickError = '';
            if (!this.bookDate) return;

            if (!this.customerId && !this.customerName.trim()) {
                this.quickError = 'Please select a customer or type a new customer name.';
                return;
            }
            if (!this.bookServiceId) {
                this.quickError = 'Please choose a service.';
                return;
            }
            if (!this.bookTime) {
                this.quickError = 'Please pick a time.';
                return;
            }

            this.saving = true;
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('appointment_date', this.bookDate);
                if (this.customerId) {
                    formData.append('customer_id', this.customerId);
                } else {
                    formData.append('customer_name', this.customerName);
                }
                formData.append('service_id', this.bookServiceId);
                formData.append('appointment_time', this.bookTime);

                const res = await fetch('{{ route('quick-bookings.store') }}', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData,
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    this.quickError = data.message || data.errors
                        ? (data.errors ? Object.values(data.errors).flat().join(' ') : data.message)
                        : 'Could not add this booking. Please check the time and try again.';
                    return;
                }

                this.showBookModal = false;
                this.toast = data.message || 'Booking added to calendar.';
                this.calendar.refetchEvents();
                setTimeout(() => { this.toast = ''; }, 3500);
            } catch (e) {
                this.quickError = 'Something went wrong. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async submitBlock() {
            this.blockError = '';
            if (!this.bookDate) return;

            this.saving = true;
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('date', this.bookDate);
                if (this.blockStart) formData.append('start_time', this.blockStart);
                if (this.blockEnd) formData.append('end_time', this.blockEnd);

                const res = await fetch('{{ route('calendar.blocks.store') }}', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData,
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    this.blockError = data.message || 'Could not block this day.';
                    return;
                }

                this.showBookModal = false;
                this.toast = data.message || 'Schedule blocked.';
                this.calendar.refetchEvents();
                setTimeout(() => { this.toast = ''; }, 3500);
            } catch (e) {
                this.blockError = 'Something went wrong. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async downloadCalendar() {
            // On small screens a screenshot of the grid doesn't survive screen
            // width — export an attractive day-by-day list instead.
            if (window.matchMedia('(max-width: 639px)').matches) {
                return this.downloadScheduleList();
            }

            const el = document.getElementById('calendar-wrapper');
            if (!el || !window.html2canvas) return;

            const dark = document.documentElement.classList.contains('dark');
            const sheet = dark ? '#0b0c0f' : '#f2f3f5';
            const ink = dark ? '#f5f5f7' : '#16181d';
            const muted = dark ? '#9ca3af' : '#5f6672';
            const hairline = dark ? 'rgba(255,255,255,0.14)' : '#d9d6dc';
            const hairlineSoft = dark ? 'rgba(255,255,255,0.07)' : '#eceaf0';
            const accent = dark ? '#ccff00' : '#4d7c0f';

            const shopName = '{{ \App\Models\BusinessSetting::get('shop_name', 'Barber Shop') }}';
            const month = this.calendarTitle || this.currentMonthLabel();

            // Frame the live grid in a symmetric, printed-style sheet. All DOM
            // surgery happens on html2canvas's clone only — the page is untouched.
            const pad = 44;
            const frameW = el.offsetWidth + pad * 2;
            const frameH = Math.ceil(el.offsetHeight + 190);

            try {
                this.toast = 'Preparing your schedule\u2026';
                const canvas = await window.html2canvas(el, {
                    backgroundColor: sheet,
                    scale: 2,
                    width: frameW,
                    height: frameH,
                    windowWidth: frameW,
                    windowHeight: frameH,
                    onclone: (doc, target) => {
                    const style = doc.createElement('style');
                    style.textContent = '#calendar-wrapper .fc-day-past, #calendar-wrapper .fc-day-other { visibility: hidden !important; }';
                    doc.head.appendChild(style);

                    const frame = doc.createElement('div');
                    frame.style.cssText =
                        'position:absolute;top:0;left:0;width:' + frameW + 'px;height:' + frameH + 'px;' +
                        'display:flex;flex-direction:column;box-sizing:border-box;' +
                        'background:' + sheet + ';border:1px solid ' + hairline + ';padding:24px ' + pad + 'px 18px;';

                    const rails =
                        '<span style="position:absolute;top:20px;bottom:16px;left:24px;width:1px;background:' + hairline + ';"></span>'
                        + '<span style="position:absolute;top:20px;bottom:16px;left:31px;width:1px;background:' + hairlineSoft + ';"></span>'
                        + '<span style="position:absolute;top:20px;bottom:16px;right:24px;width:1px;background:' + hairline + ';"></span>'
                        + '<span style="position:absolute;top:20px;bottom:16px;right:31px;width:1px;background:' + hairlineSoft + ';"></span>';

                    const divider =
                        '<div style="display:flex;align-items:center;gap:12px;">'
                        + '<span style="flex:1;height:1px;background:' + hairline + ';"></span>'
                        + '<span style="color:' + accent + ';font-size:13px;line-height:1;">&#9986;</span>'
                        + '<span style="flex:1;height:1px;background:' + hairline + ';"></span>'
                        + '</div>';

                    frame.innerHTML = rails
                        + divider
                        + '<div style="text-align:center;padding:14px 0 0;">'
                        + '<h1 style="margin:0;font-family:Georgia,\'Times New Roman\',serif;font-size:22px;line-height:1.2;font-weight:700;letter-spacing:0.24em;text-transform:uppercase;color:' + ink + ';">' + shopName + '</h1>'
                        + '<p style="margin:7px 0 0;font-size:12px;letter-spacing:0.06em;color:' + muted + ';">Monthly schedule for ' + month + '</p>'
                        + '</div>'
                        + '<div data-grid style="position:relative;flex:1;min-height:0;margin-top:14px;"></div>'
                        + divider
                        + '<div style="text-align:center;padding-top:14px;font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:' + muted + ';">OBS \u2014 developed by JCC</div>';

                    const host = target.parentNode;
                    host.insertBefore(frame, target);
                    frame.querySelector('[data-grid]').appendChild(target);
                },
                });

                const link = document.createElement('a');
                link.download = shopName.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-schedule-' + month.replace(/[^a-z0-9]+/gi, '-').toLowerCase() + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                this.toast = 'Schedule downloaded.';
            } catch (e) {
                this.toast = 'Export failed \u2014 please try again.';
            } finally {
                setTimeout(() => { this.toast = ''; }, 3500);
            }
        },

        async downloadScheduleList() {
            if (!window.html2canvas) return;

            // A printed artifact, independent of the on-screen theme: warm
            // paper, charcoal ink, olive-lime accents, twin symmetric rails.
            const sheet = '#f8f6f1';
            const ink = '#26292f';
            const muted = '#7b8089';
            const hairline = '#e7e2d7';
            const hairlineSoft = 'rgba(38,41,47,0.05)';
            const accent = '#4d5d18';
            const danger = '#a04b3e';

            const shopName = '{{ \App\Models\BusinessSetting::get('shop_name', 'Barber Shop') }}';
            const monthMatch = (this.calendarTitle || '').match(/^([A-Za-z]+)\s+(\d{4})$/);
            const monthLabel = monthMatch ? monthMatch[1] : this.currentMonthLabel().split(' ')[0];
            const monthYear = monthMatch ? monthMatch[2] : new Date().getFullYear();
            const periodLabel = monthLabel.charAt(0).toUpperCase() + monthLabel.slice(1) + ' ' + monthYear;

            const byDay = {};
            (this.calendar?.getEvents() || []).forEach(e => {
                if (!(e.start instanceof Date) || isNaN(e.start)) return;
                const key = this.padDate(e.start);
                if (!byDay[key]) byDay[key] = [];
                const block = e.extendedProps?.blocked;
                byDay[key].push({
                    block: !!block,
                    time: block ? '' : this.formatTimeDate(e.start),
                    status: block ? '' : (e.extendedProps?.status || ''),
                    label: block ? (e.extendedProps?.reason || e.title || 'Barber on leave') : e.title,
                });
            });

            // Only print from today up to the last day of the displayed month.
            const todayKey = this.padDate(new Date());
            const viewDate = this.calendar?.getDate ? this.calendar.getDate() : new Date();
            const lastKey = this.padDate(new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 0));

            let listHtml = '';
            Object.keys(byDay).sort().filter(key => key >= todayKey && key <= lastKey).forEach(key => {
                const entries = byDay[key].sort((a, b) => (a.block ? 0 : 1) - (b.block ? 0 : 1) || a.time.localeCompare(b.time));
                const d = new Date(key + 'T00:00:00');
                const dayLabel = d.toLocaleString('en-US', { month: 'long', day: 'numeric' });
                const dayName = d.toLocaleString('en-US', { weekday: 'short' }).toUpperCase();

                listHtml += '<div style="margin-top:18px;">';
                listHtml += '<div style="display:flex;align-items:center;gap:12px;">'
                    + '<span style="font-size:12px;font-weight:700;color:' + ink + ';">' + dayLabel + '</span>'
                    + '<span style="flex:1;height:1px;background:' + hairline + ';"></span>'
                    + '<span style="font-size:9px;letter-spacing:0.18em;color:' + muted + ';">' + dayName + '</span></div>';
                entries.forEach(en => {
                    if (en.block) {
                        listHtml += '<div style="margin-top:10px;padding-left:64px;font-size:12.5px;font-style:italic;font-weight:600;color:' + danger + ';">' + en.label + '</div>';
                    } else {
                        listHtml += '<div style="margin-top:10px;display:flex;align-items:baseline;gap:12px;">'
                            + '<span style="font-family:ui-monospace,SFMono-Regular,monospace;font-size:11.5px;font-weight:600;color:' + accent + ';min-width:64px;">' + en.time + '</span>'
                            + '<span style="flex:1;font-size:13px;color:' + ink + ';">' + en.label + '</span>'
                            + (en.status ? '<span style="font-size:9px;letter-spacing:0.18em;color:' + muted + ';">' + en.status.toUpperCase() + '</span>' : '') + '</div>';
                    }
                });
                listHtml += '</div>';
            });

            const scissors = '<span style="color:' + accent + ';font-size:13px;line-height:1;">&#9986;</span>';
            const divider =
                '<div style="display:flex;align-items:center;gap:12px;margin:20px 0 2px;">'
                + '<span style="flex:1;height:1px;background:' + hairline + ';"></span>'
                + scissors
                + '<span style="flex:1;height:1px;background:' + hairline + ';"></span>'
                + '</div>';

            const rails =
                '<span style="position:absolute;top:24px;bottom:28px;left:22px;width:1px;background:' + hairline + ';"></span>'
                + '<span style="position:absolute;top:24px;bottom:28px;left:30px;width:1px;background:' + hairlineSoft + ';"></span>'
                + '<span style="position:absolute;top:24px;bottom:28px;right:22px;width:1px;background:' + hairline + ';"></span>'
                + '<span style="position:absolute;top:24px;bottom:28px;right:30px;width:1px;background:' + hairlineSoft + ';"></span>';

            const node = document.createElement('div');
            node.style.cssText = 'position:fixed;top:0;left:0;width:' + Math.min(430, window.innerWidth) + 'px;z-index:2147483000;';
            node.innerHTML =
                '<div style="position:relative;box-sizing:border-box;display:flex;flex-direction:column;min-height:560px;font-family:ui-sans-serif,system-ui,sans-serif;background:' + sheet + ';color:' + ink + ';padding:30px 44px 20px;border:1px solid #cfc8b8;border-radius:2px;box-shadow:0 20px 44px rgba(20,20,28,0.12);">'
                + rails
                + '<div style="text-align:center;padding-top:22px;border-top:1px solid ' + hairline + ';">'
                + '<h1 style="margin:0;font-family:Georgia,\'Times New Roman\',serif;font-size:29px;line-height:1.15;font-weight:700;letter-spacing:0.22em;text-transform:uppercase;color:' + ink + ';">' + shopName + '</h1>'
                + '<p style="margin:9px 0 0;font-size:12px;letter-spacing:0.06em;color:' + muted + ';">Monthly schedule for ' + periodLabel + '</p>'
                + '</div>'
                + divider
                + '<div style="flex:1;">'
                + (listHtml || '<p style="margin:26px 0 0;text-align:center;font-size:13px;font-style:italic;color:' + muted + ';">No bookings scheduled for this month.</p>')
                + '</div>'
                + divider
                + '<div style="text-align:center;padding-top:18px;font-size:10px;letter-spacing:0.2em;text-transform:uppercase;color:' + muted + ';">OBS \u2014 developed by JCC</div>'
                + '</div>';
            document.body.appendChild(node);

            try {
                this.toast = 'Preparing your schedule\u2026';
                const canvas = await window.html2canvas(node, { backgroundColor: sheet, scale: 3 });
                const link = document.createElement('a');
                link.download = shopName.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-schedule-' + monthLabel.toLowerCase() + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                this.toast = 'Schedule downloaded.';
            } catch (e) {
                this.toast = 'Export failed \u2014 please try again.';
            } finally {
                node.remove();
                setTimeout(() => { this.toast = ''; }, 3500);
            }
        },

        formatTimeDate(date) {
            if (!(date instanceof Date) || isNaN(date)) return '';
            let h = date.getHours();
            const m = String(date.getMinutes()).padStart(2, '0');
            const ap = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return h + ':' + m + ' ' + ap;
        },

        currentMonthLabel() {
            return new Date().toLocaleString('en-US', { month: 'long', year: 'numeric' });
        },

        async loadSlots() {
            if (!this.bookServiceId || !this.bookDate) return;
            const res = await fetch(`{{ route('api.availability.index') }}?date=${this.bookDate}&service_id=${this.bookServiceId}`);
            const data = await res.json();
            this.availableSlots = data.slots || [];
        },

        formatTime(time) {
            if (!time) return '';
            const [h, m] = time.split(':');
            const hour = parseInt(h);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const h12 = hour % 12 || 12;
            return `${h12}:${m} ${ampm}`;
        },

        formatMoney(centavos) {
            if (!centavos) return '₱0.00';
            return '₱' + (centavos / 100).toFixed(2);
        },
    }));
});
</script>
@endpush