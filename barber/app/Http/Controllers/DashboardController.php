<?php

namespace App\Http\Controllers;

use App\Services\SalesService;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SalesService $sales,
    ) {}

    public function index(): View
    {
        $now = Carbon::now();

        $metrics = [
            'today_bookings' => $this->sales->bookingsOn($now),
            'today_completed' => $this->sales->completedOn($now),
            'today_sales' => $this->sales->salesOn($now),
            'week_sales' => $this->sales->salesThisWeek(),
            'month_sales' => $this->sales->salesThisMonth(),
            'year_sales' => $this->sales->salesThisYear(),
            'overall_sales' => $this->sales->salesOverall(),
        ];

        return view('dashboard.index', [
            'metrics' => $metrics,
            'schedule' => $this->sales->schedule(),
        ]);
    }
}
