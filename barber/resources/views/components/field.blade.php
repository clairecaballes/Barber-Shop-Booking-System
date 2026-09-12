@props(['label' => null, 'for' => null, 'hint' => null, 'errorName' => null])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label @if ($for) for="{{ $for }}" @endif class="label">{{ $label }}</label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="text-xs text-muted">{{ $hint }}</p>
    @endif

    @if ($errorName && $errors->has($errorName))
        <p class="text-xs font-medium text-red-500">{{ $errors->first($errorName) }}</p>
    @endif
</div>
