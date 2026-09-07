@extends('layouts.app')
@section('title', 'Business Settings')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">

    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Business Information</h2>
        </div>
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-5 px-6 py-5">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Shop Name</label>
                <input type="text" name="shop_name" value="{{ $settings['shop_name'] }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                @error('shop_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Currency Symbol</label>
                <input type="text" name="currency" value="{{ $settings['currency'] }}" required maxlength="10" class="mt-1 block w-32 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:hover:bg-white">Save</button>
            </div>
        </form>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Working Hours</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Set your opening and closing times. Leave blank for days off.</p>
        </div>
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-4 px-6 py-5">
            @csrf
            @method('PATCH')
            <input type="hidden" name="shop_name" value="{{ $settings['shop_name'] }}">
            <input type="hidden" name="currency" value="{{ $settings['currency'] }}">

            @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                <div class="flex items-center gap-4">
                    <span class="w-28 text-sm font-medium text-slate-700 dark:text-slate-200 capitalize">{{ $day }}</span>
                    <input type="time" name="operating_hours[{{ $day }}][open]" value="{{ $settings['operating_hours'][$day]['open'] ?? '' }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                    <span class="text-sm text-slate-400">to</span>
                    <input type="time" name="operating_hours[{{ $day }}][close]" value="{{ $settings['operating_hours'][$day]['close'] ?? '' }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                </div>
            @endforeach

            <div class="flex justify-end pt-2">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:hover:bg-white">Save Hours</button>
            </div>
        </form>
    </section>
</div>
@endsection
