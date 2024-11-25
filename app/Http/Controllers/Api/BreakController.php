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
        // Ensure the user is authenticated
        $user = auth()->user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
    
        // Validate the input
        $request->validate([
            'break_time' => 'required|integer|min:1', // Ensure break_time is validated
        ]);
    
        // Retrieve the latest break for the user
        $break = $user->breaks()->latest()->first();
    
        if (!$break) {
            return response()->json(['error' => 'No active break found.'], 404);
        }
    
        // Convert break_time from seconds to HH:MM:SS format
        $breakTime = gmdate('H:i:s', $request->input('break_time'));
    
        // Update the break with end time and break_time
        $break->update([
            'end_time' => now(),
            'break_time' => $breakTime, // Save break_time in HH:MM:SS format
        ]);
    
        return response()->json(['message' => 'Break ended successfully.'], 200);
    }
}
