@props(['label', 'value', 'icon' => null, 'tone' => 'default', 'hint' => null])

@php
    $lit = $tone === 'lit';
@endphp

<div {{ $attributes->merge(['class' => 'bento bento-hover p-5'.($lit ? ' bento-lit' : '')]) }}>
    <div class="flex items-start justify-between gap-4">
        <p class="text-xs font-semibold tracking-wide text-muted">{{ $label }}</p>

        @if ($icon)
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.65rem] border {{ $lit ? 'border-accent-line text-accent' : 'border-line text-muted' }}"
                  style="{{ $lit
                      ? 'background-image: linear-gradient(180deg, var(--accent-soft), transparent);'
                      : 'background-image: linear-gradient(180deg, rgba(255,255,255,0.05), transparent);' }}">
                <x-icon :name="$icon" class="h-4 w-4" />
            </span>
        @endif
    </div>

    <p class="numeral mt-4 text-2xl font-semibold {{ $lit ? 'text-ink' : 'text-ink' }}">{{ $value }}</p>

    @if ($hint)
        <p class="mt-1 text-xs text-muted">{{ $hint }}</p>
    @endif

    @isset($footer)
        <div class="mt-4 border-t border-line pt-2.5 text-xs text-muted">
            {{ $footer }}
        </div>
    @endisset
</div>
