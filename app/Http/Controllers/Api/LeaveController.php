<?php

namespace App\Http\Controllers\Api;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leave;
use App\Mail\LeaveApplicationMail;
use App\Models\User;
use App\Models\DailyTask; // Import the DailyTask model
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Mail\LeaveStatusMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{

    public function store(Request $request)
{
    // Validate the request data
    $validatedData = $request->validate([
        'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home',
        'half_day' => 'nullable|required_if:type_of_leave,Half Day Leave|in:First Half,Second Half',
        'start_date' => 'nullable|required_if:type_of_leave,Short Leave,Half Day Leave,Full Day Leave|date',
        'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave|date|after_or_equal:start_date',
        'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
        'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
        'reason' => 'required|string',
        'contact_during_leave' => 'required|string|max:50',
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
            'last_updated_by' => Auth::user()->name,
        ]);

        // Retrieve admin user details (update as per your logic)
        $adminUser = User::where('email', 'adbbharat@gmail.com')->first();
        $adminName = $adminUser ? $adminUser->name : 'Admin';

        // Prepare leave details for the email
        $leaveDetails = [
            'user_name' => Auth::user()->name,
            'type_of_leave' => $leave->type_of_leave,
            'reason' => $leave->reason,
            'start_date' => $leave->start_date,
            'end_date' => $leave->end_date,
            'start_time' => $leave->start_time,
            'end_time' => $leave->end_time,
            'contact_during_leave' => $leave->contact_during_leave,
            'admin_name' => $adminName, // Include admin name
        ];

        // Send email to the admin
        Mail::to('adbbharat@gmail.com')->send(new LeaveApplicationMail($leaveDetails));

        // Respond with success
        return response()->json(['message' => 'Leave application submitted successfully!'], 201);

    } catch (\Exception $e) {
        Log::error('Leave submission failed: ' . $e->getMessage());
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

        // Return the formatted leave data including last_updated_by
        return [
            'id' => $leave->id,
            'type' => $typeFormatted,
            'half' => $leave->half,  // Include the half data here
            'duration' => $duration,
            'status' => ucfirst($leave->status),
            'created_at' => $leave->created_at->format('F d, Y'),
            'updated_by' => $leave->last_updated_by,  // Fetch 'last_updated_by' directly from the leaves table
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
    } elseif ($leave->type_of_leave === 'Work From Home') {
        $start = $leave->start_date;
        $end = $leave->end_date;

        // Calculate the number of working days
        $days = \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;

        // Check if start_time and end_time exist to calculate working hours
        if ($leave->start_time && $leave->end_time) {
            $startTime = \Carbon\Carbon::parse($leave->start_time);
            $endTime = \Carbon\Carbon::parse($leave->end_time);
            $hours = $startTime->diff($endTime)->format('%H:%I hours');

            return $days . ' day(s) (from ' . $startTime->format('g:i A') . ' to ' . $endTime->format('g:i A') - ')';
        }

        return $days . ' day(s) of Work From Home';
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
            'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home',
            'half' => 'nullable|required_if:type_of_leave,Half Day Leave|in:First Half,Second Half',
            'start_date' => 'required|date',
            'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave|date|after_or_equal:start_date',
            'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
            'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:255',
            'contact_during_leave' => 'required|string|max:15',
            'status' => 'required|in:pending,approved,disapproved,hold,canceled',
        ]);

        // Clear specific fields for Work From Home leave type
        if ($validatedData['type_of_leave'] === 'Work From Home') {
            $validatedData['start_time'] = null;
            $validatedData['end_time'] = null;
            $validatedData['half'] = null;
        }

        // Clear end_date if Short Leave is selected
        if ($validatedData['type_of_leave'] === 'Short Leave') {
            $validatedData['end_date'] = null;
        }

        // Update the leave record
        $leave->update([
            'type_of_leave' => $validatedData['type_of_leave'],
            'half' => $validatedData['half'] ?? null,
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
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
        \Log::info('Request to update leave:', $request->all());

        // Validate request data
        $validatedData = $request->validate([
            'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home',
'half' => 'nullable|required_if:type_of_leave,Half Day Leave|in:First Half,Second Half',
            'start_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Half Day Leave,Short Leave,Work From Home|date',
            'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Work From Home|date|after_or_equal:start_date',
            'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
            'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
            'reason' => 'required|string',
            'contact_during_leave' => 'required|string|max:15',
            'status' => 'required|in:pending,approved,disapproved,hold,canceled',
        ]);
        

        // Handle 'Work From Home' leave type-specific logic
        if ($validatedData['type_of_leave'] === 'Work From Home') {
            $validatedData['half'] = null; // Not applicable
            $validatedData['start_time'] = null; // Not applicable
            $validatedData['end_time'] = null; // Not applicable
        }

        // Update leave record
        $leave->update([
            'type_of_leave' => $validatedData['type_of_leave'],
            'half' => $validatedData['half'] ?? null,
            'start_date' => $validatedData['start_date'] ?? null,
            'end_date' => in_array($validatedData['type_of_leave'], ['Full Day Leave', 'Work From Home']) ? ($validatedData['end_date'] ?? null) : null,
            'start_time' => $validatedData['start_time'] ?? null,
            'end_time' => $validatedData['end_time'] ?? null,
            'reason' => $validatedData['reason'],
            'contact_during_leave' => $validatedData['contact_during_leave'],
            'status' => $validatedData['status'],
            'last_updated_by' => Auth::user()->name,
        ]);

        // If status is approved, handle daily_tasks logic
        if ($validatedData['status'] === 'approved' && in_array($validatedData['type_of_leave'], ['Short Leave', 'Half Day Leave'])) {
            $attendance = Attendance::where('user_id', $leave->user_id)
                ->whereDate('clockin_time', $validatedData['start_date'])
                ->first();

            if ($attendance) {
                $hours = $validatedData['type_of_leave'] === 'Half Day Leave' 
                    ? 4 
                    : ($validatedData['type_of_leave'] === 'Short Leave'
                        ? (strtotime($validatedData['end_time']) - strtotime($validatedData['start_time'])) / 3600
                        : 8); // Default 8 hours for Work From Home

                DailyTask::create([
                    'user_id' => $leave->user_id,
                    'attendance_id' => $attendance->id,
                    'project_id' => null,
                    'project_name' => $validatedData['type_of_leave'], // Leave type as project name
                    'leave_id' => $leave->id,
                    'task_description' => $validatedData['reason'], // Reason as description
                    'hours' => $hours,
                    'task_status' => 'pending', // Default status
                ]);
            }
        }

        // Send email notification if the status is updated
        $user = $leave->user;
        Mail::to($user->email)->send(new LeaveStatusMail($leave));

        return response()->json(['message' => 'Leave updated successfully!', 'leave' => $leave], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Leave not found',
            'error' => $e->getMessage()
        ], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Error occurred:', ['error' => $e->getMessage()]);
        \Log::error('Validation failed:', $e->errors());
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        \Log::error('Error occurred:', ['error' => $e->getMessage()]);
        \Log::error('Error updating leave:', ['exception' => $e]);
        return response()->json(['error' => 'An internal error occurred. Please try again later.'], 500);
    }
}
    

    
public function search(Request $request)
{
    $searchTerm = $request->input('query', '');

    $users = User::leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                ->where('users.status', '1') // Only fetch active users
                ->where('users.name', 'LIKE', "$searchTerm%")
                ->get([
                    'users.id', 
                    'users.name', 
                    'user_profiles.user_image' // Include user image from user_profiles
                ]);

    // Format user image URL
    $users->transform(function ($user) {
        $user->user_image = $user->user_image 
            ? asset('storage/' . $user->user_image) 
            : asset('img/CWlogo.jpeg'); // Fallback image
        return $user;
    });

    return response()->json($users);
}

    

    public function teamLeave(Request $request)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home', // Include 'Work From Home'
        'half' => 'nullable|required_if:type_of_leave,Half Day Leave|in:First Half,Second Half', // 'half' is required for 'Half Day Leave'
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'start_time' => 'nullable|date_format:H:i',
        'end_time' => 'nullable|date_format:H:i|after:start_time',
        'reason' => 'required|string',
        'contact_during_leave' => 'required|string|max:15',
        'selected_user' => 'required|exists:users,id', // Validate the selected user exists in the 'users' table
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Additional handling for Work From Home leave type
    if ($request->type_of_leave === 'Work From Home') {
        $request->merge([
            'start_time' => null,
            'end_time' => null,
            'half' => null,
        ]);
    }

    // Create the leave record
    $leave = Leave::create([
        'user_id' => $request->selected_user, // Store the selected user's ID
        'type_of_leave' => $request->type_of_leave,
        'half' => $request->type_of_leave === 'Half Day Leave' ? $request->half : null, // Save 'half' only for 'Half Day Leave'
        'start_date' => $request->start_date,
        'end_date' => $request->type_of_leave === 'Short Leave' ? null : $request->end_date, // Clear end_date for Short Leave
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
        $query->where('leaves.status', $request->status); // Explicitly use the 'leaves' table
    }

    if ($request->filled('created_date')) {
        $query->whereDate('leaves.created_at', $request->created_date); // Explicitly use the 'leaves' table
    }

    // Join with users table to get employee name and user_profiles table to get user image
    $query->join('users', 'leaves.user_id', '=', 'users.id')
          ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id') // Left join to include users without profiles
          ->select(
              'leaves.*',
              'users.name as employee_name', // Assuming 'name' is the column for user's name
              'leaves.last_updated_by', // Include last_updated_by from the leaves table
              'user_profiles.user_image' // Include user image from the user_profiles table
          );

    // Fetch the leaves
    $leaves = $query->orderBy('leaves.created_at', 'asc')->get(); // Explicitly use the 'leaves' table

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

        // Get the full URL for the user image
        $userImageUrl = $leave->user_image ? asset('storage/' . $leave->user_image) : null;

        return [
            'id' => $leave->id,
            'employee_name' => $leave->employee_name, // Employee name from the joined table
            'employee_image' => $userImageUrl, // User image URL
            'type' => $typeFormatted,
            'duration' => $duration,
            'status' => ucfirst($leave->status),
            'created_at' => $leave->created_at->format('F d, Y'),
            'updated_by' => $leave->last_updated_by, // Now using 'last_updated_by' from the leaves table
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $formattedLeaves,
    ]);
}
    
   
    public function getUsersLeave(Request $request)
    {
        $selectedDate = $request->input('date'); // Get the selected date from the request
    
        $usersOnLeave = Leave::where('status', 'approved') // Only approved leaves
            ->whereHas('user', function ($query) {
                $query->where('status', '1'); // Fetch only users with status '1'
            })
            ->whereDate('start_date', '<=', $selectedDate) // Start date is before or equal to the selected date
            ->whereDate('end_date', '>=', $selectedDate) // End date is after or equal to the selected date
            ->with(['user:id,name,status', 'user.profile:user_id,user_image']) // Load user details and profile
            ->get()
            ->map(function ($leave) {
                $user = $leave->user;
    
                // Ensure the user is active before including them
                if ($user->status == '0') {
                    return null;
                }
    
                $userImage = $user->profile->user_image ?? null; // Fetch user image
                $userImageUrl = $userImage ? asset('storage/' . $userImage) : null; // Generate full image URL
    
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $leave->status, // Leave status
                    'user_image' => $userImageUrl, // Full URL for the user image
                ];
            })
            ->filter(); // Remove null values (users with status 0)
    
        return response()->json($usersOnLeave);
    }    
    

