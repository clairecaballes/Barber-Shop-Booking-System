@extends('layouts.app')
@section('title', 'Add Service')

@section('content')
<div class="mx-auto max-w-lg">
    <x-panel title="New service" subtitle="Duration sets how long the slot is held on the calendar." bodyClass="p-6">
        <form method="POST" action="{{ route('services.store') }}" class="space-y-5">
            @csrf

            <x-field label="Name" for="name" errorName="name">
                <x-input id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Skin fade" />
            </x-field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Price (₱)" for="price" errorName="price">
                    <x-input id="price" type="number" name="price" value="{{ old('price', 150) }}" min="0" step="1" required />
                </x-field>

                <x-field label="Duration (minutes)" for="duration" errorName="duration">
                    <x-input id="duration" type="number" name="duration" value="{{ old('duration', 30) }}" min="15" max="240" step="15" required />
                </x-field>
            </div>

            <label class="flex items-center gap-2.5 text-sm text-ink">
                <input type="checkbox" name="active" value="1" class="checkbox h-4 w-4 rounded border-line" @checked(old('active', '1'))>
                Bookable straight away
            </label>

            <div class="flex justify-end gap-2 border-t border-line pt-5">
                <x-btn variant="ghost" :href="route('services.index')">Cancel</x-btn>
                <x-btn variant="accent" type="submit">Create service</x-btn>
            </div>
        </form>
    </x-panel>
</div>
@endsection
