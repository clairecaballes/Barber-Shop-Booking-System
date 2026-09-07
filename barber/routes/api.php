<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\CalendarEventsController;
use App\Http\Controllers\Api\QuickBookingSlotsController;
use Illuminate\Support\Facades\Route;

// The API routes live outside the `web` group, so sessions aren't loaded by default.
// Add the `web` group here so the session-based `auth` guard works for these routes.
Route::middleware(['web', 'auth'])->name('api.')->group(function () {
    Route::get('calendar/events', [CalendarEventsController::class, 'index'])->name('calendar.events');
    Route::get('availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::get('quick-booking/slots', [QuickBookingSlotsController::class, 'index'])->name('quick-booking.slots');
});
