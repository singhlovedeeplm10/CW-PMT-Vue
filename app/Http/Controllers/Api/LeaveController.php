<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function addLeave(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'type_of_leave' => 'required|in:Short Leave,Half Day,Full Day',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'leave_time' => 'nullable|date_format:H:i',
            'reason' => 'required|string',
            'contact_during_leave' => 'required|string|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create a new leave entry
        $leave = Leave::create([
            'user_id' => Auth::id(),
            'type_of_leave' => $request->type_of_leave,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'leave_time' => $request->leave_time,
            'reason' => $request->reason,
            'contact_during_leave' => $request->contact_during_leave,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Leave request created successfully', 'leave' => $leave], 201);
    }
}
