@extends('layouts.app')
@section('title', 'Account')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">

    @if (session('status'))
        <x-alert>{{ session('status') }}</x-alert>
    @endif

    <x-panel title="Profile" subtitle="The name and email you sign in with." bodyClass="p-6">
        <form method="POST" action="{{ route('account.update') }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <x-field label="Name" for="name" errorName="name">
                <x-input id="name" name="name" value="{{ old('name', $user->name) }}" required />
            </x-field>

            <x-field label="Email" for="email" errorName="email">
                <x-input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required />
            </x-field>

            <div class="flex justify-end border-t border-line pt-5">
                <x-btn variant="accent" type="submit">Save profile</x-btn>
            </div>
        </form>
    </x-panel>

    <x-panel title="Password" subtitle="Changing it signs out any other session you left open." bodyClass="p-6">
        <form method="POST" action="{{ route('account.password') }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <x-field label="Current password" for="current_password" errorName="current_password">
                <x-input id="current_password" type="password" name="current_password" required autocomplete="current-password" />
            </x-field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="New password" for="password" errorName="password">
                    <x-input id="password" type="password" name="password" required autocomplete="new-password" />
                </x-field>

                <x-field label="Confirm new password" for="password_confirmation">
                    <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                </x-field>
            </div>

            <div class="flex justify-end border-t border-line pt-5">
                <x-btn variant="accent" type="submit">Update password</x-btn>
            </div>
        </form>
    </x-panel>
</div>
@endsection
