@props(['title' => null, 'subtitle' => null, 'tone' => 'default', 'bodyClass' => 'p-5'])

<section {{ $attributes->merge(['class' => 'bento overflow-hidden'.($tone === 'lit' ? ' bento-lit' : '')]) }}>
    @if ($title || isset($actions))
        <header class="flex flex-wrap items-start justify-between gap-4 border-b border-line px-5 py-4">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-sm font-semibold text-ink">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="mt-1 text-xs text-muted">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    <div class="{{ $bodyClass }}">{{ $slot }}</div>

    @isset($footer)
        <div class="border-t border-line px-5 py-3">{{ $footer }}</div>
    @endisset
</section>
