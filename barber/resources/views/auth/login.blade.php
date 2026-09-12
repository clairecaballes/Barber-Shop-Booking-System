@php
    $shopName = \App\Models\BusinessSetting::get('shop_name') ?? 'Barber Shop';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in · {{ $shopName }}</title>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-start justify-center bg-page px-4 py-6 font-sans text-ink antialiased sm:items-center sm:py-10">

<div class="bento w-full max-w-4xl overflow-hidden lg:grid lg:grid-cols-2">

    {{-- The sign: a long chrome door with the spinning pole. On phones it fills
         the whole first screen; the desk form waits below, one scroll away. --}}
    <div class="chrome chrome-sheen relative flex min-h-[100svh] flex-col justify-between gap-5 overflow-hidden p-6 sm:min-h-0 sm:gap-0 sm:p-8 lg:gap-0">
        <div class="flex items-center justify-between gap-4 lg:block">
            <div class="relative">
                <p class="text-[11px] text-rail-muted">Shop desk</p>
                <h1 class="mt-1 text-xl font-semibold tracking-tight text-rail-ink sm:text-2xl">{{ $shopName }}</h1>
            </div>
        </div>

        <div class="relative flex flex-1 items-center justify-center py-2 sm:flex-none sm:py-6 lg:flex-1 lg:py-10">
            <div class="pointer-events-none absolute bottom-8 h-28 w-44 rounded-full"
                 style="background-image: radial-gradient(ellipse, var(--rail-accent-glow), transparent 70%);"
                 aria-hidden="true"></div>

            <span class="flex flex-col items-center" aria-hidden="true">
                <span class="pole-cap h-3 w-16 sm:h-2.5 sm:w-14"></span>
                <span class="pole pole-anim my-2 h-[42vh] w-16 sm:h-48 sm:w-14"></span>
                <span class="pole-cap h-3 w-16 sm:h-2.5 sm:w-14"></span>
            </span>
        </div>

        <p class="relative max-w-[22rem] text-[13px] leading-relaxed text-rail-muted sm:text-sm">
            Schedules, walk-ins, and the day's takings, all on one desk.
        </p>
    </div>

    {{-- Sign in --}}
    <div class="p-6 sm:p-10">
        <h2 class="text-lg font-semibold text-ink">Sign in</h2>
        <p class="mt-1 text-sm text-muted">Open the desk to see today's chair.</p>

        @if (session('status'))
            <div class="mt-6">
                <x-alert>{{ session('status') }}</x-alert>
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6">
                <x-alert tone="danger">{{ $errors->first() }}</x-alert>
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-5">
            @csrf

            <x-field label="Email" for="email" errorName="email">
                <x-input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
            </x-field>

            <x-field label="Password" for="password" errorName="password">
                <div class="relative" x-data="{ show: false }">
<x-input id="password" type="password" name="password"
                         x-bind:type="show ? 'text' : 'password'"
                         class="pr-10"
                         required autocomplete="current-password" />
                    <button type="button" @click="show = !show"
                            :aria-label="show ? 'Hide password' : 'Show password'"
                            :title="show ? 'Hide password' : 'Show password'"
                            class="absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-[0.55rem] text-muted transition-colors hover:bg-accent-soft hover:text-ink">
                        <x-icon name="eye" class="h-4 w-4" x-show="!show" x-cloak />
                        <x-icon name="eye-off" class="h-4 w-4" x-show="show" x-cloak />
                    </button>
                </div>
            </x-field>

            <div class="flex items-center justify-between gap-4">
                <label class="flex items-center gap-2.5 text-sm text-ink">
                    <input type="checkbox" name="remember" class="checkbox h-4 w-4 rounded border-line">
                    Keep me signed in
                </label>

                <a href="{{ route('password.request') }}"
                   class="shrink-0 text-xs font-semibold text-accent transition-colors hover:text-accent-hi">
                    Forgot password?
                </a>
            </div>

            <x-btn variant="accent" type="submit" class="w-full py-2.5">Sign in</x-btn>
        </form>

        <p class="mt-8 text-xs text-muted">Private barber management system.</p>
    </div>
</div>

</body>
</html>
