@php
    $shopName = \App\Models\BusinessSetting::get('shop_name') ?? 'Barber Shop';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') · {{ $shopName }}</title>

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
<body class="min-h-screen bg-page font-sans text-ink antialiased">

<div class="min-h-screen lg:flex" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-black/70 backdrop-blur-sm lg:hidden"></div>

    {{-- Rail --}}
    <aside x-show="sidebarOpen" x-cloak
           @keydown.escape.window="sidebarOpen = false"
           class="chrome chrome-sheen fixed inset-y-0 left-0 z-40 flex w-[16.5rem] transform flex-col border-r border-black/60 transition-transform duration-200 lg:static lg:flex! lg:translate-x-0 lg:transition-none"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="flex items-center justify-between gap-3 px-5 pb-4 pt-6">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                {{-- Barber pole mark: chrome caps around a lit cylinder. --}}
                <span class="flex flex-col items-center" aria-hidden="true">
                    <span class="pole-cap h-1.5 w-7"></span>
                    <span class="pole my-0.5 h-9 w-6"></span>
                    <span class="pole-cap h-1.5 w-7"></span>
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold tracking-tight text-rail-ink">{{ $shopName }}</span>
                    <span class="block text-[11px] text-rail-muted">Chair &amp; schedule desk</span>
                </span>
            </a>

            <button type="button" @click="sidebarOpen = false" aria-label="Close menu"
                    class="text-rail-muted transition-colors hover:text-rail-ink lg:hidden">
                <x-icon name="close" class="h-5 w-5" />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 pb-4" aria-label="Main">
            @include('layouts.partials.nav', ['active' => $active ?? ''])
        </nav>

        <div class="border-t border-white/6 px-5 py-4">
            <p class="text-[11px] text-rail-muted">Signed in as</p>
            <p class="mt-0.5 truncate text-xs font-medium text-rail-ink">{{ auth()->user()->name }}</p>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="flex min-w-0 flex-1 flex-col">

        {{-- Topbar --}}
        <header class="sticky top-0 z-20 border-b border-line bg-page/80 backdrop-blur-xl">
            <div class="flex items-center justify-between gap-3 px-4 py-3 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" @click="sidebarOpen = true" aria-label="Open menu"
                            class="chrome flex h-9 w-9 items-center justify-center rounded-[0.65rem] border border-white/10 text-rail-muted transition-colors hover:text-rail-ink lg:hidden">
                        <x-icon name="menu" class="h-4 w-4" />
                    </button>
                    <div class="min-w-0">
                        <h1 class="truncate text-sm font-semibold text-ink sm:text-base">@yield('title', 'Dashboard')</h1>
                        <p class="hidden text-[11px] text-muted sm:block">{{ now()->format('l, F j, Y') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if (Route::has('quick-bookings.index'))
                        <x-btn variant="accent" :href="route('quick-bookings.index')" class="hidden sm:inline-flex">
                            <x-icon name="plus" class="h-4 w-4" />
                            Quick booking
                        </x-btn>
                    @endif

                    {{-- Theme toggle --}}
                    <button type="button" onclick="toggleTheme()" aria-label="Toggle light and dark theme"
                            title="Toggle light and dark theme"
                            class="chrome flex h-9 w-9 items-center justify-center rounded-[0.65rem] border border-white/10 text-rail-muted transition-colors hover:text-rail-ink">
                        <x-icon name="sun" class="h-4 w-4 dark:hidden" />
                        <x-icon name="moon" class="hidden h-4 w-4 dark:block" />
                    </button>

                    {{-- User menu --}}
                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" aria-haspopup="menu"
                                class="btn btn-metal px-2.5 py-1.5">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold"
                                  style="background-image: linear-gradient(180deg, var(--accent-hi), var(--accent)); color: var(--accent-ink);">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden max-w-[9rem] truncate text-xs sm:block">{{ auth()->user()->name }}</span>
                            <x-icon name="chevron-down" class="h-3.5 w-3.5 text-muted" />
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                             class="bento absolute right-0 z-30 mt-2 w-52 overflow-hidden p-1.5">
                            <a href="{{ route('account.edit') }}"
                               class="flex items-center gap-2.5 rounded-[0.6rem] px-3 py-2 text-sm text-ink transition-colors hover:bg-accent-soft">
                                <x-icon name="user" class="h-4 w-4 text-muted" />
                                Account
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-2.5 rounded-[0.6rem] px-3 py-2 text-left text-sm text-red-500 transition-colors hover:bg-red-500/10">
                                    <x-icon name="logout" class="h-4 w-4" />
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 px-4 py-6 lg:px-8 lg:py-8">
            <div class="mx-auto w-full max-w-[80rem]">
                @yield('content')
            </div>
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
