@extends('layouts.app')
@section('title', 'Account')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">

        {{-- Status message --}}
        @if (session('status'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">
                {{ session('status') }}
            </div>
        @endif

        {{-- Profile --}}
        <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Profile</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Update your name and email address.</p>
            </div>

            <form method="POST" action="{{ route('account.update') }}" class="space-y-5 px-6 py-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                        Save
                    </button>
                </div>
            </form>
        </section>

        {{-- Password --}}
        <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
            <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-4">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Change Password</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Ensure your account stays secure.</p>
            </div>

            <form method="POST" action="{{ route('account.password') }}" class="space-y-5 px-6 py-5" x-data="{}">
                @csrf
                @method('PATCH')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Current password</label>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           required
                           autocomplete="current-password"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">New password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Confirm new password</label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                        Update password
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection