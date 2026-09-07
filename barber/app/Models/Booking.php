<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Carbon\CarbonInterface;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_id',
    'service_id',
    'appointment_date',
    'appointment_time',
    'price',
    'status',
    'notes',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date:Y-m-d',
            'appointment_time' => 'string',
            'price' => 'integer',
            'status' => BookingStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope to bookings that occupy (block) their time slot.
     *
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn(
            'status',
            array_map(fn (BookingStatus $status) => $status->value, [
                BookingStatus::Pending,
                BookingStatus::Booked,
            ])
        );
    }

    /**
     * Scope to completed bookings.
     *
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Completed->value);
    }

    /**
     * Scope to bookings for a given calendar day.
     *
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    public function scopeOnDate(Builder $query, string|CarbonInterface $date): Builder
    {
        return $query->whereDate('appointment_date', $date);
    }
}
