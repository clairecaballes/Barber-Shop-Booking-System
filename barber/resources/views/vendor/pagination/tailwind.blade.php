@if ($paginator->hasPages())
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-xs text-muted">
            Page <span class="numeral">{{ $paginator->currentPage() }}</span> of
            <span class="numeral">{{ $paginator->lastPage() }}</span>
            <span class="text-muted/60">&middot;</span>
            <span class="numeral">{{ $paginator->total() }}</span> total
        </p>

        <nav role="navigation" aria-label="Pagination"
             class="chrome flex flex-wrap items-center gap-1 rounded-[0.75rem] border border-white/10 p-1">
            @if ($paginator->onFirstPage())
                <span class="flex h-8 w-8 items-center justify-center rounded-[0.55rem] text-rail-muted opacity-40" aria-hidden="true">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"
                   class="flex h-8 w-8 items-center justify-center rounded-[0.55rem] text-rail-muted transition-colors hover:bg-white/5 hover:text-rail-ink">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1 text-xs text-rail-muted" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="view-tab is-active flex min-w-8 items-center justify-center">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Go to page {{ $page }}"
                               class="view-tab flex min-w-8 items-center justify-center">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"
                   class="flex h-8 w-8 items-center justify-center rounded-[0.55rem] text-rail-muted transition-colors hover:bg-white/5 hover:text-rail-ink">
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </a>
            @else
                <span class="flex h-8 w-8 items-center justify-center rounded-[0.55rem] text-rail-muted opacity-40" aria-hidden="true">
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </span>
            @endif
        </nav>
    </div>
@endif