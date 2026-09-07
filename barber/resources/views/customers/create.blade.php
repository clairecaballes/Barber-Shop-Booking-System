@extends('layouts.app')
@section('title', 'Add Customer')

@section('content')
<div class="mx-auto max-w-lg">
    <section class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 px-6 py-4"><h2 class="text-sm font-semibold text-slate-900 dark:text-white">New Customer</h2></div>
        <form method="POST" action="{{ route('customers.store') }}" class="space-y-5 px-6 py-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Messenger ID</label>
                <input type="text" name="messenger_id" value="{{ old('messenger_id') }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                @error('messenger_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Notes</label>
                <textarea name="notes" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">{{ old('notes') }}</textarea>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('customers.index') }}" class="rounded-lg border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:hover:bg-white">Create</button>
            </div>
        </form>
    </section>
</div>
@endsection
