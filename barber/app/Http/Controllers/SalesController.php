<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function __construct(
        private readonly SalesService $sales,
    ) {}

    public function index(Request $request): View
    {
        $now = Carbon::now();

        $overallSales = $this->sales->salesOverall();
        $expensesOverall = $this->sales->expensesOverall();

        $metrics = [
            'today_sales' => $this->sales->salesOn($now),
            'week_sales' => $this->sales->salesThisWeek(),
            'month_sales' => $this->sales->salesThisMonth(),
            'year_sales' => $this->sales->salesThisYear(),
            'overall_sales' => $overallSales,
            'expenses_overall' => $expensesOverall,
            'net_sales' => $overallSales - $expensesOverall,
            'completed_count' => Booking::query()->completed()->count(),
            'avg_daily' => $this->sales->averageDailySales(),
            'avg_weekly' => $this->sales->averageWeeklySales(),
            'avg_monthly' => $this->sales->averageMonthlySales(),
            'cancellation_count' => Booking::query()->where('status', 'cancelled')->count(),
            'no_show_count' => Booking::query()->where('status', 'no_show')->count(),
            'busiest_day' => $this->sales->busiestDay(),
            'busiest_time' => $this->sales->busiestTime(),
        ];

        return view('sales.index', [
            'metrics' => $metrics,
        ]);
    }
}
