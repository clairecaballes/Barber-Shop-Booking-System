@php
    $current = Route::currentRouteName();

    $sections = [
        'main' => [
            'label' => 'Menu',
            'items' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard', 'match' => ['dashboard']],
                ['key' => 'calendar', 'label' => 'Calendar', 'route' => 'calendar.index', 'icon' => 'calendar', 'match' => ['calendar.index', 'api.calendar.events']],
                ['key' => 'bookings', 'label' => 'Bookings', 'route' => 'bookings.index', 'icon' => 'bookings', 'match' => ['bookings.index', 'bookings.show', 'bookings.edit', 'quick-bookings.index']],
                ['key' => 'customers', 'label' => 'Customers', 'route' => 'customers.index', 'icon' => 'customers', 'match' => ['customers.index', 'customers.create', 'customers.show', 'customers.edit']],
                ['key' => 'services', 'label' => 'Services', 'route' => 'services.index', 'icon' => 'services', 'match' => ['services.index', 'services.create', 'services.show', 'services.edit']],
            ],
        ],
        'manage' => [
            'label' => 'Manage',
            'items' => [
                ['key' => 'sales', 'label' => 'Sales', 'route' => 'sales.index', 'icon' => 'sales', 'match' => ['sales.index']],
                ['key' => 'settings', 'label' => 'Settings', 'route' => 'settings.index', 'icon' => 'settings', 'match' => ['settings.index']],
            ],
        ],
    ];
@endphp

@foreach ($sections as $section)
    <div class="px-3 pt-5 pb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-500">
        {{ $section['label'] }}
    </div>
    <div class="mt-1 space-y-1">
        @foreach ($section['items'] as $item)
            @php
                $isActive = in_array($current, $item['match']);
                $exists = Route::has($item['route']);
            @endphp
            <a href="{{ $exists ? route($item['route']) : '#' }}"
               @if(!$exists) title="Coming soon" @endif
               class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200
                      {{ $isActive
                            ? 'bg-gradient-to-r from-amber-400/90 to-amber-500/80 text-white shadow-lg shadow-amber-500/20'
                            : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                {{-- Active left indicator --}}
                <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-white/90 transition-all duration-200 {{ $isActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-40' }}"></span>

                <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0 transition-transform duration-200 group-hover:scale-110" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
@endforeach
