<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BlockedSlotController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\QuickBookingController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.attempt');

    Route::get('forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendCode'])
        ->middleware('throttle:5,10')
        ->name('password.email');
    Route::get('reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:10,10')
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('account', [AccountController::class, 'update'])->name('account.update');
    Route::patch('account/password', [AccountController::class, 'updatePassword'])->name('account.password');

    // Calendar
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('calendar/blocked-slots', [BlockedSlotController::class, 'store'])->name('calendar.blocks.store');

    // Quick Booking
    Route::get('quick-bookings', [QuickBookingController::class, 'index'])->name('quick-bookings.index');
    Route::post('quick-bookings', [QuickBookingController::class, 'store'])->name('quick-bookings.store');

    // Bookings
    Route::resource('bookings', BookingController::class)->except(['create', 'store']);
    Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');
    Route::patch('bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])->name('bookings.reschedule');

    // Customers
    Route::resource('customers', CustomerController::class);

    // Services
    Route::resource('services', ServiceController::class);

    // Sales
    Route::get('sales', [SalesController::class, 'index'])->name('sales.index');

    // Expenses
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::resource('expenses', ExpenseController::class)->only(['store', 'update', 'destroy']);

    // Settings
    Route::get('settings', [BusinessSettingsController::class, 'index'])->name('settings.index');
    Route::patch('settings', [BusinessSettingsController::class, 'update'])->name('settings.update');
});
