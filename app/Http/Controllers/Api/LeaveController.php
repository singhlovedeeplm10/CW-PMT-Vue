<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'type_of_leave' => 'required|in:Short Leave,Half Day,Full Day Leave',
            'half_day' => 'nullable|required_if:type_of_leave,Half Day|in:First Half,Second Half',
            'start_date' => 'nullable|required_if:type_of_leave,Short Leave,Half Day,Full Day Leave|date',
            'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave|date|after_or_equal:start_date',
            'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
            'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
            'reason' => 'required|string',
            'contact_during_leave' => 'required|string|max:15',
        ]);
    
        try {
            // Save leave data in the database
            $leave = Leave::create([
                'user_id' => Auth::id(),
                'type_of_leave' => $validatedData['type_of_leave'],
                'half' => $validatedData['half_day'] ?? null,
                'start_date' => $validatedData['start_date'] ?? null,
                'end_date' => $validatedData['end_date'] ?? null,
                'start_time' => $validatedData['start_time'] ?? null,
                'end_time' => $validatedData['end_time'] ?? null,
                'reason' => $validatedData['reason'],
                'contact_during_leave' => $validatedData['contact_during_leave'],
                'status' => 'pending', // Default status
                'last_updated_by' => Auth::user()->name, // Storing the name of the user who created the leave
            ]);
    
            // Respond with success
            return response()->json(['message' => 'Leave application submitted successfully!'], 201);
    
        } catch (\Exception $e) {
            // Handle error and return response
            return response()->json(['error' => 'Failed to submit leave application.'], 500);
        }
    }
    

    public function showLeaves(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
    
        // Initialize the query
        $query = Leave::where('user_id', $user->id);
    
        // Apply search filters if provided
        if ($request->filled('type')) {
            $query->where('type_of_leave', 'like', '%' . $request->type . '%');
        }
    
        if ($request->filled('duration')) {
            // Implement your logic for filtering by duration
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('created_date')) {
            $query->whereDate('created_at', $request->created_date);
        }
    
        // Fetch the leaves
        $leaves = $query->orderBy('created_at', 'asc')->get();
    
        // Transform the data to match the desired format
        $formattedLeaves = $leaves->map(function ($leave) {
            // Calculate duration
            $duration = $this->calculateDuration($leave);
    
// Determine icon based on reason
$reasonIcons = [
    'Birthday' => '<i class="fas fa-gift" style="color: #ff69b4;"></i>',
    'Festival' => '<i class="fas fa-gift" style="color: #ff4500;"></i>',
    'Party' => '<i class="fas fa-gift" style="color: #ffa500;"></i>',
    'Function' => '<i class="fas fa-gift" style="color: #ff6347;"></i>',
    'Wedding Function' => '<i class="fas fa-gift" style="color: #ff6347;"></i>',
];
$icon = $reasonIcons[$leave->reason] ?? '<i class="fas fa-briefcase" style="color: #4682b4;"></i>';

// Format the Type field with the icon
$typeFormatted = $icon . ' ' . $leave->type_of_leave . ' (' . \Carbon\Carbon::parse($leave->start_date)->format('F d, Y') . ')';

// Format the week of the day (e.g., "Monday to Friday")
$startDayOfWeek = \Carbon\Carbon::parse($leave->start_date)->format('l'); // Day of the week for start_date
$endDayOfWeek = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('l') : $startDayOfWeek; // Day of the week for end_date

// Append the week range to the formatted type
$typeFormatted .= ' (' . $startDayOfWeek . ' to ' . $endDayOfWeek . ')';

// Append the time range in IST if start_time and end_time exist
if ($leave->start_time && $leave->end_time) {
    $startTimeIST = \Carbon\Carbon::parse($leave->start_time)->timezone('Asia/Kolkata')->format('g:i A'); // Format as 12-hour time
    $endTimeIST = \Carbon\Carbon::parse($leave->end_time)->timezone('Asia/Kolkata')->format('g:i A'); // Format as 12-hour time
    $typeFormatted .= " (from $startTimeIST to $endTimeIST)";
}

    
            return [
                'id' => $leave->id,
                'type' => $typeFormatted,
                'duration' => $duration,
                'status' => ucfirst($leave->status),
                'created_at' => $leave->created_at->format('F d, Y'),   
                'updated_by' => $leave->user ? $leave->user->name . ' ': 'Unknown',

            ];
        });
    
        return response()->json([
            'success' => true,
            'data' => $formattedLeaves,
        ]);
    }    
    

    /**
     * Calculate the duration of the leave.
     *
     * @param Leave $leave
     * @return string
     */
    private function calculateDuration(Leave $leave)
    {
        // Example logic to calculate duration
        if ($leave->type_of_leave === 'Full Day Leave') {
            $start = $leave->start_date;
            $end = $leave->end_date;
            $days = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;
            return $days . ' day(s)';
        } elseif ($leave->type_of_leave === 'Half Day Leave') {
            return $leave->half; // 'First Half' or 'Second Half'
        } elseif ($leave->type_of_leave === 'Short Leave') {
            // Calculate time difference
            $start = \Carbon\Carbon::parse($leave->start_time);
            $end = \Carbon\Carbon::parse($leave->end_time);
            $duration = $start->diff($end)->format('%H:%I hours');
            return $duration;
        }

        return '';
    }

    public function update(Request $request, Leave $leave)
    {
        try {
            // Log the incoming request data for debugging
            \Log::info('Incoming Request Data:', $request->all());
    
            // Ensure time fields are in the correct format
            if ($request->has('start_time')) {
                $request->merge([
                    'start_time' => date('H:i', strtotime($request->start_time)),
                ]);
            }
    
            if ($request->has('end_time')) {
                $request->merge([
                    'end_time' => date('H:i', strtotime($request->end_time)),
                ]);
            }
    
            // Validate the input
            $validatedData = $request->validate([
                'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave',
                'half_day' => 'nullable|required_if:type_of_leave,Half Day Leave|in:first_half,second_half',
                'start_date' => 'nullable|required_if:type_of_leave,Full Day Leave|date',
                'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave|date|after_or_equal:start_date',
                'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
                'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
                'reason' => 'required|string|max:255',
                'contact_during_leave' => 'required|string|max:15',
                'status' => 'required|in:pending,approved,disapproved,hold,canceled',
            ]);
    
            // Update the leave record
            $leave->update([
                'type_of_leave' => $validatedData['type_of_leave'],
                'half_day' => $validatedData['half_day'] ?? null,
                'start_date' => $validatedData['start_date'] ?? null,
                'end_date' => $validatedData['end_date'] ?? null,
                'start_time' => $validatedData['start_time'] ?? null,
                'end_time' => $validatedData['end_time'] ?? null,
                'reason' => $validatedData['reason'],
                'contact_during_leave' => $validatedData['contact_during_leave'],
                'status' => $validatedData['status'],
                'last_updated_by' => $request->user()->name,
            ]);
    
            return response()->json([
                'message' => 'Leave application updated successfully!',
                'leave' => $leave,
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating leave:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while updating leave.'], 500);
        }
    }
      

    public function updateTeamLeave(Request $request, Leave $leave)
    {
try {
    $validatedData = $request->validate([
        'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave',
        'half' => 'nullable|required_if:type_of_leave,Half Day Leave|in:First Half,Second Half',
        'start_date' => 'nullable|required_if:type_of_leave,Full Day Leave|date',
        'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave|date|after_or_equal:start_date',
        'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
        'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
        'reason' => 'required|string',
        'contact_during_leave' => 'required|string|max:15',
        'status' => 'required|in:pending,approved,disapproved,hold,canceled',
    ]);
    
            // Update the leave record
            $leave->update([
                'type_of_leave' => $validatedData['type_of_leave'],
                'half' => $validatedData['half'] ?? null,
                'start_date' => $validatedData['start_date'] ?? null,
                'end_date' => $validatedData['end_date'] ?? null,
                'start_time' => $validatedData['start_time'] ?? null,
                'end_time' => $validatedData['end_time'] ?? null,
                'reason' => $validatedData['reason'],
                'contact_during_leave' => $validatedData['contact_during_leave'],
                'status' => $validatedData['status'], // Update the status
                'last_updated_by' => Auth::user()->name,
            ]);
    
            // Respond with success
            return response()->json(['message' => 'Leave application updated successfully!', 'leave' => $leave], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            return response()->json(['error' => $e->errors()], 422);
        }
    }
    
    
    

public function search(Request $request)
{
    $searchTerm = $request->input('query', '');
    $users = User::where('name', 'LIKE', "$searchTerm%")
                ->get(['id', 'name']); // Adjust the fields as needed

    return response()->json($users);
}

public function teamLeave(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave',
            'half' => 'nullable|in:First Half,Second Half',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'required|string',
            'contact_during_leave' => 'required|string|max:15',
            'selected_user' => 'required|exists:users,id', // Make sure the selected user exists in the 'users' table
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create the leave record
        $leave = Leave::create([
            'user_id' => $request->selected_user, // Store the selected user's ID
            'type_of_leave' => $request->type_of_leave,
            'half' => $request->half,  // Store the half value if applicable
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'contact_during_leave' => $request->contact_during_leave,
            'last_updated_by' => Auth::user()->name, // Save the name of the authenticated user
            'status' => 'pending', // Default status for new leave request
        ]);

        return response()->json(['message' => 'Leave applied successfully!', 'data' => $leave], 201);
    }
    
    public function show($id)
    {
        // Fetch leave by ID
        $leave = Leave::find($id);
    
        if (!$leave) {
            return response()->json([
                'success' => false,
                'message' => 'Leave not found.',
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'data' => $leave,
        ]);
    }

    public function showteamLeaves(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
    
        // Initialize the query
        $query = Leave::query();
    
        // Apply search filters if provided
        if ($request->filled('type')) {
            $query->where('type_of_leave', 'like', '%' . $request->type . '%');
        }
    
        if ($request->filled('duration')) {
            // Implement your logic for filtering by duration
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('created_date')) {
            $query->whereDate('created_at', $request->created_date);
        }
    
        // Join with users table to get employee name
        $query->join('users', 'leaves.user_id', '=', 'users.id')
            ->select(
                'leaves.*',
                'users.name as employee_name' // Assuming 'name' is the column for user's name
            );
    
        // Fetch the leaves
        $leaves = $query->orderBy('created_at', 'asc')->get();
    
        // Transform the data to match the desired format
        $formattedLeaves = $leaves->map(function ($leave) {
            // Calculate duration
            $duration = $this->calculateDuration($leave);
    
            // Determine icon based on reason
            $reasonIcons = [
                'Birthday' => '<i class="fas fa-gift" style="color: #ff69b4;"></i>',
                'Festival' => '<i class="fas fa-gift" style="color: #ff4500;"></i>',
                'Party' => '<i class="fas fa-gift" style="color: #ffa500;"></i>',
                'Function' => '<i class="fas fa-gift" style="color: #ff6347;"></i>',
                'Wedding Function' => '<i class="fas fa-gift" style="color: #ff6347;"></i>',
            ];
            $icon = $reasonIcons[$leave->reason] ?? '<i class="fas fa-briefcase" style="color: #4682b4;"></i>';
    
            // Format the Type field with the icon
            $typeFormatted = $icon . ' ' . $leave->type_of_leave . ' (' . \Carbon\Carbon::parse($leave->start_date)->format('F d, Y') . ')';
    
            // Format the week of the day (e.g., "Monday to Friday")
            $startDayOfWeek = \Carbon\Carbon::parse($leave->start_date)->format('l'); // Day of the week for start_date
            $endDayOfWeek = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('l') : $startDayOfWeek; // Day of the week for end_date
    
            // Append the week range to the formatted type
            $typeFormatted .= ' (' . $startDayOfWeek . ' to ' . $endDayOfWeek . ')';
    
            // Append the time range in IST if start_time and end_time exist
            if ($leave->start_time && $leave->end_time) {
                $startTimeIST = \Carbon\Carbon::parse($leave->start_time)->timezone('Asia/Kolkata')->format('g:i A'); // Format as 12-hour time
                $endTimeIST = \Carbon\Carbon::parse($leave->end_time)->timezone('Asia/Kolkata')->format('g:i A'); // Format as 12-hour time
                $typeFormatted .= " (from $startTimeIST to $endTimeIST)";
            }
    
            return [
                'id' => $leave->id,
                'employee_name' => $leave->employee_name, // Employee name from the joined table
                'type' => $typeFormatted,
                'duration' => $duration,
                'status' => ucfirst($leave->status),
                'created_at' => $leave->created_at->format('F d, Y'),
                'updated_by' => $leave->user ? $leave->user->name : 'Unknown',
            ];
        });
    
        return response()->json([
            'success' => true,
            'data' => $formattedLeaves,
        ]);
    }    

    public function getUsersLeave()
{
    $statuses = ['pending', 'approved', 'disapproved', 'hold', 'canceled']; // Define all statuses

    $usersOnLeave = Leave::whereIn('status', $statuses) // Include all statuses
        ->with('user:id,name') // Load user details (name and ID)
        ->get()
        ->map(function ($leave) {
            return [
                'id' => $leave->user->id,
                'name' => $leave->user->name,
                'status' => $leave->status, // Include the leave status in the response
            ];
        });

    return response()->json($usersOnLeave);
}

public function getUsersOnLeave(Request $request)
{
    $selectedDate = $request->query('date'); // Get the selected date from the request

    // Query users on leave where the selected date falls within the start_date and end_date range
    $usersOnLeave = Leave::whereDate('start_date', '<=', $selectedDate)
        ->whereDate('end_date', '>=', $selectedDate)
        ->with('user:id,name') // Load user details
        ->get()
        ->map(function ($leave) {
            return [
                'id' => $leave->user->id,
                'name' => $leave->user->name,
            ];
        });

    return response()->json($usersOnLeave);
}
}
