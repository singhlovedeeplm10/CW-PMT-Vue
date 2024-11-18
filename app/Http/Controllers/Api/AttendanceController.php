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
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'clockin_time' => Carbon::now(),
        ]);
    
        return response()->json([
            'status' => 'Clocked in successfully',
            'attendance' => $attendance,
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
        $weeklyHoursInSeconds = Attendance::where('user_id', $user->id)
            ->whereBetween('clockin_time', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->get()
            ->sum(function ($attendance) {
                // Convert time format to seconds
                $time = explode(':', $attendance->productive_hours);
                return ($time[0] * 3600) + ($time[1] * 60) + $time[2];
            });
    
        return response()->json([
            'weekly_hours' => $weeklyHoursInSeconds, // Return total seconds
        ]);
    }
}
