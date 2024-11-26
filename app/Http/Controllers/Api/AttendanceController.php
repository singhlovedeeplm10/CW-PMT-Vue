<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Carbon;


class AttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        $user = Auth::user();
    
        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clockin_time' => Carbon::now(),
        ]);
    
        // Generate ClockinToken
        $clockinToken = $user->createToken('ClockinToken')->plainTextToken;
    
        return response()->json([
            'status' => 'Clocked in successfully',
            'attendance' => $attendance,
            'token' => $clockinToken, // Return the token
        ]);
    }
    
    
    public function clockOut(Request $request)
    {
        try {
            $userId = auth()->id();
    
            // Check if the user has an active clock-in record
            $attendance = Attendance::where('user_id', $userId)
                ->whereNull('clockout_time')
                ->first();
    
            if (!$attendance) {
                return response()->json(['message' => 'No active clock-in record found.'], 400);
            }
    
            // Calculate total task hours for the day
            $totalTaskHours = DB::table('daily_tasks')
                ->where('user_id', $userId)
                ->whereDate('created_at', today()) // Ensure we only check for today's tasks
                ->sum('hours');
    
            // Check if total task hours are exactly 8
            if ($totalTaskHours != 8) {
                return response()->json(['message' => 'You must complete exactly 8 hours of work before clocking out.'], 400);
            }
    
            // Proceed with clocking out if task hours are 8
            $clockOutTime = now();
            $productiveHours = $clockOutTime->diff($attendance->clockin_time)->format('%H:%I:%S');
    
            // Update the attendance record
            $attendance->update([
                'clockout_time' => $clockOutTime,
                'productive_hours' => $productiveHours,
            ]);
    
            // Calculate total weekly hours
            $weeklyHours = Attendance::where('user_id', $userId)
                ->whereBetween('clockin_time', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum(DB::raw("TIME_TO_SEC(productive_hours)"));
    
            return response()->json([
                'message' => 'Clocked out successfully.',
                'weekly_hours' => gmdate('H:i:s', $weeklyHours),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
        
    public function getWeeklyHours(Request $request)
    {
        $user = Auth::user();
    
        try {
            $weeklyHoursInSeconds = Attendance::where('user_id', $user->id)
                ->whereBetween('clockin_time', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->get()
                ->sum(function ($attendance) {
                    // Check if productive_hours is not null
                    if ($attendance->productive_hours) {
                        $time = explode(':', $attendance->productive_hours);
                        return ($time[0] * 3600) + ($time[1] * 60) + $time[2];
                    }
                    return 0; // Return 0 if productive_hours is null
                });
    
            return response()->json([
                'weekly_hours' => $weeklyHoursInSeconds, // Return total seconds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch weekly hours. Please try again later.',
            ], 500);
        }
    }

    public function checkClockInStatus(Request $request)
    {
        $user = $request->user(); // Retrieve the user from the token
    
        // Check if there's an active clock-in record for today
        $clockedInToday = Attendance::where('user_id', $user->id)
                                    ->whereDate('clockin_time', Carbon::today())
                                    ->exists();
    
        return response()->json(['clocked_in' => $clockedInToday]);
    }
    
    public function getTodayProductiveHours()
    {
        $user = Auth::user();
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
    
        try {
            $productiveHoursInSeconds = Attendance::where('user_id', $user->id)
                ->whereBetween('clockin_time', [$todayStart, $todayEnd])
                ->get()
                ->sum(function ($attendance) {
                    // Check if productive_hours is not null
                    if ($attendance->productive_hours) {
                        $time = explode(':', $attendance->productive_hours);
                        return ($time[0] * 3600) + ($time[1] * 60) + $time[2];
                    }
                    return 0; // Return 0 if productive_hours is null
                });
    
            return response()->json([
                'productive_hours' => $productiveHoursInSeconds, // Return total seconds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch today\'s productive hours. Please try again later.',
            ], 500);
        }
    }
    
    
}
