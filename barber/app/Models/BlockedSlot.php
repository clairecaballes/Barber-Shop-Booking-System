<?php

namespace App\Models;

use App\Models\Concerns\BelongsToShop;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'date', 'reason', 'start_time', 'end_time'])]
class BlockedSlot extends Model
{
    use BelongsToShop;

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    /**
     * Whether this block covers the entire day rather than a time window.
     */
    public function isFullDay(): bool
    {
        return ! $this->start_time || ! $this->end_time;
    }
}
