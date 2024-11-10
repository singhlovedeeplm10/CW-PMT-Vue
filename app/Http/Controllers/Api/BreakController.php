<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Breaks;
use App\Models\Attendance;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BreakController extends Controller
{
    public function storeBreak(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'attendance_id' => 'required|exists:attendances,id',
            'start_time' => 'required|date',
            'reason' => 'required|string|max:255',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        // Store break data
        $break = Breaks::create([
            'attendance_id' => $request->attendance_id,
            'start_time' => Carbon::parse($request->start_time),
            'reason' => $request->reason,
        ]);

        // Return response
        return response()->json([
            'success' => true,
            'message' => 'Break started successfully',
            'data' => $break
        ], 201);
    }
}
