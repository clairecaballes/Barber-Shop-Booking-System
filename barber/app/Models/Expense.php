<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'item', 'cost', 'expense_date', 'notes'])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use BelongsToShop;

    use HasFactory;

    protected function casts(): array
    {
        return [
            'cost' => 'integer',
            'expense_date' => 'date:Y-m-d',
        ];
    }
}
