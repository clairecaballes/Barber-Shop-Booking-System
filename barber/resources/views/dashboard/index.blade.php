@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $today = now()->toDateString();
    $daily = $metrics['last_seven_days'];
    $dailyMax = max(1, max(array_column($daily, 'value')));
    $dailyTotal = array_sum(array_column($daily, 'value'));
    $peak = collect($daily)->sortByDesc('value')->first();

    $allTime = $metrics['overall_sales'];
    $ladder = [
        ['label' => 'This week', 'value' => $metrics['week_sales']],
        ['label' => 'This month', 'value' => $metrics['month_sales']],
        ['label' => 'This year', 'value' => $metrics['year_sales']],
        ['label' => 'All time', 'value' => $allTime],
    ];
@endphp

<div class="space-y-6">

    {{-- Today at a glance: one lit hero, one chart, one ledger. --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">

        {{-- Hero tile: the one number that runs the shop. --}}
        <section class="bento bento-lit bento-raised bento-lift relative flex flex-col overflow-hidden p-6 lg:col-span-5">
            <div class="pointer-events-none absolute -right-12 -top-16 h-48 w-48 rounded-full"
                 style="background-image: radial-gradient(circle, var(--accent-glow), transparent 70%);"
                 aria-hidden="true"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-muted">Today's takings</p>
                    <p class="numeral mt-3 text-4xl font-semibold text-ink">{{ money($metrics['today_sales']) }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-[0.7rem] border border-accent-line text-accent"
                      style="background-image: linear-gradient(180deg, var(--accent-soft), transparent);">
                    <x-icon name="sales" class="h-5 w-5" />
                </span>
            </div>

            <dl class="relative mt-auto grid grid-cols-2 gap-3 pt-6">
                <div class="bento-sunken px-3.5 py-2.5">
                    <dt class="text-xs text-muted">Booked today</dt>
                    <dd class="numeral mt-1 text-lg font-semibold text-ink">{{ $metrics['today_bookings'] }}</dd>
                </div>
                <div class="bento-sunken px-3.5 py-2.5">
                    <dt class="text-xs text-muted">Cut and paid</dt>
                    <dd class="numeral mt-1 text-lg font-semibold text-ink">{{ $metrics['today_completed'] }}</dd>
                </div>
            </dl>
        </section>

        {{-- The shape of the week: seven bars, today's lit. --}}
        <x-panel class="bento-raised bento-lift bento-wash bento-accent-edge flex flex-col lg:col-span-4" bodyClass="flex flex-1 flex-col p-5"
                 title="Last 7 days"
                 :subtitle="$dailyTotal > 0 ? 'Best day '.$peak['label'].' · '.money($peak['value']) : 'No takings yet'">
            <p class="numeral text-2xl font-semibold text-ink">{{ money($dailyTotal) }}</p>

            <div class="mt-5 flex h-28 items-end gap-1.5" role="img"
                 aria-label="Daily takings for the last seven days, {{ money($dailyTotal) }} in total">
                @foreach ($daily as $day)
                    @php
                        $isToday = $day['date'] === $today;
                        $fill = $day['value'] > 0 ? max(6, (int) round($day['value'] / $dailyMax * 100)) : 2;
                    @endphp
                    <div @class(['bar', 'bar-today' => $isToday, 'flex-1']) style="height: {{ $fill }}%"
                         title="{{ $day['label'] }} · {{ money($day['value']) }}"></div>
                @endforeach
            </div>

            <div class="mt-2 flex gap-1.5">
                @foreach ($daily as $day)
                    <span @class([
                        'flex-1 text-center text-[0.625rem] font-semibold uppercase tracking-wide',
                        'text-accent' => $day['date'] === $today,
                        'text-muted' => $day['date'] !== $today,
                    ])>{{ mb_substr($day['label'], 0, 2) }}</span>
                @endforeach
            </div>
        </x-panel>

        {{-- Period totals as a ladder: each period measured against all time. --}}
        <x-panel class="bento-raised bento-lift bento-wash bento-accent-edge lg:col-span-3" title="Takings" subtitle="Measured against all time" bodyClass="p-5">
            <dl class="space-y-3.5">
                @foreach ($ladder as $row)
                    <div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-xs font-medium text-muted">{{ $row['label'] }}</dt>
                            <dd class="numeral text-sm font-semibold text-ink">{{ money($row['value']) }}</dd>
                        </div>
                        <div class="meter mt-1.5">
                            <span class="meter-fill"
                                  style="width: {{ $allTime > 0 ? max(2, (int) round($row['value'] / $allTime * 100)) : 0 }}%"></span>
                        </div>
                    </div>
                @endforeach
            </dl>
        </x-panel>
    </div>

    {{-- Today's work --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">

        <x-panel title="Today's chair" :subtitle="now()->format('l, F j')" bodyClass="p-2" class="bento-raised bento-lift lg:col-span-5">
            <div class="divide-y divide-line">
                @forelse ($schedule['today'] as $booking)
                    <div class="flex items-center gap-3 rounded-[0.75rem] px-3 py-2.5 transition-colors hover:bg-accent-soft">
                        <span class="numeral w-16 shrink-0 text-xs font-semibold text-muted">
                            {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-ink">{{ $booking->customer->name ?? 'Walk-in' }}</p>
                            <p class="truncate text-xs text-muted">{{ $booking->service->name ?? 'N/A' }}</p>
                        </div>
                        <x-status-badge :status="$booking->status" />
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-muted">Nothing booked for today yet.</p>
                @endforelse
            </div>
        </x-panel>

        <x-panel title="Coming up" bodyClass="p-2" class="bento-raised bento-lift lg:col-span-4">
            <div class="space-y-1">
                @forelse ($schedule['upcoming'] as $booking)
                    <div class="flex items-center justify-between gap-3 rounded-[0.75rem] px-3 py-2.5 transition-colors hover:bg-accent-soft">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-ink">{{ $booking->customer->name ?? 'Walk-in' }}</p>
                            <p class="numeral truncate text-xs text-muted">
                                {{ $booking->appointment_date->format('M j') }} · {{ \Carbon\Carbon::parse($booking->appointment_time)->format('g:i A') }}
                            </p>
                        </div>
                        <span class="numeral shrink-0 text-xs font-semibold text-ink">{{ money($booking->price) }}</span>
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-muted">The chair is clear — no upcoming bookings.</p>
                @endforelse
            </div>
        </x-panel>

        <x-panel title="Just booked" bodyClass="p-2" class="bento-raised bento-lift lg:col-span-3">
            <div class="space-y-1">
                @forelse ($schedule['recent'] as $booking)
                    <div class="rounded-[0.75rem] px-3 py-2.5 transition-colors hover:bg-accent-soft">
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-sm font-medium text-ink">{{ $booking->customer->name ?? 'Walk-in' }}</p>
                            <x-status-badge :status="$booking->status" />
                        </div>
                        <p class="mt-1 text-xs text-muted">{{ $booking->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-sm text-muted">No bookings on record yet.</p>
                @endforelse
            </div>
        </x-panel>
    </div>
</div>
@endsection
