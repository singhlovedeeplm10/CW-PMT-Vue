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
use Illuminate\Support\Facades\Log;

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

public function getUserBreaks(Request $request)
{
    try {
        $userId = $request->user()->id; // Ensure the user is authenticated
        Log::info("Fetching breaks for user ID: " . $userId);

        $breaks = Breaks::where('user_id', $userId)
            ->select('start_time', 'break_time', 'reason') // Fetch only required fields
            ->get()
            ->map(function ($break) {
                // Calculate "Break Out" time
                $breakOutTime = \Carbon\Carbon::parse($break->start_time)
                    ->addSeconds($this->convertBreakTimeToSeconds($break->break_time));

                return [
                    'start_time' => \Carbon\Carbon::parse($break->start_time)->format('h:i A'),
                    'break_out' => $breakOutTime->format('h:i A'), // Calculated Break Out time
                    'break_time' => gmdate('H:i:s', $this->convertBreakTimeToSeconds($break->break_time)), // Format in hours, minutes, seconds
                    'reason' => $break->reason,
                ];
            });

        return response()->json($breaks);
    } catch (\Exception $e) {
        Log::error("Error fetching breaks: " . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch breaks'], 500);
    }
}

/**
 * Convert break time to seconds.
 * Handles cases where break_time might be stored in different formats.
 *
 * @param string|int $breakTime
 * @return int
 */
private function convertBreakTimeToSeconds($breakTime)
{
    // If break_time is already an integer (e.g., stored as seconds)
    if (is_numeric($breakTime)) {
        return (int) $breakTime;
    }

    // If break_time is stored in H:i:s format
    try {
        $timeParts = explode(':', $breakTime);
        return ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2];
    } catch (\Exception $e) {
        Log::error("Invalid break_time format: " . $breakTime);
        return 0; // Default to 0 seconds if parsing fails
    }
}


}
