@extends('layouts.app')
@section('title', 'Business Settings')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">

    @if (session('status'))
        <x-alert>{{ session('status') }}</x-alert>
    @endif

    <x-panel title="Business information" subtitle="Shown across the desk, the calendar, and exported schedules." bodyClass="p-6">
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <x-field label="Shop name" for="shop_name" errorName="shop_name">
                <x-input id="shop_name" name="shop_name" value="{{ $settings['shop_name'] }}" required />
            </x-field>

            <x-field label="Currency symbol" for="currency" errorName="currency" class="w-32">
                <x-input id="currency" name="currency" value="{{ $settings['currency'] }}" required maxlength="10" />
            </x-field>

            <div class="flex justify-end border-t border-line pt-5">
                <x-btn variant="accent" type="submit">Save details</x-btn>
            </div>
        </form>
    </x-panel>

    <x-panel title="Working hours" subtitle="Leave a day blank to close it." bodyClass="p-6">
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="shop_name" value="{{ $settings['shop_name'] }}">
            <input type="hidden" name="currency" value="{{ $settings['currency'] }}">

            <div class="space-y-2">
                @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                    @php
                        $open = $settings['operating_hours'][$day]['open'] ?? '';
                        $close = $settings['operating_hours'][$day]['close'] ?? '';
                    @endphp

                    <div class="bento-sunken flex flex-wrap items-center gap-3 px-4 py-3">
                        <span class="w-24 shrink-0 text-sm font-medium capitalize text-ink">{{ $day }}</span>

                        <input type="time" name="operating_hours[{{ $day }}][open]" value="{{ $open }}"
                               aria-label="{{ ucfirst($day) }} opening time" class="field w-32">

                        <span class="text-sm text-muted">to</span>

                        <input type="time" name="operating_hours[{{ $day }}][close]" value="{{ $close }}"
                               aria-label="{{ ucfirst($day) }} closing time" class="field w-32">

                        @if (! $open || ! $close)
                            <span class="chip text-muted">Closed</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end border-t border-line pt-5">
                <x-btn variant="accent" type="submit">Save hours</x-btn>
            </div>
        </form>
    </x-panel>
</div>
@endsection
