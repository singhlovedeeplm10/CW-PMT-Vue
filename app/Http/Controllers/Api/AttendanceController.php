<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class AttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        $user = Auth::user();
        
        // Create attendance record with IST timezone
        $clockinTime = Carbon::now()->setTimezone('Asia/Kolkata');
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clockin_time' => $clockinTime,
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
                ->whereDate('created_at', today('Asia/Kolkata')) // Use IST
                ->sum('hours');
    
            // Check if total task hours are exactly 8
            if ($totalTaskHours != 8) {
                return response()->json(['message' => 'You must complete exactly 8 hours of work before clocking out.'], 400);
            }
    
            // Proceed with clocking out
            $clockOutTime = Carbon::now()->setTimezone('Asia/Kolkata');
            $productiveHours = $clockOutTime->diff($attendance->clockin_time)->format('%H:%I:%S');
    
            // Update the attendance record
            $attendance->update([
                'clockout_time' => $clockOutTime,
                'productive_hours' => $productiveHours,
            ]);
    
            // Revoke the ClockinToken
            $user = auth()->user();
            $user->tokens()->where('name', 'ClockinToken')->delete();
    
            // Calculate today's productive hours
            $todayStart = now()->startOfDay()->setTimezone('Asia/Kolkata');
            $todayEnd = now()->endOfDay()->setTimezone('Asia/Kolkata');
    
            $productiveHoursInSeconds = Attendance::where('user_id', $userId)
                ->whereBetween('clockin_time', [$todayStart, $todayEnd])
                ->get()
                ->sum(function ($attendance) {
                    if ($attendance->productive_hours) {
                        $time = explode(':', $attendance->productive_hours);
                        return ($time[0] * 3600) + ($time[1] * 60) + $time[2];
                    }
                    return 0;
                });
    
            return response()->json([
                'message' => 'Clocked out successfully. ClockinToken has been revoked.',
                'today_productive_hours' => $productiveHoursInSeconds, // Return updated productive hours in seconds
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    public function checkClockInStatus(Request $request)
    {
        $user = $request->user(); // Retrieve the authenticated user
    
        // Get the user's active ClockinToken from the personal_access_tokens table
        $clockinToken = DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->id)
            ->where('name', 'ClockinToken') // Check if the token is a ClockinToken
            ->orderBy('created_at', 'desc') // Get the latest token
            ->first();
    
        // Check if the user has a valid ClockinToken
        if (!$clockinToken) {
            return response()->json(['clocked_in' => false]); // Respond with clocked_in: false
        }
    
        // Check if there's an active attendance record for today
        $clockedInToday = Attendance::where('user_id', $user->id)
            ->whereDate('clockin_time', Carbon::today())
            ->exists();
    
        return response()->json(['clocked_in' => $clockedInToday]);
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

    public function getDailyHours(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        try {
            $dailyHoursInSeconds = Attendance::where('user_id', $user->id)
                ->whereDate('clockin_time', Carbon::today())
                ->get()
                ->sum(function ($attendance) {
                    if ($attendance->productive_hours) {
                        $time = explode(':', $attendance->productive_hours);
                        return ($time[0] * 3600) + ($time[1] * 60) + $time[2];
                    }
                    return 0;
                });
    
            return response()->json([
                'daily_hours' => $dailyHoursInSeconds,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch daily hours. Please try again later.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }    

    public function getClockinToken()
    {
        $user = Auth::user();
    
        // Check if the user has a ClockinToken in the `personal_access_tokens` table
        $token = $user->tokens()->where('name', 'ClockinToken')->first();
    
        if ($token) {
            // Fetch clock-in time from attendance
            $attendance = Attendance::where('user_id', $user->id)->latest('clockin_time')->first();
            $isOnBreak = false; // Add logic to check if user is on a break
            $breakStartTime = null; // Fetch break start time if applicable
            $totalBreakTime = 0; // Fetch total break time if applicable
    
            return response()->json([
                'isClockedIn' => true,
                'clockInTime' => $attendance->clockin_time,
                'isOnBreak' => $isOnBreak,
                'breakStartTime' => $breakStartTime,
                'totalBreakTime' => $totalBreakTime,
            ]);
        }
    
        return response()->json(['isClockedIn' => false]);
    }    

    
}
