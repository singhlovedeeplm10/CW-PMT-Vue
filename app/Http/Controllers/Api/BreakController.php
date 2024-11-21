<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Breaks;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BreakController extends Controller
{
    public function startBreak(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        // Check if a break is already in progress for the user
        $ongoingBreak = Breaks::where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        if ($ongoingBreak) {
            return response()->json([
                'message' => 'You already have an ongoing break.',
                'break' => $ongoingBreak,
            ], 400);
        }

        // Create a new break entry
        $break = Breaks::create([
            'user_id' => $user->id,
            'attendance_id' => $request->attendance_id,
            'start_time' => Carbon::now(),
            'reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Break started successfully.',
            'break' => $break,
        ], 201);
    }

    public function getOngoingBreak()
    {
        $user = auth()->user();

        // Get ongoing break
        $ongoingBreak = Breaks::where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        if (!$ongoingBreak) {
            return response()->json(['message' => 'No ongoing break found.'], 404);
        }

        return response()->json([
            'message' => 'Ongoing break retrieved successfully.',
            'break' => $ongoingBreak,
        ]);
    }
}
