<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login · Barber Shop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-900 px-4 dark:bg-slate-950">

<div class="w-full max-w-sm">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold tracking-tight text-white">
            Barber <span class="text-amber-400">Shop</span>
        </h1>
        <p class="mt-1 text-sm text-slate-400">Sign in to your dashboard</p>
    </div>

    <div class="rounded-2xl bg-white p-8 shadow-xl dark:bg-slate-900">
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       required autofocus
                       autocomplete="username"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-amber-500 dark:border-slate-600 dark:bg-slate-900 focus:ring-amber-300">
                Remember me
            </label>

            <button type="submit"
                    class="w-full rounded-lg bg-slate-900 px-4 py-2.5 dark:bg-slate-100 dark:text-slate-900 dark:focus:ring-slate-100 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-slate-800 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                Sign in
            </button>
        </form>
    </div>

    <p class="mt-6 text-center text-xs text-slate-600 dark:text-slate-300">
        Private barber management system
    </p>
</div>

</body>
</html>