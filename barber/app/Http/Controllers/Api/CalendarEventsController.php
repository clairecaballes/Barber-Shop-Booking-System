<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\BlockedSlot;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarEventsController extends Controller
{
    /**
     * FullCalendar event source (JSON).
     */
    public function index(Request $request): JsonResponse
    {
        $start = $request->input('start');
        $end = $request->input('end');

        // Normalize FullCalendar's ISO bounds to plain dates (avoid timezone/format mismatches).
        $startDate = $start ? Carbon::parse($start)->toDateString() : now()->startOfMonth()->toDateString();
        $endDate = $end ? Carbon::parse($end)->toDateString() : now()->endOfMonth()->toDateString();

        $bookings = Booking::query()
            ->with(['customer', 'service'])
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->get()
            ->map(function (Booking $booking) {
                // Fills match the UI status dots; all carry dark ink so they read
                // as painted labels on the charcoal board.
                $statusColors = [
                    BookingStatus::Pending->value => '#f0b429',
                    BookingStatus::Booked->value => '#60a5fa',
                    BookingStatus::Completed->value => '#ccff00',
                    BookingStatus::Cancelled->value => '#f87171',
                    BookingStatus::NoShow->value => '#fb923c',
                ];

                return [
                    'id' => $booking->id,
                    'title' => ($booking->customer->name ?? 'Walk-in').' · '.$booking->service->name,
                    'start' => $booking->appointment_date->toDateString().'T'.$booking->appointment_time,
                    'end' => Carbon::parse($booking->appointment_date->toDateString().' '.$booking->appointment_time)
                        ->addMinutes($booking->service->duration)
                        ->toDateTimeString(),
                    'color' => $statusColors[$booking->status->value] ?? '#71717a',
                    'textColor' => '#141416',
                    'extendedProps' => [
                        'customerName' => $booking->customer->name ?? 'Walk-in',
                        'serviceName' => $booking->service->name,
                        'serviceId' => $booking->service_id,
                        'duration' => $booking->service->duration,
                        'price' => $booking->price,
                        'status' => $booking->status->value,
                        'notes' => $booking->notes,
                        'appointmentDate' => $booking->appointment_date->toDateString(),
                        'appointmentTime' => $booking->appointment_time,
                    ],
                ];
            });

        $blockedSlots = BlockedSlot::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->map(fn (BlockedSlot $blocked) => [
                'id' => 'block-'.$blocked->id,
                'title' => $blocked->reason ?: 'Barber on leave',
                'start' => $blocked->date->toDateString(),
                'allDay' => true,
                'color' => '#3f3f46',
                'textColor' => '#f4f4f5',
                'extendedProps' => [
                    'blocked' => true,
                    'reason' => $blocked->reason,
                ],
            ]);

        return response()->json($bookings->concat($blockedSlots));
    }
}
