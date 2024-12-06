<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Breaks;
use App\Models\Attendance;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

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
    
    public function getDailyBreaks(Request $request)
    {
        // Fetch the authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        try {
            // Calculate total break time in seconds for the authenticated user for today
            $totalBreakTimeInSeconds = Breaks::where('user_id', $user->id)
                ->whereDate('created_at', Carbon::today())  // Filter by today's date
                ->get()
                ->sum(function ($break) {
                    // Ensure the duration is in 'HH:MM:SS' format
                    if ($break->break_time) {
                        $timeParts = explode(':', $break->break_time);
                        // Return the total time in seconds
                        if (count($timeParts) == 3) {
                            return ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2];
                        }
                    }
                    return 0; // If no valid duration, return 0
                });
    
            return response()->json([
                'total_break_time' => $totalBreakTimeInSeconds,
            ]);
        } catch (\Exception $e) {
            // Return error response with message and error context
            return response()->json([
                'error' => 'Failed to fetch total break time. Please try again later.',
                'message' => $e->getMessage(),  // Add the exception message for debugging
            ], 500);
        }
    }

    public function getWeeklyBreakTime(Request $request)
{
    $user = Auth::user();

    try {
        $weeklyBreakTimeInSeconds = Breaks::where('user_id', $user->id)
            ->whereHas('attendance', function ($query) {
                $query->whereBetween('clockin_time', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            })
            ->get()
            ->sum(function ($break) {
                if ($break->break_time) {
                    $time = explode(':', $break->break_time);
                    return ($time[0] * 3600) + ($time[1] * 60) + $time[2];
                }
                return 0; // Return 0 if break_time is null
            });

        return response()->json([
            'weekly_break_time' => $weeklyBreakTimeInSeconds, // Return total seconds
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch weekly break time. Please try again later.',
        ], 500);
    }
}

public function getBreakEntries(Request $request)
{
    $date = $request->query('date');
    $breakEntries = Breaks::with('user')
        ->whereDate('break_time', $date)
        ->get();
    return response()->json($breakEntries);
}


}
