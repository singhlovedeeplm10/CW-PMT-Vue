<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Leave;
use App\Models\Breaks;
use App\Models\DailyTask;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class AttendanceController extends Controller
{
    public function autoClockOut()
{
    // Get current time in Indian Standard Time (IST)
    $currentTime = Carbon::now('Asia/Kolkata')->toDateString(); // Get only date

    // Find users with null clockout_time and productive_hours
    $users = DB::table('attendances')
        ->whereNull('clockout_time')
        ->whereNull('productive_hours')
        ->get();

    if ($users->isEmpty()) {
        return response()->json(['message' => 'No users found to clock out.']);
    }

    // Update the records
    foreach ($users as $user) {
        // Get the corresponding ClockinToken
        $clockinToken = DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->user_id)
            ->where('name', 'ClockinToken')
            ->first();

        // Remove breaks for the same user on the same day
        DB::table('breaks')
            ->where('user_id', $user->user_id)
            ->whereDate('start_time', $currentTime)
            ->delete();

        // Remove BreakinToken
        DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->user_id)
            ->where('name', 'BreakinToken')
            ->delete();

        // Update attendance
        if ($clockinToken) {
            DB::table('attendances')
                ->where('id', $user->id)
                ->update([
                    'clockout_time' => $user->clockin_time, // Same as clock-in time
                    'productive_hours' => '00:00:00',
                    'updated_at' => now(),
                ]);

            // Remove the ClockinToken
            DB::table('personal_access_tokens')
                ->where('id', $clockinToken->id)
                ->delete();
        }
    }

    return response()->json(['message' => 'User clock-out successfully, and related break records removed.']);
}

    
    public function clockIn(Request $request)
{
    $user = Auth::user();

    // Create attendance record with IST timezone
    $clockinTime = Carbon::now()->setTimezone('Asia/Kolkata');
    $attendance = Attendance::create([
        'user_id' => $user->id,
        'clockin_time' => $clockinTime,
    ]);

    // Check for approved leave on the same day
    $today = Carbon::now()->setTimezone('Asia/Kolkata')->toDateString();
    $leave = Leave::where('user_id', $user->id)
        ->where('status', 'approved')
        ->where('start_date', $today)
        ->whereIn('type_of_leave', ['Short Leave', 'Half Day Leave']) // Only consider these two types
        ->first();

    if ($leave) {
        // Check if a daily task for the leave already exists
        $existingTask = DailyTask::where('user_id', $user->id)
            ->where('leave_id', $leave->id)
            ->whereDate('created_at', $today)
            ->exists();

        if (!$existingTask) {
            // Assign hours based on leave type
            $hours = ($leave->type_of_leave == 'Half Day Leave') ? 4 : 2;

            // Create a task for the leave
            DailyTask::create([
                'user_id' => $leave->user_id,
                'attendance_id' => $attendance->id,
                'project_id' => null,
                'project_name' => $leave->type_of_leave, // Leave type
                'leave_id' => $leave->id,
                'task_description' => $leave->reason, // Reason as description
                'hours' => $hours,
                'task_status' => 'pending', // Default status
            ]);
        }
    }

    // Generate ClockinToken
    $clockinToken = $user->createToken('ClockinToken')->plainTextToken;

    return response()->json([
        'status' => 'Clocked in successfully',
        'attendance' => $attendance,
        'token' => $clockinToken,
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
        // Get the current week's range (Monday to Sunday)
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        // Calculate total productive hours for the week
        $weeklyProductiveSeconds = Attendance::where('user_id', $user->id)
            ->whereBetween('clockin_time', [$startOfWeek, $endOfWeek])
            ->whereNotNull('productive_hours')
            ->get()
            ->sum(function ($attendance) {
                $time = explode(':', $attendance->productive_hours);
                return ($time[0] * 3600) + ($time[1] * 60) + $time[2];
            });

        // Calculate total break time for the week
        $weeklyBreakSeconds = Breaks::where('user_id', $user->id)
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->whereNotNull('break_time')
            ->get()
            ->sum(function ($break) {
                $time = explode(':', $break->break_time);
                return ($time[0] * 3600) + ($time[1] * 60) + $time[2];
            });

        // Subtract total break time from total productive hours
        $weeklyNetProductiveSeconds = max(0, $weeklyProductiveSeconds - $weeklyBreakSeconds);

        return response()->json([
            'weekly_hours' => $weeklyNetProductiveSeconds, // in seconds
            'productive_hours' => $weeklyProductiveSeconds, // in seconds
            'break_hours' => $weeklyBreakSeconds, // in seconds
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch weekly hours. Please try again later.',
            'details' => $e->getMessage()
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
