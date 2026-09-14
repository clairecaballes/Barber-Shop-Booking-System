<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalesService
{
    public function bookingsOn(Carbon $date): int
    {
        return Booking::query()->onDate($date)->count();
    }

    public function completedOn(Carbon $date): int
    {
        return Booking::query()->onDate($date)->completed()->count();
    }

    public function salesOn(Carbon $date): int
    {
        return Booking::query()->onDate($date)->completed()->sum('price');
    }

    public function salesThisWeek(): int
    {
        $now = Carbon::now();

        return $this->salesBetween($now->copy()->startOfWeek(), $now->copy()->endOfWeek());
    }

    public function salesThisMonth(): int
    {
        $now = Carbon::now();

        return $this->salesBetween($now->copy()->startOfMonth(), $now->copy()->endOfMonth());
    }

    public function salesThisYear(): int
    {
        $now = Carbon::now();

        return $this->salesBetween($now->copy()->startOfYear(), $now->copy()->endOfYear());
    }

    public function salesOverall(): int
    {
        return Booking::query()->completed()->sum('price');
    }

    /**
     * Total expenses (stored as centavos) across all time.
     */
    public function expensesOverall(): int
    {
        return (int) Expense::query()->sum('cost');
    }

    /**
     * Total expenses within a date range.
     */
    public function expensesBetween(Carbon $start, Carbon $end): int
    {
        return (int) Expense::query()
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->sum('cost');
    }

    /**
     * Expense items within a date range, newest first.
     *
     * @return Collection<int, Expense>
     */
    public function expenseItemsBetween(Carbon $start, Carbon $end): Collection
    {
        return Expense::query()
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('expense_date')
            ->orderByDesc('created_at')
            ->get();
    }

    public function salesBetween(Carbon $start, Carbon $end): int
    {
        return Booking::query()
            ->completed()
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->sum('price');
    }

    /**
     * Completed-sales totals per day across a date range, oldest first.
     * Days without takings are included with a zero value.
     *
     * @return array<int, array{date: string, label: string, value: int}>
     */
    public function salesByDay(Carbon $start, Carbon $end): array
    {
        $totals = Booking::query()
            ->completed()
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('appointment_date, SUM(price) as total')
            ->groupBy('appointment_date')
            ->get()
            ->mapWithKeys(fn ($row) => [
                Carbon::parse($row->appointment_date)->toDateString() => (int) $row->total,
            ]);

        $days = [];
        $current = $start->copy()->startOfDay();

        while ($current->lte($end)) {
            $key = $current->toDateString();

            $days[] = [
                'date' => $key,
                'label' => $current->format('D'),
                'value' => $totals[$key] ?? 0,
            ];

            $current->addDay();
        }

        return $days;
    }

    /**
     * Average daily sales (completed sales / days with at least one completed booking).
     */
    public function averageDailySales(): int
    {
        $totalDays = Booking::query()->completed()
            ->selectRaw('COUNT(DISTINCT appointment_date) as days')
            ->value('days') ?? 0;

        if ($totalDays === 0) {
            return 0;
        }

        return (int) (Booking::query()->completed()->sum('price') / $totalDays);
    }

    /**
     * Average weekly sales.
     */
    public function averageWeeklySales(): int
    {
        $firstBooking = Booking::query()->completed()
            ->orderBy('appointment_date')
            ->first();

        if (! $firstBooking) {
            return 0;
        }

        $weeks = max(1, (int) Carbon::parse($firstBooking->appointment_date)->startOfWeek()->diffInWeeks(now()));

        return (int) (Booking::query()->completed()->sum('price') / $weeks);
    }

    /**
     * Average monthly sales.
     */
    public function averageMonthlySales(): int
    {
        $firstBooking = Booking::query()->completed()
            ->orderBy('appointment_date')
            ->first();

        if (! $firstBooking) {
            return 0;
        }

        $months = max(1, (int) Carbon::parse($firstBooking->appointment_date)->startOfMonth()->diffInMonths(now()));

        return (int) (Booking::query()->completed()->sum('price') / $months);
    }

    /**
     * Busiest day of the week.
     */
    public function busiestDay(): ?string
    {
        $counts = [];

        foreach (Booking::query()->completed()->select('appointment_date')->get() as $booking) {
            $day = Carbon::parse($booking->appointment_date)->dayOfWeek;
            $counts[$day] = ($counts[$day] ?? 0) + 1;
        }

        if ($counts === []) {
            return null;
        }

        $bestDay = array_keys($counts, max($counts))[0];
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return $days[(int) $bestDay] ?? null;
    }

    /**
     * Busiest hour of the day.
     */
    public function busiestTime(): ?string
    {
        $counts = [];

        foreach (Booking::query()->completed()->select('appointment_time')->get() as $booking) {
            $time = (string) ($booking->appointment_time ?? '00:00');
            $hour = (int) substr($time, 0, 2);
            $counts[$hour] = ($counts[$hour] ?? 0) + 1;
        }

        if ($counts === []) {
            return null;
        }

        $bestHour = array_keys($counts, max($counts))[0];

        return Carbon::parse(sprintf('%02d:00', $bestHour))->format('g:i A');
    }

    /**
     * Weekly chart data (last 12 weeks).
     */
    public function weeklyChartData(Carbon $start, Carbon $end): array
    {
        $results = Booking::query()
            ->completed()
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('appointment_date, SUM(price) as total')
            ->groupBy('appointment_date')
            ->get()
            ->groupBy(fn ($r) => Carbon::parse($r->appointment_date)->startOfWeek()->format('M j'));

        $weeks = [];
        $current = $start->copy()->startOfWeek();

        while ($current->lte($end)) {
            $label = $current->format('M j');
            $weeks[] = [
                'label' => $label,
                'value' => $results->get($label, collect())->sum('total'),
            ];
            $current->addWeek();
        }

        return $weeks;
    }

    /**
     * Monthly chart data (last 12 months).
     */
    public function monthlyChartData(Carbon $start, Carbon $end): array
    {
        $results = [];

        foreach (
            Booking::query()
                ->completed()
                ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
                ->select('appointment_date', 'price')
                ->get() as $booking
        ) {
            $key = Carbon::parse($booking->appointment_date)->format('Y-m');
            $results[$key] = ($results[$key] ?? 0) + (int) $booking->price;
        }

        $months = [];
        $current = $start->copy()->startOfMonth();

        while ($current->lte($end)) {
            $key = $current->format('Y-m');
            $months[] = [
                'label' => $current->format('M Y'),
                'value' => $results[$key] ?? 0,
            ];
            $current->addMonth();
        }

        return $months;
    }

    /**
     * Yearly chart data (last 5 years).
     */
    public function yearlyChartData(Carbon $start, Carbon $end): array
    {
        $results = [];

        foreach (
            Booking::query()
                ->completed()
                ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
                ->select('appointment_date', 'price')
                ->get() as $booking
        ) {
            $key = Carbon::parse($booking->appointment_date)->format('Y');
            $results[$key] = ($results[$key] ?? 0) + (int) $booking->price;
        }

        $years = [];
        $current = $start->copy()->startOfYear();

        while ($current->lte($end)) {
            $key = $current->format('Y');
            $years[] = [
                'label' => $key,
                'value' => $results[$key] ?? 0,
            ];
            $current->addYear();
        }

        return $years;
    }

    /**
     * The dashboard's schedule blocks.
     */
    public function schedule(): array
    {
        $now = Carbon::now();

        return [
            'today' => Booking::query()
                ->with(['customer', 'service'])
                ->onDate($now)
                ->orderBy('appointment_time')
                ->get(),
            'upcoming' => Booking::query()
                ->with(['customer', 'service'])
                ->active()
                ->whereDate('appointment_date', '>=', $now->toDateString())
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->limit(5)
                ->get(),
            'recent' => Booking::query()
                ->with(['customer', 'service'])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ];
    }
}
