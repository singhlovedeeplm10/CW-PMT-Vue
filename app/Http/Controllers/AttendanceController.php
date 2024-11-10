<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Auth;

class AttendanceController extends Controller
{
    public function ClockIn(Request $request)
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
                                ->whereNull('clockout_time')
                                ->first();
    
        if ($attendance) {
            // Clock out if there's an open attendance record
            $attendance->clockout_time = Carbon::now();
            $attendance->productive_hours = $attendance->clockout_time->diff($attendance->clockin_time);
            $attendance->save();
            
            return response()->json([
                'status' => 'clocked_out',
                'clockout_time' => $attendance->clockout_time->format('H:i:s')
            ]);
        } else {
            // Clock in if no open attendance record
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'clockin_time' => Carbon::now()
            ]);
            
            return response()->json([
                'status' => 'clocked_in',
                'clockin_time' => $attendance->clockin_time->format('H:i:s')
            ]);
        }
    }
}