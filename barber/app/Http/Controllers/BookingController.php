<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function index(Request $request): View
    {
        $query = Booking::with(['customer', 'service']);

        if ($search = $request->input('search')) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($serviceId = $request->input('service_id')) {
            $query->where('service_id', $serviceId);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('appointment_date', $date);
        }

        $bookings = $query->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(15)
            ->withQueryString();

        return view('bookings.index', [
            'bookings' => $bookings,
            'statuses' => BookingStatus::cases(),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load(['customer', 'service']);

        return view('bookings.show', ['booking' => $booking]);
    }

    public function edit(Booking $booking): View
    {
        $booking->load(['customer', 'service']);

        return view('bookings.edit', [
            'booking' => $booking,
            'customers' => Customer::orderBy('name')->get(),
            'services' => Service::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ]);

        $service = Service::findOrFail($data['service_id']);

        $this->bookingService->reschedule(
            $booking,
            $data['appointment_date'],
            $data['appointment_time'].':00',
        );

        $booking->update([
            'customer_id' => $data['customer_id'],
            'service_id' => $data['service_id'],
            'price' => $service->price,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('bookings.index')->with('status', 'Booking updated.');
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_column(BookingStatus::cases(), 'value'))],
        ]);

        $status = BookingStatus::from($request->status);

        $this->bookingService->updateStatus($booking, $status);

        return back()->with('status', 'Booking status updated to '.$status->value.'.');
    }

    public function reschedule(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        $this->bookingService->reschedule(
            $booking,
            $data['appointment_date'],
            $data['appointment_time'].':00',
        );

        return back()->with('status', 'Booking rescheduled.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        return redirect()->route('bookings.index')->with('status', 'Booking deleted.');
    }
}
