<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'item' => fake()->words(2, true),
            'cost' => fake()->numberBetween(50, 2000) * 100,
            'expense_date' => fake()->date(),
            'notes' => null,
        ];
    }
}
