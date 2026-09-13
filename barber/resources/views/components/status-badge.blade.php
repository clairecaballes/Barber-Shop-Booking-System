@props(['status'])

@php
    $value = $status instanceof \App\Enums\BookingStatus ? $status->value : (string) $status;

    $meta = match ($value) {
        'pending' => ['label' => 'Pending', 'var' => '--st-pending'],
        'booked' => ['label' => 'Booked', 'var' => '--st-booked'],
        'completed' => ['label' => 'Completed', 'var' => '--st-completed'],
        'cancelled' => ['label' => 'Cancelled', 'var' => '--st-cancelled'],
        'no_show' => ['label' => 'No-show', 'var' => '--st-noshow'],
        default => ['label' => ucfirst(str_replace('_', ' ', $value)), 'var' => '--st-blocked'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-[0.6875rem] font-semibold text-muted']) }}>
    <span class="h-1.5 w-1.5 rounded-full"
          style="background-color: var({{ $meta['var'] }}); box-shadow: 0 0 8px var({{ $meta['var'] }});"></span>
    {{ $meta['label'] }}
</span>
