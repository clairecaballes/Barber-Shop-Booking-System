@php
    $current = Route::currentRouteName();

    $sections = [
        'daily' => [
            'label' => 'Daily',
            'items' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard', 'match' => ['dashboard']],
                ['key' => 'calendar', 'label' => 'Calendar', 'route' => 'calendar.index', 'icon' => 'calendar', 'match' => ['calendar.index', 'api.calendar.events']],
                ['key' => 'bookings', 'label' => 'Bookings', 'route' => 'bookings.index', 'icon' => 'bookings', 'match' => ['bookings.index', 'bookings.show', 'bookings.edit', 'quick-bookings.index']],
                ['key' => 'customers', 'label' => 'Customers', 'route' => 'customers.index', 'icon' => 'customers', 'match' => ['customers.index', 'customers.create', 'customers.show', 'customers.edit']],
                ['key' => 'services', 'label' => 'Services', 'route' => 'services.index', 'icon' => 'services', 'match' => ['services.index', 'services.create', 'services.show', 'services.edit']],
            ],
        ],
        'business' => [
            'label' => 'Business',
            'items' => [
                ['key' => 'sales', 'label' => 'Sales', 'route' => 'sales.index', 'icon' => 'sales', 'match' => ['sales.index']],
                ['key' => 'expenses', 'label' => 'Expenses', 'route' => 'expenses.index', 'icon' => 'receipt', 'match' => ['expenses.index']],
                ['key' => 'settings', 'label' => 'Settings', 'route' => 'settings.index', 'icon' => 'settings', 'match' => ['settings.index']],
            ],
        ],
    ];
@endphp

@foreach ($sections as $section)
    @unless ($loop->first)
        <div class="mx-3 my-3 h-px bg-white/6"></div>
    @endunless

    <p class="px-3 pb-1.5 pt-2 text-[11px] text-rail-muted">{{ $section['label'] }}</p>

    <div class="rail-path space-y-1">
        @foreach ($section['items'] as $item)
            @php
                $isActive = in_array($current, $item['match']);
                $exists = Route::has($item['route']);
            @endphp

            <a href="{{ $exists ? route($item['route']) : '#' }}"
               @if (! $exists) title="Coming soon" @endif
               @if ($isActive) aria-current="page" @endif
               class="rail-item {{ $isActive ? 'rail-item-active' : '' }}">
                <span class="rail-node">
                    <x-icon :name="$item['icon']" class="h-4 w-4" />
                </span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
@endforeach
