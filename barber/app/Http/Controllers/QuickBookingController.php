<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuickBookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function index(): View
    {
        $customers = Customer::orderBy('name')->get();
        $services = Service::where('active', true)->orderBy('name')->get();

        return view('bookings.quick', [
            'customers' => $customers,
            'services' => $services,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        $service = Service::findOrFail($data['service_id']);

        // Resolve the customer: use the selected one, or create from the typed name.
        $customerId = $data['customer_id']
            ?? Customer::create(['name' => $data['customer_name']])->id;

        $this->bookingService->create([
            'customer_id' => $customerId,
            'service_id' => $data['service_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'].':00',
            'price' => $service->price,
            'status' => BookingStatus::Booked,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Booking added to calendar.']);
        }

        return redirect()->route('calendar.index')->with('status', 'Booking added to calendar.');
    }
}
