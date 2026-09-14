@php
    $shopName = \App\Models\BusinessSetting::get('shop_name') ?? 'Barber Shop';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create account · {{ $shopName }}</title>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-page px-4 py-8 font-sans text-ink antialiased sm:py-10">

<div class="bento w-full max-w-md overflow-hidden">
    <div class="chrome chrome-sheen flex items-center justify-between gap-4 px-6 py-5">
        <div>
            <p class="text-[11px] text-rail-muted">Shop desk</p>
            <h1 class="mt-0.5 text-lg font-semibold tracking-tight text-rail-ink">{{ $shopName }}</h1>
        </div>
        <span class="flex flex-col items-center shrink-0" aria-hidden="true">
            <span class="pole-cap h-1.5 w-7"></span>
            <span class="pole pole-anim my-0.5 h-12 w-7"></span>
            <span class="pole-cap h-1.5 w-7"></span>
        </span>
    </div>

    <div class="p-6 sm:p-8">
        <h2 class="text-lg font-semibold text-ink">Create an account</h2>
        <p class="mt-1 text-sm text-muted">A desk for the shop, in under a minute.</p>

        @if ($errors->any())
            <div class="mt-6">
                <x-alert tone="danger">{{ $errors->first() }}</x-alert>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
            @csrf

            <x-field label="Name" for="name" errorName="name">
                <x-input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Your name" />
            </x-field>

            <x-field label="Email" for="email" errorName="email">
                <x-input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com" />
            </x-field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Password" for="password" hint="8+ characters, letters and numbers." errorName="password">
                    <x-input id="password" type="password" name="password" required autocomplete="new-password" />
                </x-field>

                <x-field label="Confirm password" for="password_confirmation">
                    <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                </x-field>
            </div>

            <x-btn variant="accent" type="submit" class="w-full py-2.5">Create account</x-btn>
        </form>

        <p class="mt-8 text-center text-xs text-muted">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-accent transition-colors hover:text-accent-hi">Sign in</a>
        </p>
    </div>
</div>

</body>
</html>