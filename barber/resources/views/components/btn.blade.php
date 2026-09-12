@props(['variant' => 'metal', 'href' => null, 'type' => 'button'])

@php
    $variantClass = match ($variant) {
        'accent' => 'btn-accent',
        'ghost' => 'btn-ghost',
        'danger' => 'btn-danger',
        default => 'btn-metal',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn '.$variantClass]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn '.$variantClass]) }}>{{ $slot }}</button>
@endif
