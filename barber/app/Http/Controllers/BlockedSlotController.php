<?php

namespace App\Http\Controllers;

use App\Models\BlockedSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockedSlotController extends Controller
{
    /**
     * Block an entire day on the schedule (e.g. "Barber on leave").
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if (BlockedSlot::where('date', $data['date'])->exists()) {
            return response()->json(['message' => 'This day is already blocked.'], 422);
        }

        BlockedSlot::create([
            'date' => $data['date'],
            'reason' => $data['reason'] ?? 'Barber on leave',
        ]);

        return response()->json(['message' => 'Schedule blocked — Barber on leave.']);
    }
}
