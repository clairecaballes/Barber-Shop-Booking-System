@php
    $shopName = \App\Models\BusinessSetting::get('shop_name') ?? 'Barber Shop';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot password · {{ $shopName }}</title>

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
        <h2 class="text-lg font-semibold text-ink">Forgot password?</h2>
        <p class="mt-1 text-sm text-muted">Enter the email you sign in with and we'll send a 6-digit reset code.</p>

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

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
            @csrf

            <x-field label="Email" for="email" errorName="email">
                <x-input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="owner@barbershop.test" />
            </x-field>

            <x-btn variant="accent" type="submit" class="w-full py-2.5">Send reset code</x-btn>
        </form>

        <p class="mt-8 text-center text-xs text-muted">
            Remembered it?
            <a href="{{ route('login') }}" class="font-semibold text-accent transition-colors hover:text-accent-hi">Back to sign in</a>
        </p>
    </div>
</div>

</body>
</html>