@extends('layouts.app')
@section('title', 'Add Customer')

@section('content')
<div class="mx-auto max-w-lg">
    <x-panel title="New customer" subtitle="Only the name is required — you can fill the rest in later." bodyClass="p-6">
        <form method="POST" action="{{ route('customers.store') }}" class="space-y-5">
            @csrf

            <x-field label="Name" for="name" errorName="name">
                <x-input id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Marco Reyes" />
            </x-field>

            <x-field label="Messenger ID" for="messenger_id" hint="Used to match conversations to bookings." errorName="messenger_id">
                <x-input id="messenger_id" name="messenger_id" value="{{ old('messenger_id') }}" />
            </x-field>

            <x-field label="Phone" for="phone" errorName="phone">
                <x-input id="phone" name="phone" value="{{ old('phone') }}" />
            </x-field>

            <x-field label="Notes" for="notes">
                <x-textarea id="notes" name="notes" rows="2">{{ old('notes') }}</x-textarea>
            </x-field>

            <div class="flex justify-end gap-2 border-t border-line pt-5">
                <x-btn variant="ghost" :href="route('customers.index')">Cancel</x-btn>
                <x-btn variant="accent" type="submit">Create customer</x-btn>
            </div>
        </form>
    </x-panel>
</div>
@endsection
