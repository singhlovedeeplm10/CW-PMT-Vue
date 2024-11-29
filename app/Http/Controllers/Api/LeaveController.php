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
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        \Log::info('Authenticated User:', [Auth::user()]);
    
        $request->validate([
            'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave',
            'half' => 'nullable|in:First Half,Second Half',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:255',
            'contact_during_leave' => 'required|string|max:15',
        ]);
    
        $leave = Leave::create([
            'user_id' => Auth::id(),
            'type_of_leave' => $request->type_of_leave,
            'half' => $request->type_of_leave === 'Half Day Leave' ? $request->half : null,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => 'pending',
            'contact_during_leave' => $request->contact_during_leave,
        ]);
    
        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'leave' => $leave,
        ]);
    }
        
    
}
