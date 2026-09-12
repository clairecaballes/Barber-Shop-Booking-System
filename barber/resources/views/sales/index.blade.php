@extends('layouts.app')
@section('title', 'Sales & Financial Analysis')

@section('content')
<div class="space-y-6">

    @if (session('status'))
        <x-alert>{{ session('status') }}</x-alert>
    @endif

    @php
        // One ledger, one headline figure — the rest is reference detail.
        $ledger = [
            'Takings' => [
                'Today' => money($metrics['today_sales']),
                'This week' => money($metrics['week_sales']),
                'This month' => money($metrics['month_sales']),
                'This year' => money($metrics['year_sales']),
                'All time' => money($metrics['overall_sales']),
            ],
            'Costs and averages' => [
                'Item costs, all time' => money($metrics['expenses_overall']),
                'Haircuts completed' => $metrics['completed_count'],
                'Average day' => money($metrics['avg_daily']),
                'Average month' => money($metrics['avg_monthly']),
            ],
            'Patterns' => [
                'Busiest day' => $metrics['busiest_day'] ?? 'Not enough data',
                'Busiest time' => $metrics['busiest_time'] ?? 'Not enough data',
                'Cancellations' => $metrics['cancellation_count'],
                'No-shows' => $metrics['no_show_count'],
            ],
        ];
    @endphp

    {{-- The one card: net take on top, the rest as a ledger spread below. --}}
    <section class="bento bento-lit bento-raised relative overflow-hidden">
        <div class="pointer-events-none absolute -left-16 -top-24 h-56 w-56 rounded-full"
             style="background-image: radial-gradient(circle, var(--accent-glow), transparent 70%);"
             aria-hidden="true"></div>

        <div class="relative flex flex-wrap items-end justify-between gap-6 p-6">
            <div>
                <p class="text-xs font-semibold text-muted">Net take, all time</p>
                <p class="numeral mt-3 text-4xl font-semibold text-ink">{{ money($metrics['net_sales']) }}</p>
                <p class="numeral mt-2 text-xs text-muted">
                    {{ money($metrics['overall_sales']) }} taken &minus; {{ money($metrics['expenses_overall']) }} in item costs
                </p>
            </div>

            <p class="numeral text-xs text-muted">
                {{ money($metrics['today_sales']) }} today · {{ $metrics['completed_count'] }} haircuts on record
            </p>
        </div>

        <div class="relative grid grid-cols-1 divide-y divide-line border-t border-line lg:grid-cols-3 lg:divide-x lg:divide-y-0">
            @foreach ($ledger as $group => $rows)
                <div class="p-6">
                    <p class="text-xs text-muted">{{ $group }}</p>

                    <dl class="mt-3 divide-y divide-line">
                        @foreach ($rows as $label => $value)
                            <div class="flex items-baseline justify-between gap-4 py-2">
                                <dt class="text-sm text-muted">{{ $label }}</dt>
                                <dd class="numeral shrink-0 text-sm font-semibold text-ink">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Monthly schedule export --}}
    <x-panel title="Monthly schedule" subtitle="Exports a dark schedule sheet you can send straight to the shop phone."
             x-data="{ month: '{{ now()->format('Y-m') }}' }" bodyClass="p-6">
        <x-slot:actions>
            <x-field label="Month">
                <x-input type="month" x-model="month" x-on:change="month = $event.target.value" />
            </x-field>
            <x-btn variant="accent" x-on:click="downloadSchedule(month)" class="self-end">
                <x-icon name="download" class="h-4 w-4" />
                Download image
            </x-btn>
        </x-slot:actions>

        <div id="monthly-schedule" class="bento-sunken p-5 text-sm text-muted">
            Pick a month and export the schedule.
        </div>
    </x-panel>
</div>

@push('scripts')
<script>
async function buildSchedule(month) {
    const [y, m] = month.split('-');
    const start = y + '-' + m + '-01';
    const lastDay = new Date(y, parseInt(m), 0).getDate();
    const end = y + '-' + m + '-' + lastDay;

    const res = await fetch('/api/calendar/events?start=' + start + '&end=' + end);
    if (!res.ok) return null;
    const events = await res.json();

    const grouped = {};
    events.forEach(e => {
        const date = e.start.split('T')[0];
        if (!grouped[date]) grouped[date] = [];
        grouped[date].push(e);
    });

    const ink = '#f4f4f5';
    const muted = '#a1a1aa';
    const lime = '#ccff00';

    let html = '<div style="font-family:ui-sans-serif,system-ui,sans-serif;background:#121212;color:' + ink + ';padding:24px;border-radius:20px;">';
    html += '<h3 style="margin:0 0 4px;font-size:20px;font-weight:600;letter-spacing:-0.01em;">Monthly schedule</h3>';
    html += '<p style="margin:0 0 18px;font-size:13px;color:' + muted + ';">' + month + '</p>';
    for (let d = 1; d <= lastDay; d++) {
        const dateStr = y + '-' + m + ('0' + d).slice(-2);
        const dayEvents = grouped[dateStr] || [];
        html += '<div style="padding:8px 0;border-top:1px solid rgba(255,255,255,0.08);">';
        html += '<div style="font-size:12px;color:' + muted + ';">' + dateStr + ' · ' + dayEvents.length + (dayEvents.length === 1 ? ' booking' : ' bookings') + '</div>';
        if (dayEvents.length) {
            dayEvents.forEach(e => {
                html += '<div style="margin-top:4px;font-size:13px;"><span style="font-family:ui-monospace,monospace;color:' + lime + ';">'
                    + (e.start.split('T')[1] || '').slice(0, 5) + '</span> ' + e.title + '</div>';
            });
        } else {
            html += '<div style="margin-top:4px;font-size:13px;color:' + muted + ';">Chair empty</div>';
        }
        html += '</div>';
    }
    html += '</div>';
    return html;
}

async function downloadSchedule(month) {
    const html = await buildSchedule(month);
    if (!html) return;
    const el = document.getElementById('monthly-schedule');
    el.innerHTML = html;
    const canvas = await window.html2canvas(el, { backgroundColor: '#121212', scale: 2 });
    const link = document.createElement('a');
    link.download = 'monthly-schedule-' + month + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}
</script>
@endpush
@endsection
