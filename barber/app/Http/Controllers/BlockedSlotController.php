<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use App\Models\BusinessSetting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockedSlotController extends Controller
{
    /**
     * Mark the barber on leave for a window on the schedule.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ]);

        if (BlockedSlot::where('date', $data['date'])->exists()) {
            return response()->json(['message' => 'This day is already blocked.'], 422);
        }

        [$start, $end] = $this->resolveWindow($data['date'], $data['start_time'] ?? null, $data['end_time'] ?? null);

        if ($start && $end && $end <= $start) {
            return response()->json(['message' => 'The leave end time must be after the start time.'], 422);
        }

        BlockedSlot::create([
            'date' => $data['date'],
            'reason' => $data['reason'] ?? 'Barber on leave',
            'start_time' => $start,
            'end_time' => $end,
        ]);

        return response()->json(['message' => 'Schedule blocked — Barber on leave.']);
    }

    /**
     * Move a barber's leave to a new window on the same day.
     */
    public function update(Request $request, BlockedSlot $blockedSlot): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
        ]);

        [$start, $end] = $this->resolveWindow(
            $blockedSlot->date->toDateString(),
            $data['start_time'] ?? null,
            $data['end_time'] ?? null,
        );

        if ($start && $end && $end <= $start) {
            return response()->json(['message' => 'The leave end time must be after the start time.'], 422);
        }

        $blockedSlot->update([
            'reason' => $data['reason'] ?? $blockedSlot->reason,
            'start_time' => $start,
            'end_time' => $end,
        ]);

        return response()->json(['message' => 'Leave updated.']);
    }

    /**
     * Cancel a barber's leave, freeing the window for bookings again.
     */
    public function destroy(BlockedSlot $blockedSlot): JsonResponse
    {
        $blockedSlot->delete();

        return response()->json(['message' => 'Leave cancelled.']);
    }

    /**
     * Resolve the leave window, falling back to the day's operating hours so a
     * leave always carries a start and end time.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveWindow(string $date, ?string $start, ?string $end): array
    {
        if ($start && $end) {
            return [$start, $end];
        }

        $hours = BusinessSetting::get('operating_hours', []);
        $day = strtolower(Carbon::parse($date)->englishDayOfWeek);

        $start = $start ?: ($hours[$day]['open'] ?? null);
        $end = $end ?: ($hours[$day]['close'] ?? null);

        return [$start, $end];
    }
}
