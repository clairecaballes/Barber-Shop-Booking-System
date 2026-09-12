<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->input('period', 'monthly');
        if (! in_array($period, ['weekly', 'monthly', 'yearly'], true)) {
            $period = 'monthly';
        }

        $now = Carbon::now();

        [$start, $end] = match ($period) {
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'yearly' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };

        $sales = app(SalesService::class);

        return view('expenses.index', [
            'period' => $period,
            'items' => $sales->expenseItemsBetween($start, $end),
            'periodTotal' => $sales->expensesBetween($start, $end),
            'allTimeTotal' => $sales->expensesOverall(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Expense::create($data);

        return back()->with('status', 'Expense added.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $expense->update($this->validated($request));

        return back()->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('status', 'Expense deleted.');
    }

    /**
     * Validate the request and convert the peso amount to centavos.
     *
     * @return array{item: string, cost: int, expense_date: string, notes: string|null}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'item' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $data['cost'] = (int) round($data['cost'] * 100); // Convert pesos to centavos

        return $data;
    }
}
