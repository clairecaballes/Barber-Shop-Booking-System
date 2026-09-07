<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\BlockedSlot;
use App\Models\Booking;
use App\Models\BusinessSetting;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Check if a proposed slot is available (no overlapping active bookings).
     */
    public function isSlotAvailable(string $date, string $time, int $serviceId, ?int $excludeBookingId = null): bool
    {
        if (BlockedSlot::where('date', $date)->exists()) {
            return false;
        }

        $service = Service::findOrFail($serviceId);
        $duration = $service->duration;

        $start = Carbon::parse("{$date} {$time}");
        $end = $start->copy()->addMinutes($duration);

        // Load active bookings for the date with their services, so we can
        // compute each existing booking's end time in PHP (DB-agnostic).
        $activeBookings = Booking::query()
            ->active()
            ->with('service')
            ->where('appointment_date', $date)
            ->when($excludeBookingId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->get();

        $overlapping = $activeBookings->contains(function (Booking $booking) use ($start, $end) {
            $bStart = Carbon::parse("{$booking->appointment_date->toDateString()} {$booking->appointment_time}");
            $bEnd = $bStart->copy()->addMinutes($booking->service->duration);

            return $start->lt($bEnd) && $end->gt($bStart);
        });

        return ! $overlapping;
    }

    /**
     * Get available time slots for a given date and service.
     */
    public function getAvailableSlots(string $date, int $serviceId): array
    {
        if (BlockedSlot::where('date', $date)->exists()) {
            return [];
        }

        $service = Service::findOrFail($serviceId);
        $duration = $service->duration;

        $hours = BusinessSetting::get('operating_hours', []);
        $dayOfWeek = strtolower(Carbon::parse($date)->englishDayOfWeek);

        if (! isset($hours[$dayOfWeek]['open']) || ! isset($hours[$dayOfWeek]['close'])) {
            return [];
        }

        $open = Carbon::parse("{$date} {$hours[$dayOfWeek]['open']}");
        $close = Carbon::parse("{$date} {$hours[$dayOfWeek]['close']}");

        $slots = [];
        $current = $open->copy();

        while ($current->copy()->addMinutes($duration)->lte($close)) {
            $slots[] = $current->format('H:i:s');
            $current->addMinutes($duration);
        }

        // Remove slots that conflict with existing bookings
        $activeBookings = Booking::query()
            ->active()
            ->where('appointment_date', $date)
            ->get();

        $available = [];
        foreach ($slots as $slot) {
            $slotStart = Carbon::parse("{$date} {$slot}");
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            $conflicts = $activeBookings->contains(function (Booking $booking) use ($slotStart, $slotEnd) {
                $bStart = Carbon::parse("{$booking->appointment_date->toDateString()} {$booking->appointment_time}");
                $bService = $booking->service;
                $bEnd = $bStart->copy()->addMinutes($bService->duration);

                return $slotStart->lt($bEnd) && $slotEnd->gt($bStart);
            });

            if (! $conflicts) {
                $available[] = $slot;
            }
        }

        return $available;
    }

    /**
     * Check if a date is a working day.
     */
    public function isWorkingDay(string $date): bool
    {
        if (BlockedSlot::where('date', $date)->exists()) {
            return false;
        }

        $hours = BusinessSetting::get('operating_hours', []);
        $dayOfWeek = strtolower(Carbon::parse($date)->englishDayOfWeek);

        return isset($hours[$dayOfWeek]['open']) && isset($hours[$dayOfWeek]['close']);
    }

    /**
     * Create a booking with overlap prevention (atomic).
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            if (! $this->isSlotAvailable($data['appointment_date'], $data['appointment_time'], $data['service_id'])) {
                abort(422, 'This time slot is no longer available.');
            }

            return Booking::create($data);
        });
    }

    /**
     * Reschedule a booking with overlap prevention (atomic).
     */
    public function reschedule(Booking $booking, string $date, string $time): Booking
    {
        return DB::transaction(function () use ($booking, $date, $time) {
            if (! $this->isSlotAvailable($date, $time, $booking->service_id, $booking->id)) {
                abort(422, 'This time slot is no longer available.');
            }

            $booking->update([
                'appointment_date' => $date,
                'appointment_time' => $time,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Update a booking's status.
     */
    public function updateStatus(Booking $booking, BookingStatus $status): Booking
    {
        $booking->update(['status' => $status]);

        return $booking->fresh();
    }
}
