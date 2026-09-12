@php
    $shopName = \App\Models\BusinessSetting::get('shop_name') ?? 'Barber Shop';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset password · {{ $shopName }}</title>

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
        <h2 class="text-lg font-semibold text-ink">Set a new password</h2>
        <p class="mt-1 text-sm text-muted">Enter the email, the 6-digit code you received, and a new password.</p>

        @if (session('status'))
            <div class="mt-6">
                <x-alert>{{ session('status') }}</x-alert>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
            @csrf

            <x-field label="Email" for="email" errorName="email">
                <x-input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email" />
            </x-field>

            <x-field label="Reset code" for="code" hint="The 6-digit code that was emailed to you." errorName="code">
                <x-input id="code" name="code" value="{{ old('code') }}" required inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" class="numeral tracking-[0.35em]" />
            </x-field>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="New password" for="password" errorName="password">
                    <x-input id="password" type="password" name="password" required autocomplete="new-password" />
                </x-field>

                <x-field label="Confirm new password" for="password_confirmation">
                    <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                </x-field>
            </div>

            <x-btn variant="accent" type="submit" class="w-full py-2.5">Reset password</x-btn>
        </form>

        <p class="mt-8 text-center text-xs text-muted">
            Remembered it?
            <a href="{{ route('login') }}" class="font-semibold text-accent transition-colors hover:text-accent-hi">Back to sign in</a>
        </p>
    </div>
</div>

</body>
</html>