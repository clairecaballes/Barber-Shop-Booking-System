@extends('layouts.app')
@section('title', 'Calendar')

@section('content')
<div x-data="calendarApp()" x-init="init()">

    @if (session('status'))
        <div class="mb-4"><x-alert>{{ session('status') }}</x-alert></div>
    @endif

    {{-- Toast --}}
    <div x-show="toast" x-transition.opacity.duration.300ms x-cloak
         class="fixed left-1/2 top-4 z-[60] -translate-x-1/2 rounded-[0.75rem] px-5 py-3 text-sm font-semibold shadow-lg"
         style="display:none; background-color: var(--accent); color: var(--accent-ink);">
        <span x-text="toast"></span>
    </div>

    {{-- Controls --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <label for="calendar-month-picker" class="sr-only">Choose month</label>
            <input id="calendar-month-picker" type="month" x-model="monthPicker" @change="changeMonth($event.target.value)"
                   class="calendar-month-control">

            <x-btn variant="ghost" @click="calendar.today()" class="ml-1">Today</x-btn>
        </div>

        <div class="flex items-center gap-2">
            <div class="chrome flex items-center gap-1 rounded-[0.75rem] border border-white/10 p-1">
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

    {{-- Day key --}}
    <div class="mb-4 flex flex-wrap items-center gap-4 text-xs text-muted">
        @foreach (['pending' => 'Pending', 'booked' => 'Booked', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No-shows'] as $key => $label)
            <span class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full" style="background-color: var(--st-{{ $key === 'no_show' ? 'noshow' : $key }});"></span>
                {{ $label }}
            </span>
        @endforeach
        <span class="flex items-center gap-1.5">
            <span class="h-2 w-2 rounded-full" style="background-color: var(--st-blocked);"></span>
            Blocked
        </span>
    </div>

    {{-- Board --}}
    <x-panel bodyClass="p-4">
        <div id="calendar-wrapper" class="h-[calc(100svh-15rem)] lg:h-[42rem]">
            <div id="calendar"></div>
        </div>
    </x-panel>

    {{-- Booking detail --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showModal = false"></div>

        <div class="bento relative w-full max-w-md p-6">
            <div class="flex items-start justify-between gap-4">
                <h3 class="text-base font-semibold text-ink" x-text="selectedBooking?.title"></h3>
                <button type="button" @click="showModal = false" aria-label="Close booking details"
                        class="flex h-8 w-8 items-center justify-center rounded-[0.65rem] border border-line text-muted transition-colors hover:border-accent-line hover:text-ink">
                    <x-icon name="close" class="h-4 w-4" />
                </button>
            </div>

            <dl class="mt-5 space-y-3 text-sm">
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
                    <dd class="font-medium text-ink" x-text="selectedBooking?.extendedProps?.status"></dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-muted">Price</dt>
                    <dd class="numeral font-semibold text-ink" x-text="formatMoney(selectedBooking?.extendedProps?.price)"></dd>
                </div>
            </dl>

            <div class="mt-6 flex justify-end gap-2 border-t border-line pt-5">
                <x-btn variant="ghost" @click="openBooking()">Open booking</x-btn>
            </div>
        </div>
    </div>

    {{-- Reschedule --}}
    <div x-show="showRescheduleModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
         @keydown.escape.window="closeReschedule()">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="closeReschedule()"></div>

        <div class="bento relative w-full max-w-md p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-ink">Reschedule booking</h3>
                    <p class="mt-1 text-sm text-muted" x-text="selectedBooking?.extendedProps?.customerName"></p>
                </div>
                <button type="button" @click="closeReschedule()" aria-label="Close reschedule"
                        class="flex h-8 w-8 items-center justify-center rounded-[0.65rem] border border-line text-muted transition-colors hover:border-accent-line hover:text-ink">
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
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showBookModal = false"></div>

        <div class="bento relative w-full max-w-md p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-ink">Quick book</h3>
                    <p class="numeral mt-1 text-xs text-muted" x-text="bookDate"></p>
                </div>
                <button type="button" @click="showBookModal = false" aria-label="Close"
                        class="text-muted transition-colors hover:text-ink">
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

                <div class="grid grid-cols-3 gap-2 border-t border-line pt-5 sm:flex sm:justify-end sm:gap-2">
                    <x-btn variant="ghost" type="button" @click="showBookModal = false" class="w-full px-0 text-center">Cancel</x-btn>
                    <x-btn variant="danger" type="button" @click="submitBlock()" x-bind:disabled="saving" class="w-full px-0 text-center">
                        <span x-show="!saving">
                            <span class="sm:hidden">Block</span>
                            <span class="hidden sm:inline">Block the day</span>
                        </span>
                        <span x-show="saving">Saving…</span>
                    </x-btn>
                    <x-btn variant="accent" type="submit" x-bind:disabled="saving" class="w-full px-0 text-center">
                        <span x-show="!saving">
                            <span class="sm:hidden">Add</span>
                            <span class="hidden sm:inline">Add to calendar</span>
                        </span>
                        <span x-show="saving">Saving…</span>
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
        monthPicker: '',
        saving: false,
        quickError: '',
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
                    self.bookDate = info.dateStr.split('T')[0];
                    self.customerId = '';
                    self.customerName = '';
                    self.bookTime = '';
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
            this.quickError = '';
            if (!this.bookDate) return;

            this.saving = true;
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('date', this.bookDate);

                const res = await fetch('{{ route('calendar.blocks.store') }}', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData,
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    this.quickError = data.message || 'Could not block this day.';
                    return;
                }

                this.showBookModal = false;
                this.toast = data.message || 'Schedule blocked.';
                this.calendar.refetchEvents();
                setTimeout(() => { this.toast = ''; }, 3500);
            } catch (e) {
                this.quickError = 'Something went wrong. Please try again.';
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
            const sheet = dark ? '#121212' : '#f4f4f5';
            const ink = dark ? '#f4f4f5' : '#18181b';
            const muted = dark ? '#a1a1aa' : '#52525b';

            const shopName = '{{ \App\Models\BusinessSetting::get('shop_name', 'Barber Shop') }}';
            const month = this.calendarTitle || this.currentMonthLabel();

            const header = document.createElement('div');
            header.style.cssText = 'text-align:center;padding:16px 8px 12px;font-family:ui-sans-serif,system-ui,sans-serif;';
            header.innerHTML =
                '<h1 style="font-size:20px;font-weight:600;color:' + ink + ';margin:0;letter-spacing:-0.01em;">' + shopName + ' — schedule</h1>' +
                '<p style="font-size:14px;color:' + muted + ';margin:6px 0 0;">' + month + '</p>';
            el.prepend(header);

            try {
                const canvas = await window.html2canvas(el, { backgroundColor: sheet, scale: 2 });
                const link = document.createElement('a');
                link.download = shopName.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-schedule-' + month.replace(/[^a-z0-9]+/gi, '-').toLowerCase() + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            } finally {
                header.remove();
            }
        },

        async downloadScheduleList() {
            if (!window.html2canvas) return;

            const dark = document.documentElement.classList.contains('dark');
            const sheet = dark ? '#121212' : '#f4f4f5';
            const ink = dark ? '#f4f4f5' : '#18181b';
            const muted = dark ? '#a1a1aa' : '#52525b';
            const line = dark ? '#2e2e33' : '#dcdcdf';
            const accent = dark ? '#ccff00' : '#4d7c0f';
            const danger = dark ? '#ff6b6b' : '#b91c1c';

            const shopName = '{{ \App\Models\BusinessSetting::get('shop_name', 'Barber Shop') }}';
            const monthMatch = (this.calendarTitle || '').match(/^([A-Za-z]+)\s+\d{4}$/);
            const monthLabel = monthMatch ? monthMatch[1] : this.currentMonthLabel().split(' ')[0];
            const monthTitle = monthLabel.charAt(0).toUpperCase() + monthLabel.slice(1) + ' Schedule';

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

            let listHtml = '';
            Object.keys(byDay).sort().forEach(key => {
                const entries = byDay[key].sort((a, b) => (a.block ? 0 : 1) - (b.block ? 0 : 1) || a.time.localeCompare(b.time));
                const d = new Date(key + 'T00:00:00');
                const dayLabel = d.toLocaleString('en-US', { month: 'long', day: 'numeric' });
                const dayName = d.toLocaleString('en-US', { weekday: 'short' });

                listHtml += '<div style="margin-top:18px;">';
                listHtml += '<div style="display:flex;align-items:baseline;justify-content:space-between;gap:8px;">'
                    + '<h3 style="margin:0;font-size:14px;font-weight:600;color:' + ink + ';">' + dayLabel + '</h3>'
                    + '<span style="font-size:11px;color:' + muted + ';">' + dayName + '</span></div>';
                entries.forEach(en => {
                    if (en.block) {
                        listHtml += '<div style="margin-top:6px;font-size:13px;font-weight:700;color:' + danger + ';">' + en.label + '</div>';
                    } else {
                        listHtml += '<div style="margin-top:6px;display:flex;align-items:baseline;gap:8px;">'
                            + '<span style="font-family:ui-monospace,monospace;font-size:12px;color:' + accent + ';min-width:70px;">' + en.time + '</span>'
                            + '<span style="font-size:13px;color:' + ink + ';">' + en.label + '</span>'
                            + '<span style="margin-left:auto;font-size:11px;color:' + muted + ';">' + en.status + '</span></div>';
                    }
                });
                listHtml += '</div>';
            });

            const node = document.createElement('div');
            node.style.cssText = 'position:fixed;left:-9999px;top:0;width:' + Math.min(430, window.innerWidth) + 'px;z-index:-1;';
            node.innerHTML =
                '<div style="font-family:Georgia,\'Times New Roman\',serif;background:' + sheet + ';color:' + ink + ';padding:26px 22px 20px;border-radius:24px;border:1px solid ' + line + ';box-shadow:0 18px 40px rgba(15,23,42,0.08);">'
                + '<div style="text-align:center;padding-bottom:12px;border-bottom:1px solid ' + line + ';">'
                + '<h1 style="margin:0;font-size:30px;line-height:1.1;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;color:#4d7c0f;">' + shopName + '</h1>'
                + '<p style="margin:8px 0 0;font-size:12px;letter-spacing:0.26em;text-transform:uppercase;color:#4d7c0f;font-weight:700;">care in every cut.</p>'
                + '</div>'
                + '<h2 style="margin:18px 0 14px;text-align:center;font-size:26px;line-height:1.15;font-weight:700;letter-spacing:-0.03em;color:' + ink + ';font-family:ui-sans-serif,system-ui,sans-serif;">' + monthTitle + '</h2>'
                + '<div style="margin-top:10px;padding-top:10px;border-top:1px solid ' + line + ';font-family:ui-sans-serif,system-ui,sans-serif;">'
                + (listHtml || '<p style="margin:0;font-size:13px;color:' + muted + ';">No bookings scheduled for this month.</p>')
                + '</div>'
                + '</div>';
            document.body.appendChild(node);

            try {
                const canvas = await window.html2canvas(node, { backgroundColor: sheet, scale: 2 });
                const link = document.createElement('a');
                link.download = shopName.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-schedule-' + monthLabel.toLowerCase() + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            } finally {
                node.remove();
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
