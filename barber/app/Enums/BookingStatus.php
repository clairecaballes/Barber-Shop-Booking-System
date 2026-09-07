<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Booked = 'booked';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    /**
     * Statuses that represent an "active" booking occupying a time slot.
     */
    public function blocksSlot(): bool
    {
        return in_array($this, [self::Pending, self::Booked], true);
    }
}
