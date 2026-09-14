@extends('layouts.app')
@section('title', 'Expenses')

@section('content')
<div class="space-y-6" x-data="{ showForm: false }">

    @if (session('status'))
        <x-alert>{{ session('status') }}</x-alert>
    @endif

    {{-- Headline --}}
    <section class="bento bento-raised bento-lit relative overflow-hidden p-6">
        <div class="pointer-events-none absolute -right-12 -top-16 h-44 w-44 rounded-full"
             style="background-image: radial-gradient(circle, var(--accent-glow), transparent 70%);"
             aria-hidden="true"></div>

        <div class="relative flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold text-muted">Item cost, {{ $period }}</p>
                <p class="numeral mt-3 text-4xl font-semibold text-ink">{{ money($periodTotal) }}</p>
                <p class="numeral mt-2 text-xs text-muted">
                    {{ money($allTimeTotal) }} all time — deducted from sales automatically.
                </p>
            </div>

            <form method="GET" action="{{ route('expenses.index') }}">
                <x-field label="Period" for="period">
                    <x-select id="period" name="period" onchange="this.form.submit()">
                        <option value="weekly" @selected($period === 'weekly')>Weekly</option>
                        <option value="monthly" @selected($period === 'monthly')>Monthly</option>
                        <option value="yearly" @selected($period === 'yearly')>Yearly</option>
                    </x-select>
                </x-field>
            </form>
        </div>
    </section>

    <div class="flex justify-end">
        <button type="button"
                @click="showForm = !showForm"
                class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-sm transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/60">
            <span x-text="showForm ? 'Close' : 'Log expense'">Log expense</span>
        </button>
    </div>

    <div x-show="showForm" x-transition.opacity class="contents">
        <x-panel title="Log an expense" bodyClass="p-6" class="bento-raised bento-lift">
            <form method="POST" action="{{ route('expenses.store') }}" class="space-y-5">
                @csrf

                <div class="grid gap-4 sm:grid-cols-3">
                    <x-field label="Item" for="expense-item" errorName="item">
                        <x-input id="expense-item" name="item" required placeholder="e.g. clipper oil" />
                    </x-field>

                    <x-field label="Cost (₱)" for="expense-cost" errorName="cost">
                        <x-input id="expense-cost" type="number" name="cost" step="0.01" min="0" required placeholder="0.00" />
                    </x-field>

                    <x-field label="Date" for="expense-date" errorName="expense_date">
                        <x-input id="expense-date" type="date" name="expense_date" value="{{ now()->toDateString() }}" required />
                    </x-field>
                </div>

                <x-field label="Notes" for="expense-notes" hint="Optional — where it was bought, size, receipt no.">
                    <x-textarea id="expense-notes" name="notes" rows="3" placeholder="Add a note…" class="w-full"></x-textarea>
                </x-field>

                <div class="flex justify-end">
                    <x-btn variant="accent" type="submit" class="w-full py-2.5 sm:w-auto">Save expense</x-btn>
                </div>
            </form>
        </x-panel>
    </div>

    {{-- Expense ledger --}}
    <x-panel title="Expenses" :subtitle="'All item costs logged in this '.$period.' period.'" bodyClass="p-6"
             x-data="{ open: false, editing: {} }"
             class="bento-raised bento-lift"
             @edit-expense.window="editing = $event.detail; open = true">

        <div class="bento-sunken overflow-x-auto">
            {{-- Mobile: stacked cards so notes and actions are never cut off. --}}
            <div class="divide-y divide-line sm:hidden">
                @if ($items->isNotEmpty())
                    @foreach ($items as $expense)
                        <div class="px-4 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-ink">{{ $expense->item }}</p>
                                    <p class="numeral mt-0.5 text-xs text-muted">{{ $expense->expense_date->format('M j, Y') }}</p>
                                </div>
                                <p class="numeral shrink-0 font-semibold text-red-500">{{ money($expense->cost) }}</p>
                            </div>

                            @if ($expense->notes)
                                <p class="mt-2 rounded-lg bg-accent-soft px-3 py-2 text-xs leading-relaxed text-muted">{{ $expense->notes }}</p>
                            @endif

                            <div class="mt-3 flex gap-2">
                                <button type="button"
                                    @click="$dispatch('edit-expense', @js([
                                        'id' => $expense->id,
                                        'item' => $expense->item,
                                        'cost' => number_format($expense->cost / 100, 2, '.', ''),
                                        'expense_date' => $expense->expense_date->toDateString(),
                                        'notes' => $expense->notes,
                                    ]))"
                                    class="rounded-[0.55rem] px-3 py-1.5 text-xs font-medium text-muted transition-colors hover:bg-accent-soft hover:text-ink">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                      onsubmit="return confirm('Delete this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-[0.55rem] px-3 py-1.5 text-xs font-medium text-red-500 transition-colors hover:bg-red-500/10">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between gap-3 bg-accent-soft px-4 py-3">
                        <span class="text-sm font-medium text-ink">Item cost total</span>
                        <span class="numeral text-sm font-semibold text-red-500">{{ money($periodTotal) }}</span>
                    </div>
                @else
                    <p class="px-4 py-10 text-center text-sm text-muted">No expenses logged for this period.</p>
                @endif
            </div>

            {{-- Desktop: the full-width table. --}}
            <table class="hidden w-full text-left text-sm sm:table">
                <thead>
                    <tr class="border-b border-line text-xs text-muted">
                        <th class="px-4 py-3 font-semibold">Item</th>
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Notes</th>
                        <th class="px-4 py-3 text-right font-semibold">Cost</th>
                        <th class="px-4 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($items as $expense)
                        <tr class="transition-colors hover:bg-accent-soft">
                            <td class="px-4 py-3 font-medium text-ink">{{ $expense->item }}</td>
                            <td class="numeral px-4 py-3 text-muted">{{ $expense->expense_date->format('M j, Y') }}</td>
                            <td class="max-w-[20rem] px-4 py-3 text-muted">{{ $expense->notes }}</td>
                            <td class="numeral px-4 py-3 text-right font-semibold text-red-500">{{ money($expense->cost) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button"
                                    @click="$dispatch('edit-expense', @js([
                                        'id' => $expense->id,
                                        'item' => $expense->item,
                                        'cost' => number_format($expense->cost / 100, 2, '.', ''),
                                        'expense_date' => $expense->expense_date->toDateString(),
                                        'notes' => $expense->notes,
                                    ]))"
                                    class="rounded-[0.55rem] px-2 py-1 text-xs font-medium text-muted transition-colors hover:bg-accent-soft hover:text-ink">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline"
                                      onsubmit="return confirm('Delete this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-[0.55rem] px-2 py-1 text-xs font-medium text-red-500 transition-colors hover:bg-red-500/10">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-muted">No expenses logged for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($items->isNotEmpty())
                    <tfoot class="border-t border-line text-sm font-semibold text-ink">
                        <tr>
                            <td class="px-4 py-3" colspan="3">Item cost total</td>
                            <td class="numeral px-4 py-3 text-right text-red-500">{{ money($periodTotal) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Edit expense --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="open = false"></div>

            <div class="bento relative w-full max-w-lg p-6">
                <h3 class="text-base font-semibold text-ink">Edit expense</h3>

                <form method="POST" :action="'{{ url('expenses') }}/' + editing.id" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')

                    <x-field label="Item">
                        <x-input name="item" x-model="editing.item" required />
                    </x-field>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-field label="Cost (₱)">
                            <x-input type="number" name="cost" step="0.01" min="0" x-model="editing.cost" required />
                        </x-field>

                        <x-field label="Date">
                            <x-input type="date" name="expense_date" x-model="editing.expense_date" required />
                        </x-field>
                    </div>

                    <x-field label="Notes">
                        <x-textarea name="notes" x-model="editing.notes" rows="3" class="w-full"></x-textarea>
                    </x-field>

                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                        <x-btn variant="ghost" type="button" @click="open = false">Cancel</x-btn>
                        <x-btn variant="accent" type="submit">Save changes</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </x-panel>
</div>
@endsection