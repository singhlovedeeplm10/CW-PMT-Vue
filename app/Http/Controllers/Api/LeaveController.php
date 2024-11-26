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
        try {
            \Log::info('Store method called with data:', $request->all());
    
            // Validate and process the request
            $validated = $request->validate([
                'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'half_day' => 'nullable|in:First Half,Second Half',
                'start_time' => 'nullable|required_if:type_of_leave,Short Leave,Half Day|date_format:H:i',
                'end_time' => 'nullable|required_if:type_of_leave,Short Leave,Half Day|date_format:H:i|after:start_time',
                'reason' => 'required|string',
                'contact_during_leave' => 'required|string|max:15',
            ]);
    
            $user = Auth::user();
            if (!$user) {
                throw new \Exception('User not authenticated.');
            }
    
            $leaveData = [
                'user_id' => $user->id,
                'type_of_leave' => $validated['type_of_leave'],
                'half' => $validated['type_of_leave'] === 'Half Day' ? $validated['half_day'] : null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'reason' => $validated['reason'],
                'contact_during_leave' => $validated['contact_during_leave'],
                'status' => 'pending',
                'last_updated_by' => $user->name,
            ];
    
            \Log::info('Prepared leave data:', $leaveData);
    
            $leave = Leave::create($leaveData);
    
            \Log::info('Leave created successfully:', $leave->toArray());
    
            return response()->json([
                'message' => 'Leave applied successfully!',
                'leave' => $leave,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error in store method:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred. Please try again.'], 500);
        }
    } 
}
