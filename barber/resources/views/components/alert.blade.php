@props(['tone' => 'success'])

@php
    $toneClass = match ($tone) {
        'danger' => 'border-red-500/30 bg-red-500/10 text-red-500',
        default => 'border-accent-line bg-accent-soft text-ink',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-tile border px-4 py-3 text-sm '.$toneClass]) }}>
    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-current"></span>
    <span>{{ $slot }}</span>
</div>
