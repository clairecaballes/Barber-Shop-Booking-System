<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'service_id' => ['required', 'exists:services,id'],
        ]);

        $slots = $this->bookingService->getAvailableSlots(
            $request->date,
            $request->service_id,
        );

        return response()->json(['slots' => $slots]);
    }
}
