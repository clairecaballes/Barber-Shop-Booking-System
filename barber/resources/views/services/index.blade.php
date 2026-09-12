@extends('layouts.app')
@section('title', 'Services')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    @if (session('status'))
        <x-alert>{{ session('status') }}</x-alert>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-semibold text-ink">Menu of services</h2>
            <p class="text-xs text-muted">Prices and durations drive the booking slots.</p>
        </div>
        <x-btn variant="accent" :href="route('services.create')">
            <x-icon name="plus" class="h-4 w-4" />
            Add service
        </x-btn>
    </div>

    <div class="space-y-3">
        @forelse ($services as $service)
            <div class="bento bento-hover flex flex-wrap items-center justify-between gap-4 rounded-tile px-5 py-4">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.65rem] border border-line text-muted"
                          style="background-image: linear-gradient(180deg, rgba(255,255,255,0.05), transparent);">
                        <x-icon name="scissors" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="truncate text-sm font-semibold text-ink">{{ $service->name }}</p>
                            @unless ($service->active)
                                <span class="chip text-muted">Hidden</span>
                            @endunless
                        </div>
                        <p class="numeral mt-0.5 text-xs text-muted">{{ $service->duration }} min in the chair</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="numeral text-sm font-semibold text-ink">{{ money($service->price) }}</span>
                    <x-btn variant="metal" :href="route('services.edit', $service)" class="px-3 py-1.5 text-xs">Edit</x-btn>
                    <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Delete this service?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="Delete {{ $service->name }}"
                                class="flex h-8 w-8 items-center justify-center rounded-[0.65rem] text-muted transition-colors hover:bg-red-500/10 hover:text-red-500">
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <x-panel bodyClass="p-10 text-center">
                <p class="text-sm text-muted">No services yet — add what you actually cut.</p>
                <div class="mt-4 flex justify-center">
                    <x-btn variant="accent" :href="route('services.create')">Add a service</x-btn>
                </div>
            </x-panel>
        @endforelse
    </div>
</div>
@endsection
