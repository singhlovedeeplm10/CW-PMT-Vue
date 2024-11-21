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
// use Carbon\Carbon;
use Illuminate\Support\Carbon;


class AttendanceController extends Controller
{
    public function storeAttendance(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'clockin_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        // Store attendance data
        $attendance = Attendance::create([
            'user_id' => Auth::id(),  // Assuming the user is authenticated
            'clockin_time' => Carbon::parse($request->clockin_time),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance clock-in recorded successfully',
            'data' => $attendance,
        ], 201);
    }

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
            $attendance = Attendance::where('user_id', $userId)->whereNull('clockout_time')->first();
    
            if (!$attendance) {
                return response()->json(['message' => 'No active clock-in record found.'], 400);
            }
    
            $clockOutTime = now();
            $productiveHours = $clockOutTime->diff($attendance->clockin_time)->format('%H:%I:%S');
    
            $attendance->update([
                'clockout_time' => $clockOutTime,
                'productive_hours' => $productiveHours,
            ]);
    
            // Optionally calculate weekly hours
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
    
}
