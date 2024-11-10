<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
        
        // Check if the user already has an open clock-in
        $attendance = Attendance::where('user_id', $user->id)
                                ->whereNull('clockout_time')
                                ->first();

        if (!$attendance) {
            // Clock in and save the current time
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'clockin_time' => Carbon::now(),
            ]);

            return response()->json(['status' => 'Clocked in successfully', 'attendance' => $attendance]);
        }

        return response()->json(['status' => 'Already clocked in'], 400);
    }

    
    
}
