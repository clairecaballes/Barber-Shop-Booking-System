@props(['label', 'value', 'icon' => null, 'accent' => 'amber'])

@php
    $accents = [
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
        'green' => 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400',
        'red' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400',
        'violet' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-400',
        'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    ];
    $iconClass = $accents[$accent] ?? $accents['amber'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    <div class="flex items-start justify-between">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $value }}</p>
        </div>
        @if ($icon)
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $iconClass }}">
                <x-icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
    </div>

    @isset($footer)
        <div class="mt-3 border-t border-slate-100 dark:border-slate-800 pt-2 text-xs text-slate-500 dark:text-slate-400">
            {{ $footer }}
        </div>
    @endisset
</div>