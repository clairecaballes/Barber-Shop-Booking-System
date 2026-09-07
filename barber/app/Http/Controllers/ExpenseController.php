<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
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
