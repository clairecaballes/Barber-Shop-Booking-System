<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'reason', 'start_time', 'end_time'])]
class BlockedSlot extends Model
{
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
