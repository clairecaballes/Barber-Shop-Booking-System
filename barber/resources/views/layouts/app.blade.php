<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') · {{ \App\Models\BusinessSetting::get('shop_name') ?? 'Barber Shop' }}</title>

    <script>
        // Apply the saved theme before first paint to avoid a flash.
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }

        function toggleTheme() {
            const dark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-200">

<div class="min-h-screen lg:flex" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         x-cloak
         class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden" style="display: none;"></div>

    {{-- Sidebar --}}
    <aside x-show="sidebarOpen" @keydown.escape.window="sidebarOpen = false"
           x-cloak
           class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-300 transition-transform duration-200 lg:static lg:translate-x-0 lg:transition-none lg:block! dark:bg-slate-950"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           style="display: none;">

        <div class="flex items-center justify-between px-6 py-5">
            <div>
                <p class="text-lg font-bold tracking-tight text-white">Barber <span class="text-amber-400">Shop</span></p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Management dashboard</p>
            </div>
            <button type="button" @click="sidebarOpen = false" class="text-slate-400 hover:text-white lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="mt-2 flex-1 space-y-1 px-3">
            @include('layouts.partials.nav', ['active' => $active ?? ''])
        </nav>

        <div class="border-t border-slate-800 px-6 py-4 text-xs text-slate-500 dark:text-slate-400">
            &copy; {{ date('Y') }} {{ \App\Models\BusinessSetting::get('shop_name') ?? 'Barber Shop' }}
        </div>
    </aside>

    {{-- Main column --}}
    <div class="flex min-w-0 flex-1 flex-col">

        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-4 py-3 lg:px-8">
            <div class="flex items-center gap-3">
                <button type="button" @click="sidebarOpen = true" class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white lg:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-base font-semibold text-slate-900 sm:text-lg dark:text-white">@yield('title', 'Dashboard')</h1>
                    <p class="hidden text-xs text-slate-500 dark:text-slate-400 sm:block">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Theme toggle --}}
                <button type="button"
                        onclick="toggleTheme()"
                        aria-label="Toggle dark mode"
                        title="Toggle dark mode"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-amber-300 dark:hover:bg-slate-800">
                    <svg class="h-5 w-5 dark:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg class="hidden h-5 w-5 dark:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>

                {{-- User menu --}}
                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button" @click="open = !open"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-400 font-semibold text-slate-900">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="hidden max-w-[10rem] truncate sm:block">{{ auth()->user()->name }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false"
                     x-cloak class="absolute right-0 mt-2 w-48 rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 py-1 shadow-lg"
                     style="display: none;">
                    <a href="{{ route('account.edit') }}" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50">Account</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">Log out</button>
                    </form>
                </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 px-4 py-6 lg:px-8">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>