@extends('layouts.app')
@section('title', 'Services')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">

    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">{{ session('status') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-900 dark:text-white">All Services</h2>
        <a href="{{ route('services.create') }}" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">+ Add Service</a>
    </div>

    <div class="space-y-3">
        @forelse ($services as $service)
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-5 py-4 shadow-sm">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $service->name }}</p>
                        @unless ($service->active)
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500 dark:text-slate-400 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                        @endunless
                    </div>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $service->duration }} minutes</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-amber-600">{{ money($service->price) }}</span>
                    <a href="{{ route('services.edit', $service) }}" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Edit</a>
                    <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Delete this service?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-sm text-slate-400">No services yet.</p>
        @endforelse
    </div>
</div>
@endsection