// public function getUsersOnLeave(Request $request)
// {
//     $selectedDate = $request->query('date'); // Get the selected date from the request

//     // Query users on leave where the selected date falls within the start_date and end_date range
//     $usersOnLeave = Leave::whereDate('start_date', '<=', $selectedDate)
//         ->whereDate('end_date', '>=', $selectedDate)
//         ->with(['user' => function ($query) {
//             $query->select('id', 'name')
//                   ->with('profile:id,user_id,user_image'); // Load user profile image
//         }])
//         ->get()
//         ->map(function ($leave) {
//             $user = $leave->user;

//             return [
//                 'id' => $user->id,
//                 'name' => $user->name,
//                 'user_image' => $user->profile && $user->profile->user_image
//                     ? asset('storage/' . $user->profile->user_image)
//                     : asset('img/CWlogo.jpeg'), // Fallback image if user_image is null
//             ];
//         });

//     return response()->json($usersOnLeave);
// }

public function getMembersOnWFH(Request $request)
{
    $selectedDate = $request->query('date'); // Get the selected date from the query parameters
    $date = \Carbon\Carbon::parse($selectedDate); // Parse the date

    // Fetch approved WFH leaves with active users and profile images
    $leaves = Leave::where('leaves.type_of_leave', 'Work From Home')
        ->where('leaves.status', 'approved')
        ->whereDate('leaves.start_date', '<=', $date)
        ->whereDate('leaves.end_date', '>=', $date)
        ->join('users', 'leaves.user_id', '=', 'users.id')
        ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id') // Join with user_profiles
        ->where('users.status', '1') // Only fetch active users (status = '1')
        ->select(
            'users.name as user_name',
            'leaves.start_date',
            'leaves.end_date',
            'user_profiles.user_image' // Include the user image
        )
        ->orderBy('leaves.start_date', 'asc')
        ->get();

    // Format the data for the response
    $formattedLeaves = $leaves->map(function ($leave) {
        return [
            'user_name' => $leave->user_name,
            'user_image' => $leave->user_image ? asset('storage/' . $leave->user_image) : null, // Generate full URL for the image
            'date_range' => \Carbon\Carbon::parse($leave->start_date)->format('F d, Y') . 
                ' to ' . 
                \Carbon\Carbon::parse($leave->end_date)->format('F d, Y'),
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $formattedLeaves,
    ]);
}


}
