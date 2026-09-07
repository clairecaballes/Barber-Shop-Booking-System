@extends('layouts.app')
@section('title', 'Calendar')

@section('content')
<div x-data="calendarApp()" x-init="init()">
    {{-- Success toast --}}
    <div x-show="toast" x-transition.opacity.duration.300ms x-cloak class="fixed left-1/2 top-4 z-[60] -translate-x-1/2 rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg" style="display:none;">
        <span x-text="toast"></span>
    </div>

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button @click="calendar.prev()" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800/50">
                &larr; Prev
            </button>
            <h2 x-text="calendarTitle" class="text-lg font-semibold text-slate-900 dark:text-white"></h2>
            <button @click="calendar.next()" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800/50">
                Next &rarr;
            </button>
            <button @click="calendar.today()" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800/50">
                Today
            </button>
        </div>
        <div class="flex items-center gap-2">
            <button @click="changeView('dayGridMonth')" :class="currentView === 'dayGridMonth' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 dark:text-white' : 'bg-white text-slate-700 dark:text-slate-200 dark:bg-slate-900 dark:text-slate-200'" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800/50">Month</button>
            <button @click="changeView('timeGridWeek')" :class="currentView === 'timeGridWeek' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 dark:text-white' : 'bg-white text-slate-700 dark:text-slate-200 dark:bg-slate-900 dark:text-slate-200'" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800/50">Week</button>
            <button @click="changeView('timeGridDay')" :class="currentView === 'timeGridDay' ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 dark:text-white' : 'bg-white text-slate-700 dark:text-slate-200 dark:bg-slate-900 dark:text-slate-200'" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800/50">Day</button>
            <button @click="downloadCalendar()" class="ml-2 flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0 0l-4-4m4 4l4-4"/></svg>
                Download
            </button>
        </div>
    </div>

    {{-- Legend --}}
    <div class="mb-4 flex flex-wrap gap-4 text-xs text-slate-600 dark:text-slate-300">
        <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-yellow-500"></span> Pending</span>
        <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-blue-500"></span> Booked</span>
        <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-green-500"></span> Completed</span>
        <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-red-500"></span> Cancelled</span>
        <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-full bg-orange-500"></span> No-show</span>
    </div>

    {{-- Calendar container --}}
    <div id="calendar-wrapper" class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-4 shadow-sm">
        <div id="calendar"></div>
    </div>

    {{-- Booking detail modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" style="display:none;">
        <div @click.outside="showModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white" x-text="selectedBooking?.title"></h3>
            <div class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <p><span class="font-medium text-slate-900 dark:text-white">Customer:</span> <span x-text="selectedBooking?.extendedProps?.customerName"></span></p>
                <p><span class="font-medium text-slate-900 dark:text-white">Service:</span> <span x-text="selectedBooking?.extendedProps?.serviceName"></span></p>
                <p><span class="font-medium text-slate-900 dark:text-white">Status:</span> <span x-text="selectedBooking?.extendedProps?.status"></span></p>
                <p><span class="font-medium text-slate-900 dark:text-white">Price:</span> <span x-text="formatMoney(selectedBooking?.extendedProps?.price)"></span></p>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a :href="'/bookings/' + selectedBooking?.id" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50">View</a>
                <a :href="'/bookings/' + selectedBooking?.id + '/edit'" class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:hover:bg-white">Edit</a>
            </div>
        </div>
    </div>

    {{-- Quick book modal --}}
    <div x-show="showBookModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" style="display:none;">
        <div @click.outside="showBookModal = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Quick Book</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400" x-text="bookDate"></p>
            <form @submit.prevent="submitQuickBook()" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="appointment_date" :value="bookDate">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Customer</label>
                    <select name="customer_id" x-model="customerId" @change="customerSelected" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                        <option value="">Select existing customer...</option>
                        @foreach (\App\Models\Customer::orderBy('name')->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Or new customer name</label>
                    <input type="text" name="customer_name" x-model="customerName" @input="typedCustomer()" placeholder="Type a new customer name" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white placeholder-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Service</label>
                    <select name="service_id" required x-model="bookServiceId" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                        @foreach (\App\Models\Service::where('active', true)->orderBy('name')->get() as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} — {{ money($s->price) }} ({{ $s->duration }}min)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Time</label>
                    <input type="time" name="appointment_time" x-model="bookTime" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    <p class="mt-1 text-xs text-slate-400">Pick a time; overlapping bookings are rejected.</p>
                </div>
                <p x-show="quickError" x-text="quickError" class="text-xs text-red-600"></p>
                <div class="flex flex-wrap justify-end gap-3">
                    <button type="button" @click="showBookModal = false" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50">Cancel</button>
                    <button type="button" @click="submitBlock()" :disabled="saving" class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 disabled:opacity-60">
                        <span x-show="!saving">Block schedule</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                    <button type="submit" :disabled="saving" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600 disabled:opacity-60">
                        <span x-show="!saving">Add to Calendar</span>
                        <span x-show="saving">Saving...</span>
                    </button>
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
        showBookModal: false,
        bookDate: '',
        bookServiceId: '',
        availableSlots: [],
        customerId: '',
        customerName: '',
        bookTime: '',
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
                },
            });

            this.calendar.render();
        },

        changeView(view) {
            this.calendar.changeView(view);
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
            const el = document.getElementById('calendar-wrapper');
            if (!el || !window.html2canvas) return;

            const shopName = '{{ \App\Models\BusinessSetting::get('shop_name', 'Barber Shop') }}';
            const month = this.calendarTitle || this.currentMonthLabel();

            const header = document.createElement('div');
            header.style.cssText = 'text-align:center;padding:16px 8px 12px;font-family:sans-serif;';
            header.innerHTML =
                '<h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0;">' + shopName + ' — Schedule</h1>' +
                '<p style="font-size:15px;color:#64748b;margin:6px 0 0;">' + month + '</p>';
            el.prepend(header);

            try {
                const canvas = await window.html2canvas(el, { backgroundColor: '#ffffff', scale: 2 });
                const link = document.createElement('a');
                link.download = shopName.toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-schedule-' + month.replace(/[^a-z0-9]+/gi, '-').toLowerCase() + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            } finally {
                header.remove();
            }
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
