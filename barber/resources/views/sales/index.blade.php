@extends('layouts.app')
@section('title', 'Sales & Financial Analysis')

@section('content')
<div class="space-y-8">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-500/30 dark:bg-green-500/10 dark:text-green-400">{{ session('status') }}</div>
    @endif

    {{-- Metrics --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Today" value="{{ money($metrics['today_sales']) }}" icon="sales" accent="amber" />
        <x-stat-card label="This Week" value="{{ money($metrics['week_sales']) }}" icon="sales" accent="blue" />
        <x-stat-card label="This Month" value="{{ money($metrics['month_sales']) }}" icon="sales" accent="violet" />
        <x-stat-card label="This Year" value="{{ money($metrics['year_sales']) }}" icon="sales" accent="green" />
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-stat-card label="Overall Sales" value="{{ money($metrics['overall_sales']) }}" icon="sales" accent="green" />
        <x-stat-card label="Total Expenses" value="{{ money($metrics['expenses_overall']) }}" icon="sales" accent="red" />
        <x-stat-card label="Net Sales" value="{{ money($metrics['net_sales']) }}" icon="sales" accent="amber">
            <x-slot:footer>Sales minus expenses</x-slot:footer>
        </x-stat-card>
        <x-stat-card label="Completed Haircuts" value="{{ $metrics['completed_count'] }}" icon="bookings" accent="blue" />
        <x-stat-card label="Avg Daily" value="{{ money($metrics['avg_daily']) }}" icon="sales" accent="amber" />
        <x-stat-card label="Avg Monthly" value="{{ money($metrics['avg_monthly']) }}" icon="sales" accent="violet" />
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Cancellations" value="{{ $metrics['cancellation_count'] }}" icon="bookings" accent="red" />
        <x-stat-card label="No-shows" value="{{ $metrics['no_show_count'] }}" icon="bookings" accent="red" />
        <x-stat-card label="Busiest Day" value="{{ $metrics['busiest_day'] ?? 'N/A' }}" icon="calendar" accent="blue" />
        <x-stat-card label="Busiest Time" value="{{ $metrics['busiest_time'] ?? 'N/A' }}" icon="calendar" accent="amber" />
    </div>

    {{-- Expenses --}}
    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-6 shadow-sm"
        x-data="{ open: false, editing: {} }"
        @edit-expense.window="editing = $event.detail; open = true"
    >
        <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Expenses</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Item cost total:
                    <span class="font-semibold text-slate-900 dark:text-white">{{ money($expensePeriodTotal) }}</span>
                    — automatically deducted from sales.
                </p>
            </div>
            <form method="GET" action="{{ route('sales.index') }}">
                <label for="expense-period" class="block text-xs font-medium text-slate-500 dark:text-slate-400">Period</label>
                <select id="expense-period" name="expense_period" onchange="this.form.submit()"
                    class="mt-1 block rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
                    <option value="weekly" @selected($expensePeriod === 'weekly')>Weekly</option>
                    <option value="monthly" @selected($expensePeriod === 'monthly')>Monthly</option>
                    <option value="yearly" @selected($expensePeriod === 'yearly')>Yearly</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 text-xs font-medium text-slate-500 dark:text-slate-400 dark:bg-slate-800/50 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Notes</th>
                        <th class="px-4 py-3 text-right">Cost</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($expenseItems as $expense)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $expense->item }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $expense->expense_date->format('M j, Y') }}</td>
                            <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $expense->notes }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">{{ money($expense->cost) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button"
                                    @click="$dispatch('edit-expense', @js([
                                        'id' => $expense->id,
                                        'item' => $expense->item,
                                        'cost' => number_format($expense->cost / 100, 2, '.', ''),
                                        'expense_date' => $expense->expense_date->toDateString(),
                                        'notes' => $expense->notes,
                                    ]))"
                                    class="text-xs text-amber-600 hover:underline">Edit</button>
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline"
                                    onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No expenses for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($expenseItems->isNotEmpty())
                    <tfoot class="border-t border-slate-100 dark:border-slate-800 bg-slate-50 text-sm font-semibold dark:bg-slate-800/50 text-slate-900 dark:text-white">
                        <tr>
                            <td class="px-4 py-3" colspan="3">Item cost total</td>
                            <td class="px-4 py-3 text-right text-red-600">{{ money($expensePeriodTotal) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Add expense --}}
        <form method="POST" action="{{ route('expenses.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
            @csrf
            <div>
                <label for="expense-item" class="block text-xs font-medium text-slate-500 dark:text-slate-400">Item</label>
                <input id="expense-item" name="item" type="text" required placeholder="e.g. Clipper oil"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
            </div>
            <div>
                <label for="expense-cost" class="block text-xs font-medium text-slate-500 dark:text-slate-400">Cost (₱)</label>
                <input id="expense-cost" name="cost" type="number" step="0.01" min="0" required placeholder="0.00"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
            </div>
            <div>
                <label for="expense-date" class="block text-xs font-medium text-slate-500 dark:text-slate-400">Date</label>
                <input id="expense-date" name="expense_date" type="date" value="{{ now()->toDateString() }}" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
            </div>
            <div>
                <label for="expense-notes" class="block text-xs font-medium text-slate-500 dark:text-slate-400">Notes (optional)</label>
                <input id="expense-notes" name="notes" type="text"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
            </div>
            <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">Add Expense</button>
        </form>

        {{-- Edit expense modal --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="open = false"></div>
            <div class="relative w-full max-w-md rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-6 shadow-lg">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Edit Expense</h3>
                <form method="POST" :action="'{{ url('expenses') }}/' + editing.id" class="mt-4 space-y-4">
                    @csrf @method('PATCH')
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Item</label>
                        <input type="text" name="item" x-model="editing.item" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Cost (₱)</label>
                            <input type="number" name="cost" step="0.01" min="0" x-model="editing.cost" required
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Date</label>
                            <input type="date" name="expense_date" x-model="editing.expense_date" required
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Notes (optional)</label>
                        <input type="text" name="notes" x-model="editing.notes"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-slate-900 px-4 dark:bg-slate-100 dark:text-slate-900 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:hover:bg-white">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Monthly schedule download --}}
    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-6 shadow-sm" x-data="{ month: '{{ now()->format('Y-m') }}' }">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Monthly Schedule</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Download the schedule as an image to share.</p>
            </div>
            <div class="flex items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">Month</label>
                    <input type="month" x-model="month" x-on:change="month = $event.target.value" class="mt-1 block rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-slate-900 dark:text-white">
                </div>
                <button x-on:click="downloadSchedule(month)" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">Download Image</button>
            </div>
        </div>
        <div id="monthly-schedule" class="rounded-lg border border-slate-200 p-4 text-sm text-slate-500 dark:text-slate-400">
            Pick a month to preview the schedule.
        </div>
    </div>
</div>

<script>
async function buildSchedule(month) {
            const [y, m] = month.split('-');
            const start = y + '-' + m + '-01';
            const lastDay = new Date(y, parseInt(m), 0).getDate();
            const end = y + '-' + m + '-' + lastDay;

            const res = await fetch('/api/calendar/events?start=' + start + '&end=' + end);
            if (!res.ok) return null;
            const events = await res.json();

            const grouped = {};
            events.forEach(e => {
                const date = e.start.split('T')[0];
                if (!grouped[date]) grouped[date] = [];
                grouped[date].push(e);
            });

            let html = '<div style="font-family:sans-serif">';
            html += '<h3 style="margin:0 0 12px;font-size:18px;text-align:center">Monthly Schedule — ' + month + '</h3>';
            for (let d = 1; d <= lastDay; d++) {
                const dateStr = y + '-' + m + ('0' + d).slice(-2);
                const dayEvents = grouped[dateStr] || [];
                html += '<div style="padding:6px 8px;border-bottom:1px solid #e2e8f0;">';
                html += '<div style="font-weight:600">' + dateStr + ' <span style="font-weight:400;color:#64748b">(' + dayEvents.length + ')</span></div>';
                if (dayEvents.length) {
                    dayEvents.forEach(e => {
                        html += '<div style="padding-left:12px;color:#334155">' + (e.start.split('T')[1] || '').slice(0, 5) + ' — ' + e.title + '</div>';
                    });
                } else {
                    html += '<div style="padding-left:12px;color:#94a3b8">No bookings</div>';
                }
                html += '</div>';
            }
            html += '</div>';
            return html;
        }

async function downloadSchedule(month) {
    const html = await buildSchedule(month);
    if (!html) return;
    const el = document.getElementById('monthly-schedule');
    el.innerHTML = html;
    const canvas = await window.html2canvas(el, { backgroundColor: '#ffffff' });
    const link = document.createElement('a');
    link.download = 'monthly-schedule-' + month + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}
</script>
@endsection
