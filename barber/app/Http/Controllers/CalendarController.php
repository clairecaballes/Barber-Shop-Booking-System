<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        return view('calendar.index');
    }

    /**
     * FullCalendar event source endpoint.
     */
    public function events(Request $request): JsonResponse
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $bookings = Booking::query()
            ->with(['customer', 'service'])
            ->whereBetween('appointment_date', [$start, $end])
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
                    'title' => ($booking->customer->name ?? 'Walk-in').' - '.$booking->service->name,
                    'start' => $booking->appointment_date->toDateString().'T'.$booking->appointment_time,
                    'end' => Carbon::parse($booking->appointment_date->toDateString().' '.$booking->appointment_time)
                        ->addMinutes($booking->service->duration)
                        ->toDateTimeString(),
                    'color' => $statusColors[$booking->status->value] ?? '#71717a',
                    'textColor' => '#141416',
                    'extendedProps' => [
                        'customerName' => $booking->customer->name ?? 'Walk-in',
                        'serviceName' => $booking->service->name,
                        'price' => $booking->price,
                        'status' => $booking->status->value,
                        'notes' => $booking->notes,
                    ],
                ];
            });

        return response()->json($bookings);
    }
}
