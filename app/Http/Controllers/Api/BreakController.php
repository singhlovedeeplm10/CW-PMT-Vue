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
        // Validate the incoming request
        $validatedData = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return response()->json(['error' => 'User is not authenticated.'], 401);
        }

        // Retrieve the latest attendance for the user
        $attendance = $user->attendances()->latest()->first();

        // Ensure the user has an active attendance record
        if (!$attendance) {
            return response()->json(['error' => 'No active attendance found.'], 400);
        }

        // Create a new break entry
        $break = Breaks::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'start_time' => now(),
            'reason' => $validatedData['reason'],
        ]);

        return response()->json([
            'message' => 'Break started successfully.',
            'break' => $break,
        ], 201);
    }

    public function endBreak(Request $request)
    {
        $validated = $request->validate([
            'break_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date',
        ]);

        $break = Breaks::where('user_id', Auth::id())
            ->whereNull('end_time')
            ->latest()
            ->first();

        if ($break) {
            $break->update([
                'end_time' => $validated['end_time'],
                'break_time' => $validated['break_time'],
            ]);
        }

        return response()->json([
            'message' => 'Break ended successfully',
            'data' => $break
        ]);
    }

}
