<?php

namespace App\Http\Controllers\Api;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leave;
use App\Mail\LeaveApplicationMail;
use App\Models\User;
use App\Models\DailyTask; 
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Mail\LeaveStatusMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{

    public function applyLeave(Request $request)
{
    // Validate the request data
    $validatedData = $request->validate([
        'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day',
        'half_day' => 'nullable|required_if:type_of_leave,Half Day Leave,Work From Home Half Day|in:First Half,Second Half',
        'start_date' => 'nullable|required_if:type_of_leave,Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day|date',
        'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Work From Home Full Day|date|after_or_equal:start_date',
        'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
        'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
        'reason' => 'required|string',
        'contact_during_leave' => 'required|string|max:50',
    ]);

    try {
        // Save leave
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
            'status' => 'pending',
            'last_updated_by' => Auth::user()->name,
        ]);

        /**
         * Prepare Notification Message
         */

        $userName = Auth::user()->name;
        $typeOfLeave = $validatedData['type_of_leave'];
        $notificationMessage = '';

        switch ($typeOfLeave) {
            case 'Full Day Leave':
                $startDate = \Carbon\Carbon::parse($validatedData['start_date']);
                $endDate = \Carbon\Carbon::parse($validatedData['end_date']);
                $days = $startDate->diffInDays($endDate) + 1;

                $dateRange = $startDate->format('D M d, Y');

                if ($startDate->ne($endDate)) {
                    $dateRange .= ' - ' . $endDate->format('M d, Y D');
                }

                $leaveType = str_replace('Work From Home', 'WFH', $typeOfLeave);

                $notificationMessage = "{$userName} created {$days} days {$leaveType} on {$dateRange}";
                break;

            case 'Work From Home Full Day':
                $startDate = \Carbon\Carbon::parse($validatedData['start_date']);
                $endDate = \Carbon\Carbon::parse($validatedData['end_date']);
                $days = $startDate->diffInDays($endDate) + 1;

                $dateRange = $startDate->format('D M d, Y');

                if ($startDate->ne($endDate)) {
                    $dateRange .= ' - ' . $endDate->format('M d, Y D');
                }

                $leaveType = str_replace('Work From Home', 'WFH', $typeOfLeave);

                $notificationMessage = "{$userName} created {$days} days WFH Full Day on {$dateRange}";
                break;

            case 'Half Day Leave':
                $startDate = \Carbon\Carbon::parse($validatedData['start_date']);
                $half = $validatedData['half_day'] ?? '';
                $dayFormatted = $startDate->format('D M d, Y');

                $leaveType = str_replace('Work From Home', 'WFH', $typeOfLeave);
                $notificationMessage = "{$userName} created {$half} Leave on {$dayFormatted}";
                break;

            case 'Work From Home Half Day':
                $startDate = \Carbon\Carbon::parse($validatedData['start_date']);
                $half = $validatedData['half_day'] ?? '';
                $dayFormatted = $startDate->format('D M d, Y');

                $leaveType = str_replace('Work From Home', 'WFH', $typeOfLeave);
                $notificationMessage = "{$userName} created {$half}, WFH on {$dayFormatted}";
                break;

            case 'Short Leave':
                $startDate = \Carbon\Carbon::parse($validatedData['start_date']);
                $startTime = \Carbon\Carbon::createFromFormat('H:i', $validatedData['start_time'])->format('h:i A');
                $endTime = \Carbon\Carbon::createFromFormat('H:i', $validatedData['end_time'])->format('h:i A');
                $dayFormatted = $startDate->format('D M d, Y');

                $notificationMessage = "{$userName} created Short Leave ({$startTime} to {$endTime}) on {$dayFormatted}";
                break;
        }

        /**
         * Send Notification to all Admins
         */

        $admins = \App\Models\User::whereHas('roles', function ($query) {
    $query->where('name', 'Admin');
})->get();

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $admin->id,
                'type' => 'leaves',
                'type_id' => $leave->id,
                'notification_message' => $notificationMessage,
                'is_read' => false,
            ]);
        }

        return response()->json(['message' => 'Leave application submitted successfully!'], 201);

    } catch (\Exception $e) {
        Log::error('Leave submission failed: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to submit leave application.'], 500);
    }
}

//    public function applyLeave(Request $request)
// {
//     // Validate the request data
//     $validatedData = $request->validate([
//         'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day',
//         'half_day' => 'nullable|required_if:type_of_leave,Half Day Leave,Work From Home Half Day|in:First Half,Second Half',
//         'start_date' => 'nullable|required_if:type_of_leave,Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day|date',
//         'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Work From Home Full Day|date|after_or_equal:start_date',
//         'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
//         'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
//         'reason' => 'required|string',
//         'contact_during_leave' => 'required|string|max:50',
//     ]);

//     try {
//         // Save leave data in the database
//         $leave = Leave::create([
//             'user_id' => Auth::id(),
//             'type_of_leave' => $validatedData['type_of_leave'],
//             'half' => $validatedData['half_day'] ?? null,
//             'start_date' => $validatedData['start_date'] ?? null,
//             'end_date' => $validatedData['end_date'] ?? null,
//             'start_time' => $validatedData['start_time'] ?? null,
//             'end_time' => $validatedData['end_time'] ?? null,
//             'reason' => $validatedData['reason'],
//             'contact_during_leave' => $validatedData['contact_during_leave'],
//             'status' => 'pending', // Default status
//             'last_updated_by' => Auth::user()->name,
//         ]);

//         // Respond with success
//         return response()->json(['message' => 'Leave application submitted successfully!'], 201);

//     } catch (\Exception $e) {
//         Log::error('Leave submission failed: ' . $e->getMessage());
//         return response()->json(['error' => 'Failed to submit leave application.'], 500);
//     }
// }


// ********************** APPLY LEAVE WITH SEND NOTIFICATIONS TO THE ADMIN **************************
//     public function applyLeave(Request $request)
// {
//     // Validate the request data
//     $validatedData = $request->validate([
//         'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home',
//         'half_day' => 'nullable|required_if:type_of_leave,Half Day Leave|in:First Half,Second Half',
//         'start_date' => 'nullable|required_if:type_of_leave,Short Leave,Half Day Leave,Full Day Leave|date',
//         'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave|date|after_or_equal:start_date',
//         'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
//         'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
//         'reason' => 'required|string',
//         'contact_during_leave' => 'required|string|max:50',
//     ]);

//     try {
//         // Save leave data in the database
//         $leave = Leave::create([
//             'user_id' => Auth::id(),
//             'type_of_leave' => $validatedData['type_of_leave'],
//             'half' => $validatedData['half_day'] ?? null,
//             'start_date' => $validatedData['start_date'] ?? null,
//             'end_date' => $validatedData['end_date'] ?? null,
//             'start_time' => $validatedData['start_time'] ?? null,
//             'end_time' => $validatedData['end_time'] ?? null,
//             'reason' => $validatedData['reason'],
//             'contact_during_leave' => $validatedData['contact_during_leave'],
//             'status' => 'pending', // Default status
//             'last_updated_by' => Auth::user()->name,
//         ]);

//         // Retrieve admin user details (update as per your logic)
//         $adminUser = User::where('email', 'adbbharat@gmail.com')->first();
//         $adminName = $adminUser ? $adminUser->name : 'Admin';

//         // Prepare leave details for the email
//         $leaveDetails = [
//             'user_name' => Auth::user()->name,
//             'type_of_leave' => $leave->type_of_leave,
//             'reason' => $leave->reason,
//             'start_date' => $leave->start_date,
//             'end_date' => $leave->end_date,
//             'start_time' => $leave->start_time,
//             'end_time' => $leave->end_time,
//             'contact_during_leave' => $leave->contact_during_leave,
//             'admin_name' => $adminName, // Include admin name
//         ];

//         // Send email to the admin
//         Mail::to('adbbharat@gmail.com')->send(new LeaveApplicationMail($leaveDetails));

//         // Respond with success
//         return response()->json(['message' => 'Leave application submitted successfully!'], 201);

//     } catch (\Exception $e) {
//         Log::error('Leave submission failed: ' . $e->getMessage());
//         return response()->json(['error' => 'Failed to submit leave application.'], 500);
//     }
// }

public function showLeavesUser(Request $request)
{
    $user = Auth::user();
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

    $leaves = $query->orderBy('created_at', 'desc')->get();

    $formattedLeaves = $leaves->map(function ($leave) {
        // Determine icon based on reason
        $reasonIcons = [
            'Full Day Leave' => '<i class="fas fa-umbrella-beach" style="color: #f39c12;"></i>',
            'Half Day Leave' => '<i class="fas fa-hourglass-half" style="color: #ff6347;"></i>',
            'Short Leave' => '<i class="fas fa-clock" style="color: #3498db;"></i>',
            'Work From Home Full Day' => '<i class="fas fa-home" style="color: #2ecc71;"></i>'
        ];
        $icon = $reasonIcons[$leave->type_of_leave] ?? '<i class="fas fa-briefcase" style="color: #4682b4;"></i>';

        // Format leave type with just the icon and type name
        $typeFormatted = $icon . ' ' . $leave->type_of_leave;

        // Convert and append time range in 12-hour format if times exist
        if ($leave->start_time && $leave->end_time && $leave->type_of_leave === 'Short Leave') {
            $startTime12hr = \Carbon\Carbon::createFromFormat('H:i:s', $leave->start_time)->format('g:i A');
            $endTime12hr = \Carbon\Carbon::createFromFormat('H:i:s', $leave->end_time)->format('g:i A');
            $typeFormatted .= "";
        }

        return [
            'id' => $leave->id,
            'type' => $typeFormatted,
            'half' => $leave->half,
            'duration' => $this->calculateDuration($leave),
            'status' => ucfirst($leave->status),
            'created_at' => $leave->created_at->format('F d, Y'),
            'updated_by' => $leave->last_updated_by,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $formattedLeaves,
    ]);
}

private function calculateDuration(Leave $leave)
{
    $start = \Carbon\Carbon::parse($leave->start_date);
    $end = \Carbon\Carbon::parse($leave->end_date);
    
    // Format the date range part that will be common for most leave types
    $dateRange = '(' . strtoupper($start->format('D')) . ' ' . $start->format('M d, Y') . ' - ' . $end->format('M d, Y') . ' ' . strtoupper($end->format('D')) . ')';

    $dateRangehalfdaylleaves = '(' . strtoupper($start->format('D')) . ' ' . $start->format('M d, Y') . ')';

    if ($leave->type_of_leave === 'Full Day Leave') {
        $days = $start->diffInDays($end) + 1;
        return $days . ' day(s) '. '<br>' . $dateRange;
    } elseif ($leave->type_of_leave === 'Half Day Leave' || $leave->type_of_leave === 'Work From Home Half Day') {
        return $leave->half . '<br>' . $dateRangehalfdaylleaves; // 'First Half' or 'Second Half'
    } elseif ($leave->type_of_leave === 'Short Leave') {
        $startTime = \Carbon\Carbon::createFromFormat('H:i:s', $leave->start_time);
        $endTime = \Carbon\Carbon::createFromFormat('H:i:s', $leave->end_time);

        $startTime12hr = \Carbon\Carbon::createFromFormat('H:i:s', $leave->start_time)->format('g:i A');
        $endTime12hr = \Carbon\Carbon::createFromFormat('H:i:s', $leave->end_time)->format('g:i A');
        
        $totalMinutes = $startTime->diffInMinutes($endTime);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        
        $duration = '';
        if ($hours > 0) {
            $duration .= $hours . ' hour' . ($hours > 1 ? 's' : '');
        }
        if ($minutes > 0) {
            if ($hours > 0) {
                $duration .= ' ';
            }
            $duration .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }
        
       return ($duration ?: '0 minutes') . " (from $startTime12hr to $endTime12hr)<br>" . $dateRangehalfdaylleaves;

    } elseif ($leave->type_of_leave === 'Work From Home Full Day') {
        $days = $start->diffInDays($end) + 1;
        
        if ($leave->start_time && $leave->end_time) {
            $startTime = $startTime->format('g:i A');
            $endTime = $endTime->format('g:i A');
            return $days . ' day(s) ' . $dateRange . ' (from ' . $startTime . ' to ' . $endTime . ')';
        }
        
        return $days . ' day(s)' . '<br>' . $dateRange . '<br>';
    }

    return '';
}

public function updateLeaveUser(Request $request, Leave $leave)
{
    try {
        \Log::info('Incoming Request Data:', $request->all());

        // Format time
        if ($request->has('start_time')) {
            $request->merge(['start_time' => date('H:i', strtotime($request->start_time))]);
        }

        if ($request->has('end_time')) {
            $request->merge(['end_time' => date('H:i', strtotime($request->end_time))]);
        }

        // Validate
        $validatedData = $request->validate([
            'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day',
            'half' => 'nullable|required_if:type_of_leave,Half Day Leave,Work From Home Half Day|in:First Half,Second Half',
            'start_date' => 'required|date',
            'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Work From Home Full Day|date|after_or_equal:start_date',
            'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
            'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:255',
            'contact_during_leave' => 'required|string|max:15',
            'status' => 'required|in:pending,approved,disapproved,hold,canceled',
        ]);

        // Cleanup fields by leave type
        switch ($validatedData['type_of_leave']) {
            case 'Work From Home Full Day':
                $validatedData['start_time'] = null;
                $validatedData['end_time'] = null;
                $validatedData['half'] = null;
                break;
            case 'Work From Home Half Day':
                $validatedData['start_time'] = null;
                $validatedData['end_time'] = null;
                break;
            case 'Short Leave':
                $validatedData['end_date'] = null;
                break;
        }

        // Update
        $leave->update([
            'type_of_leave' => $validatedData['type_of_leave'],
            'half' => $validatedData['half'] ?? null,
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'] ?? null,
            'start_time' => $validatedData['start_time'] ?? null,
            'end_time' => $validatedData['end_time'] ?? null,
            'reason' => $validatedData['reason'],
            'contact_during_leave' => $validatedData['contact_during_leave'],
            'status' => $validatedData['status'],
            'last_updated_by' => $request->user()->name,
        ]);

        // ========== Notify Admins ==========
        $userName = $request->user()->name;
        $typeOfLeave = $validatedData['type_of_leave'];
        $status = $validatedData['status'];
        $notificationMessage = '';
        

       if ($status === 'canceled') {
    $notificationMessage = "";
    $dateRange = "";
    
    // Format date range
    if ($leave->start_date && $leave->end_date) {
        $startDay = Carbon::parse($leave->start_date)->format('D M d, Y');
        $endDay = Carbon::parse($leave->end_date)->format('D M d, Y');
        
        if ($leave->start_date === $leave->end_date) {
            $dateRange = "({$startDay})";
        } else {
            $dateRange = "({$startDay} - {$endDay})";
        }
    }

    switch ($leave->type_of_leave) {
        case 'Short Leave':
            $timeRange = "";
            if ($leave->start_time && $leave->end_time) {
                $startTime = Carbon::parse($leave->start_time)->format('h:i A');
                $endTime = Carbon::parse($leave->end_time)->format('h:i A');
                $timeRange = " from {$startTime} to {$endTime}";
            }
            $notificationMessage = "{$userName} canceled their Short Leave{$timeRange} on " . Carbon::parse($leave->start_date)->format('D M d, Y');
            break;

        case 'Half Day Leave':
            $half = $leave->half ? " ({$leave->half})" : "";
            $notificationMessage = "{$userName} canceled their Half Day Leave{$half} on " . Carbon::parse($leave->start_date)->format('D M d, Y');
            break;

        case 'Full Day Leave':
            $daysCount = 1;
            if ($leave->end_date && $leave->start_date !== $leave->end_date) {
                $daysCount = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            }
            $dayText = $daysCount > 1 ? "{$daysCount} days" : "1 day";
            $notificationMessage = "{$userName} canceled their Full Day Leave {$dayText} {$dateRange}";
            break;

        case 'Work From Home Full Day':
            $daysCount = 1;
            if ($leave->end_date && $leave->start_date !== $leave->end_date) {
                $daysCount = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            }
            $dayText = $daysCount > 1 ? "{$daysCount} days" : "1 day";
            $notificationMessage = "{$userName} canceled their Work From Home Full Day {$dayText} {$dateRange}";
            break;

        case 'Work From Home Half Day':
            $half = $leave->half ? " ({$leave->half})" : "";
            $notificationMessage = "{$userName} canceled their Work From Home Half Day{$half} on " . Carbon::parse($leave->start_date)->format('D M d, Y');
            break;

        default:
            $notificationMessage = "{$userName} canceled their leave request";
            break;
    }
} else {
            switch ($typeOfLeave) {
                case 'Full Day Leave':
                case 'Work From Home Full Day':
                    $start = \Carbon\Carbon::parse($validatedData['start_date']);
                    $end = \Carbon\Carbon::parse($validatedData['end_date']);
                    $days = $start->diffInDays($end) + 1;
                    $dateRange = $start->format('D M d, Y');

                    if ($start->ne($end)) {
                        $dateRange .= ' - ' . $end->format('M d, Y D');
                    }

                    $leaveType = str_replace('Work From Home', 'WFH', $typeOfLeave);
                    $notificationMessage = "{$userName} updated {$days} days {$leaveType} on {$dateRange}";
                    break;

                case 'Half Day Leave':
                case 'Work From Home Half Day':
                    $startDate = \Carbon\Carbon::parse($validatedData['start_date']);
                    $half = $validatedData['half'] ?? '';
                    $dayFormatted = $startDate->format('D M d, Y');
                    $leaveType = str_replace('Work From Home', 'WFH', $typeOfLeave);
                    $notificationMessage = "{$userName} updated {$leaveType}, to {$half} on {$dayFormatted}";
                    break;

                case 'Short Leave':
                    $startDate = \Carbon\Carbon::parse($validatedData['start_date']);
                    $startTime = \Carbon\Carbon::createFromFormat('H:i', $validatedData['start_time'])->format('h:i A');
                    $endTime = \Carbon\Carbon::createFromFormat('H:i', $validatedData['end_time'])->format('h:i A');
                    $dayFormatted = $startDate->format('D M d, Y');
                    $notificationMessage = "{$userName} updated Short Leave ({$startTime} to {$endTime}) on {$dayFormatted}";
                    break;
            }
        }

        // Send to all admins
        $admins = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'Admin');
        })->get();

        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'from_user_id' => $request->user()->id,
                'to_user_id' => $admin->id,
                'type' => 'leaves',
                'type_id' => $leave->id,
                'notification_message' => $notificationMessage,
                'is_read' => false,
            ]);
        }

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
// public function updateTeamLeave(Request $request, Leave $leave)
// {
//     try {
//         \Log::info('Request to update leave:', $request->all());

//         // Save original leave data for comparison later
//         $originalLeave = $leave->replicate();

//         // Validate request data
//         $validatedData = $request->validate([
//             'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day',
//             'half' => 'nullable|required_if:type_of_leave,Half Day Leave,Work From Home Half Day|in:First Half,Second Half',
//             'start_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Half Day Leave,Short Leave,Work From Home Full Day,Work From Home Half Day|date',
//             'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Work From Home Full Day|date|after_or_equal:start_date',
//             'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
//             'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
//             'reason' => 'required|string',
//             'contact_during_leave' => 'required|string|max:15',
//             'status' => 'required|in:pending,approved,disapproved,hold,canceled',
//         ]);

//         // Handle leave type-specific logic
//         switch ($validatedData['type_of_leave']) {
//             case 'Work From Home Full Day':
//                 $validatedData['half'] = null;
//                 $validatedData['start_time'] = null;
//                 $validatedData['end_time'] = null;
//                 break;

//             case 'Work From Home Half Day':
//                 $validatedData['start_time'] = null;
//                 $validatedData['end_time'] = null;
//                 break;
//         }

//         // Update leave record
//         $leave->update([
//             'type_of_leave' => $validatedData['type_of_leave'],
//             'half' => $validatedData['half'] ?? null,
//             'start_date' => $validatedData['start_date'] ?? null,
//             'end_date' => in_array($validatedData['type_of_leave'], ['Full Day Leave', 'Work From Home Full Day'])
//                             ? ($validatedData['end_date'] ?? null)
//                             : null,
//             'start_time' => $validatedData['start_time'] ?? null,
//             'end_time' => $validatedData['end_time'] ?? null,
//             'reason' => $validatedData['reason'],
//             'contact_during_leave' => $validatedData['contact_during_leave'],
//             'status' => $validatedData['status'],
//             'last_updated_by' => Auth::user()->name,
//         ]);

//         // Check for changes in type/date vs. the original leave
//         $isTypeOrDateChanged = (
//             $originalLeave->type_of_leave !== $leave->type_of_leave ||
//             $originalLeave->half !== $leave->half ||
//             $originalLeave->start_date !== $leave->start_date ||
//             $originalLeave->end_date !== $leave->end_date ||
//             $originalLeave->start_time !== $leave->start_time ||
//             $originalLeave->end_time !== $leave->end_time
//         );

//         // Prepare notification messages
//         $actingAdmin = Auth::user();
//         $user = \App\Models\User::find($leave->user_id);

//         $notifications = [];

//         if ($isTypeOrDateChanged) {
//             // Format old leave
//             $oldDesc = $this->formatLeaveDescription($originalLeave);
//             $newDesc = $this->formatLeaveDescription($leave);

//             // Notification to user
//             $userMessage = "Your leave, {$oldDesc} updated to {$newDesc}";
//             $notifications[] = [
//                 'from_user_id' => $actingAdmin->id,
//                 'to_user_id' => $user->id,
//                 'type' => 'leaves',
//                 'type_id' => $leave->id,
//                 'notification_message' => $userMessage,
//                 'created_at' => now(),
//                 'updated_at' => now(),
//             ];

//             // Notification to other admins
//             $adminMessage = "{$actingAdmin->name} updated {$user->name} leave, {$oldDesc} to {$newDesc}";
//             $admins = \App\Models\User::whereHas('roles', function ($q) {
//                 $q->where('name', 'Admin');
//             })->where('id', '<>', $actingAdmin->id)->get();

//             foreach ($admins as $admin) {
//                 $notifications[] = [
//                     'from_user_id' => $actingAdmin->id,
//                     'to_user_id' => $admin->id,
//                     'type' => 'leaves',
//                     'type_id' => $leave->id,
//                     'notification_message' => $adminMessage,
//                     'created_at' => now(),
//                     'updated_at' => now(),
//                 ];
//             }
//         } else {
//             // Prepare messages for status change only
//             $leaveDesc = $this->formatLeaveDescription($leave);

//             // Notification to user
//             $userMessage = "Your {$leaveDesc} is " . strtoupper($leave->status);
//             $notifications[] = [
//                 'from_user_id' => $actingAdmin->id,
//                 'to_user_id' => $user->id,
//                 'type' => 'leaves',
//                 'type_id' => $leave->id,
//                 'notification_message' => $userMessage,
//                 'created_at' => now(),
//                 'updated_at' => now(),
//             ];

//             // Notification to other admins
//             $adminMessage = "{$actingAdmin->name} " . strtoupper($leave->status) . " {$user->name} {$leaveDesc}";
//             $admins = \App\Models\User::whereHas('roles', function ($q) {
//                 $q->where('name', 'Admin');
//             })->where('id', '<>', $actingAdmin->id)->get();

//             foreach ($admins as $admin) {
//                 $notifications[] = [
//                     'from_user_id' => $actingAdmin->id,
//                     'to_user_id' => $admin->id,
//                     'type' => 'leaves',
//                     'type_id' => $leave->id,
//                     'notification_message' => $adminMessage,
//                     'created_at' => now(),
//                     'updated_at' => now(),
//                 ];
//             }
//         }

//         // Insert notifications
//         if (!empty($notifications)) {
//             \App\Models\Notification::insert($notifications);
//         }

//         // Handle daily_tasks logic if approved
//         if ($validatedData['status'] === 'approved') {
//             $attendance = Attendance::where('user_id', $leave->user_id)
//                 ->whereDate('clockin_time', $validatedData['start_date'])
//                 ->first();

//             if ($attendance) {
//                 $hours = 0;
//                 switch ($validatedData['type_of_leave']) {
//     case 'Half Day Leave':
//         $hours = 4;
//         break;

//     case 'Short Leave':
//         $start = strtotime($validatedData['start_time']);
//         $end = strtotime($validatedData['end_time']);
        
//         // Handle cases where end time might be next day (crossing midnight)
//         if ($end < $start) {
//             $end += 86400; // Add 24 hours (86400 seconds) if end is before start
//         }
        
//         $hours = ($end - $start) / 3600;
//         break;
// }


//                 DailyTask::updateOrCreate(
//                     ['leave_id' => $leave->id],
//                     [
//                         'user_id' => $leave->user_id,
//                         'attendance_id' => $attendance->id,
//                         'project_id' => null,
//                         'project_name' => $validatedData['type_of_leave'],
//                         'leave_id' => $leave->id,
//                         'task_description' => $validatedData['reason'],
//                         'hours' => $hours,
//                         'task_status' => 'pending',
//                     ]
//                 );
//             }
//         }

//         return response()->json([
//             'message' => 'Leave updated successfully!',
//             'leave' => $leave
//         ], 200);

//     } catch (ModelNotFoundException $e) {
//         return response()->json([
//             'message' => 'Leave not found',
//             'error' => $e->getMessage()
//         ], 404);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         \Log::error('Error occurred:', ['error' => $e->getMessage()]);
//         \Log::error('Validation failed:', $e->errors());
//         return response()->json(['errors' => $e->errors()], 422);
//     } catch (\Exception $e) {
//         \Log::error('Error occurred:', ['error' => $e->getMessage()]);
//         \Log::error('Error updating leave:', ['exception' => $e]);
//         return response()->json(['error' => 'An internal error occurred. Please try again later.'], 500);
//     }
// }
public function updateTeamLeave(Request $request, Leave $leave)
{
    try {
        \Log::info('Request to update leave:', $request->all());

        // Save original leave data for comparison
        $originalLeave = $leave->replicate();

        // Validate request data
        $validatedData = $request->validate([
            'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day',
            'half' => 'nullable|required_if:type_of_leave,Half Day Leave,Work From Home Half Day|in:First Half,Second Half',
            'start_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Half Day Leave,Short Leave,Work From Home Full Day,Work From Home Half Day|date',
            'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Work From Home Full Day|date|after_or_equal:start_date',
            'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
            'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
            'reason' => 'required|string',
            'contact_during_leave' => 'required|string|max:15',
            'status' => 'required|in:pending,approved,disapproved,hold,canceled',
        ]);

        // Handle leave type-specific logic
        switch ($validatedData['type_of_leave']) {
            case 'Work From Home Full Day':
                $validatedData['half'] = null;
                $validatedData['start_time'] = null;
                $validatedData['end_time'] = null;
                break;

            case 'Work From Home Half Day':
                $validatedData['start_time'] = null;
                $validatedData['end_time'] = null;
                break;
        }

        // Update the leave record
        $leave->update([
            'type_of_leave' => $validatedData['type_of_leave'],
            'half' => $validatedData['half'] ?? null,
            'start_date' => $validatedData['start_date'] ?? null,
            'end_date' => in_array($validatedData['type_of_leave'], ['Full Day Leave', 'Work From Home Full Day'])
                            ? ($validatedData['end_date'] ?? null)
                            : null,
            'start_time' => $validatedData['start_time'] ?? null,
            'end_time' => $validatedData['end_time'] ?? null,
            'reason' => $validatedData['reason'],
            'contact_during_leave' => $validatedData['contact_during_leave'],
            'status' => $validatedData['status'],
            'last_updated_by' => Auth::user()->name,
        ]);

        // Check if only status changed or other details
        $isTypeOrDateChanged = $leave->isDirty([
            'type_of_leave',
            'half',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
        ]);

        $actingAdmin = Auth::user();
        $user = \App\Models\User::find($leave->user_id);

        $notifications = [];
        $admins = \App\Models\User::whereHas('roles', function ($q) {
                $q->where('name', 'Admin');
            })->where('id', '<>', $actingAdmin->id)->get();

        if ($isTypeOrDateChanged) {
            // Leave details changed (e.g. type, date, times)

            $oldDesc = $this->formatLeaveDescription($originalLeave);
            $newDesc = $this->formatLeaveDescription($leave);

            // Notify user
            $userMessage = "Your leave, {$oldDesc} updated to {$newDesc}";
            $notifications[] = [
                'from_user_id' => $actingAdmin->id,
                'to_user_id' => $user->id,
                'type' => 'leaves',
                'type_id' => $leave->id,
                'notification_message' => $userMessage,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Notify other admins
            $adminMessage = "{$actingAdmin->name} updated {$user->name} leave, {$oldDesc} to {$newDesc}";
            foreach ($admins as $admin) {
                $notifications[] = [
                    'from_user_id' => $actingAdmin->id,
                    'to_user_id' => $admin->id,
                    'type' => 'leaves',
                    'type_id' => $leave->id,
                    'notification_message' => $adminMessage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        } else {
            // Only status changed
            $leaveDesc = $this->formatLeaveDescription($leave);
            $statusUpper = strtoupper($leave->status);

            // User notification
            $notifications[] = [
                'from_user_id' => $actingAdmin->id,
                'to_user_id' => $user->id,
                'type' => 'leaves',
                'type_id' => $leave->id,
                'notification_message' => "Your {$leaveDesc} is {$statusUpper}",
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Admin notifications
            $adminMessage = "{$actingAdmin->name} {$statusUpper} {$user->name} {$leaveDesc}";
            foreach ($admins as $admin) {
                $notifications[] = [
                    'from_user_id' => $actingAdmin->id,
                    'to_user_id' => $admin->id,
                    'type' => 'leaves',
                    'type_id' => $leave->id,
                    'notification_message' => $adminMessage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($notifications)) {
            \App\Models\Notification::insert($notifications);
        }

        // Handle daily_tasks logic if approved
        if ($validatedData['status'] === 'approved') {
            $attendance = Attendance::where('user_id', $leave->user_id)
                ->whereDate('clockin_time', $validatedData['start_date'])
                ->first();

            if ($attendance) {
                $hours = 0;
                switch ($validatedData['type_of_leave']) {
                    case 'Half Day Leave':
                        $hours = 4;
                        break;

                        case 'Work From Home Half Day':
                        $hours = 4;
                        break;

                    case 'Short Leave':
                        $start = strtotime($validatedData['start_time']);
                        $end = strtotime($validatedData['end_time']);

                        if ($end < $start) {
                            $end += 86400; // handle crossing midnight
                        }

                        $hours = ($end - $start) / 3600;
                        break;
                }

                DailyTask::updateOrCreate(
                    ['leave_id' => $leave->id],
                    [
                        'user_id' => $leave->user_id,
                        'attendance_id' => $attendance->id,
                        'project_id' => null,
                        'project_name' => $validatedData['type_of_leave'],
                        'leave_id' => $leave->id,
                        'task_description' => $validatedData['reason'],
                        'hours' => $hours,
                        'task_status' => 'pending',
                    ]
                );
            }
        }

        return response()->json([
            'message' => 'Leave updated successfully!',
            'leave' => $leave
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Leave not found',
            'error' => $e->getMessage()
        ], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation failed:', $e->errors());
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        \Log::error('Error updating leave:', ['exception' => $e]);
        return response()->json(['error' => 'An internal error occurred. Please try again later.'], 500);
    }
}

/**
 * Helper to generate leave description string
 */
private function formatLeaveDescription($leave)
{
    $type = $leave->type_of_leave;

    $dateStr = '';
    if (in_array($type, ['Full Day Leave', 'Work From Home Full Day'])) {
        $startDate = $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('D M d, Y') : null;
        $endDate = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('D M d, Y') : null;
        $days = $startDate && $endDate && $startDate !== $endDate
            ? \Carbon\Carbon::parse($leave->start_date)->diffInDays($leave->end_date) + 1 . ' days '
            : '1 day ';
        $dateStr = $days . $startDate . ($endDate && $startDate !== $endDate ? ' - ' . $endDate : '');
    } elseif (in_array($type, ['Half Day Leave', 'Work From Home Half Day'])) {
        $dateStr = ($leave->half ? $leave->half . ' ' : '') . 'on ' . \Carbon\Carbon::parse($leave->start_date)->format('D M d, Y');
    } elseif ($type === 'Short Leave') {
        $dateStr = '(' . $leave->start_time . ' to ' . $leave->end_time . ') on ' . \Carbon\Carbon::parse($leave->start_date)->format('D M d, Y');
    }

    $typeDesc = $type === 'Work From Home Full Day' ? 'WFH Full Day'
              : ($type === 'Work From Home Half Day' ? 'WFH Half Day'
              : $type);

    return trim("{$dateStr} {$typeDesc}");
}

public function applyTeamLeave(Request $request)
{
    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day',
        'half' => 'nullable|required_if:type_of_leave,Half Day Leave,Work From Home Half Day|in:First Half,Second Half',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'start_time' => 'nullable|date_format:H:i',
        'end_time' => 'nullable|date_format:H:i|after:start_time',
        'reason' => 'required|string',
        'contact_during_leave' => 'required|string|max:15',
        'selected_user' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Additional handling for Work From Home leave types
    if (in_array($request->type_of_leave, ['Work From Home Full Day', 'Work From Home Half Day'])) {
        $request->merge([
            'start_time' => null,
            'end_time' => null,
        ]);
        
        // Only clear half if it's Full Day
        if ($request->type_of_leave === 'Work From Home Full Day') {
            $request->merge(['half' => null]);
        }
    }

    // Create the leave record
    $leave = Leave::create([
        'user_id' => $request->selected_user,
        'type_of_leave' => $request->type_of_leave,
        'half' => in_array($request->type_of_leave, ['Half Day Leave', 'Work From Home Half Day']) 
                  ? $request->half 
                  : null,
        'start_date' => $request->start_date,
        'end_date' => $request->type_of_leave === 'Short Leave' 
                     ? null 
                     : $request->end_date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'reason' => $request->reason,
        'contact_during_leave' => $request->contact_during_leave,
        'last_updated_by' => Auth::user()->name,
        'status' => 'pending',
    ]);

    // Create notifications
    $this->createNewLeaveNotifications($leave, Auth::user());

    return response()->json(['message' => 'Leave applied successfully!', 'data' => $leave], 201);
}

protected function createNewLeaveNotifications(Leave $leave, $adminUser)
{
    $startDate = Carbon::parse($leave->start_date);
    $endDate = $leave->end_date ? Carbon::parse($leave->end_date) : null;

    $formattedStart = $startDate->format('D M d, Y');
    $formattedEnd = $endDate && !$startDate->equalTo($endDate)
        ? $endDate->format('D M d, Y')
        : null;

    $dateRange = $formattedStart;
    $daysCount = 1;

    if ($formattedEnd) {
        $daysCount = $startDate->diffInDays($endDate) + 1;
        $dateRange .= " - " . $formattedEnd;
    }

    // Format time range if short leave
    $timePart = '';
    if ($leave->start_time && $leave->end_time) {
        $startTime = Carbon::parse($leave->start_time)->format('h:i A');
        $endTime = Carbon::parse($leave->end_time)->format('h:i A');
        $timePart = " ($startTime to $endTime)";
    }

    // Construct leave description
    $leaveDescription = '';

    switch ($leave->type_of_leave) {
        case 'Full Day Leave':
            $leaveDescription = ($daysCount > 1 ? "{$daysCount} days " : "1 day ") . "Full Day Leave on $dateRange";
            break;

        case 'Half Day Leave':
            $leaveDescription = "{$leave->half} Leave on $formattedStart";
            break;

        case 'Short Leave':
            $leaveDescription = "Short Leave{$timePart} on $formattedStart";
            break;

        case 'Work From Home Full Day':
            $leaveDescription = ($daysCount > 1 ? "{$daysCount} days " : "1 day ") . "WFH Full Day on $dateRange";
            break;

        case 'Work From Home Half Day':
            $leaveDescription = "{$leave->half} WFH Half Day on $formattedStart";
            break;
    }

    // Append status
    $statusFormatted = strtoupper($leave->status);
    $userMessage = "{$adminUser->name} Applied your {$leaveDescription} is {$statusFormatted}";
    $adminMessage = "{$adminUser->name} Applied {$leave->user->name} {$leaveDescription} is {$statusFormatted}";

    // Create notification for user
    \App\Models\Notification::create([
        'from_user_id' => $adminUser->id,
        'type' => 'leaves',
        'type_id' => $leave->id,
        'notification_message' => $userMessage,
        'to_user_id' => $leave->user_id,
    ]);

    // Notify other admins
    $admins = \App\Models\User::whereHas('roles', function ($q) {
        $q->where('name', 'Admin');
    })->where('id', '!=', $adminUser->id)->get();

    foreach ($admins as $admin) {
        \App\Models\Notification::create([
            'from_user_id' => $adminUser->id,
            'type' => 'leaves',
            'type_id' => $leave->id,
            'notification_message' => $adminMessage,
            'to_user_id' => $admin->id,
        ]);
    }
}


// public function updateLeaveUser(Request $request, Leave $leave)
// {
//     try {
//         // Log the incoming request data for debugging
//         \Log::info('Incoming Request Data:', $request->all());

//         // Ensure time fields are in the correct format
//         if ($request->has('start_time')) {
//             $request->merge([
//                 'start_time' => date('H:i', strtotime($request->start_time)),
//             ]);
//         }

//         if ($request->has('end_time')) {
//             $request->merge([
//                 'end_time' => date('H:i', strtotime($request->end_time)),
//             ]);
//         }

//         // Validate the input
//         $validatedData = $request->validate([
//             'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day',
//             'half' => 'nullable|required_if:type_of_leave,Half Day Leave,Work From Home Half Day|in:First Half,Second Half',
//             'start_date' => 'required|date',
//             'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Work From Home Full Day|date|after_or_equal:start_date',
//             'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
//             'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
//             'reason' => 'required|string|max:255',
//             'contact_during_leave' => 'required|string|max:15',
//             'status' => 'required|in:pending,approved,disapproved,hold,canceled',
//         ]);

//         // Handle special cases for different leave types
//         switch ($validatedData['type_of_leave']) {
//             case 'Work From Home Full Day':
//                 $validatedData['start_time'] = null;
//                 $validatedData['end_time'] = null;
//                 $validatedData['half'] = null;
//                 break;
                
//             case 'Work From Home Half Day':
//                 $validatedData['start_time'] = null;
//                 $validatedData['end_time'] = null;
//                 break;
                
//             case 'Short Leave':
//                 $validatedData['end_date'] = null;
//                 break;
//         }

//         // Update the leave record
//         $leave->update([
//             'type_of_leave' => $validatedData['type_of_leave'],
//             'half' => $validatedData['half'] ?? null,
//             'start_date' => $validatedData['start_date'],
//             'end_date' => $validatedData['end_date'] ?? null,
//             'start_time' => $validatedData['start_time'] ?? null,
//             'end_time' => $validatedData['end_time'] ?? null,
//             'reason' => $validatedData['reason'],
//             'contact_during_leave' => $validatedData['contact_during_leave'],
//             'status' => $validatedData['status'],
//             'last_updated_by' => $request->user()->name,
//         ]);

//         return response()->json([
//             'message' => 'Leave application updated successfully!',
//             'leave' => $leave,
//         ], 200);

//     } catch (\Illuminate\Validation\ValidationException $e) {
//         \Log::error('Validation failed:', $e->errors());
//         return response()->json(['error' => $e->errors()], 422);
//     } catch (\Exception $e) {
//         \Log::error('Error updating leave:', ['message' => $e->getMessage()]);
//         return response()->json(['error' => 'An error occurred while updating leave.'], 500);
//     }
// }

     
// public function updateTeamLeave(Request $request, Leave $leave)
// {
//     try {
//         \Log::info('Request to update leave:', $request->all());

//         // Validate request data
//         $validatedData = $request->validate([
//             'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day',
//             'half' => 'nullable|required_if:type_of_leave,Half Day Leave,Work From Home Half Day|in:First Half,Second Half',
//             'start_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Half Day Leave,Short Leave,Work From Home Full Day,Work From Home Half Day|date',
//             'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Work From Home Full Day|date|after_or_equal:start_date',
//             'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
//             'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
//             'reason' => 'required|string',
//             'contact_during_leave' => 'required|string|max:15',
//             'status' => 'required|in:pending,approved,disapproved,hold,canceled',
//         ]);
        
//         // Handle leave type-specific logic
//         switch ($validatedData['type_of_leave']) {
//             case 'Work From Home Full Day':
//                 $validatedData['half'] = null;
//                 $validatedData['start_time'] = null;
//                 $validatedData['end_time'] = null;
//                 break;
                
//             case 'Work From Home Half Day':
//                 $validatedData['start_time'] = null;
//                 $validatedData['end_time'] = null;
//                 break;
//         }

//         // Update leave record
//         $leave->update([
//             'type_of_leave' => $validatedData['type_of_leave'],
//             'half' => $validatedData['half'] ?? null,
//             'start_date' => $validatedData['start_date'] ?? null,
//             'end_date' => in_array($validatedData['type_of_leave'], ['Full Day Leave', 'Work From Home Full Day']) 
//                          ? ($validatedData['end_date'] ?? null) 
//                          : null,
//             'start_time' => $validatedData['start_time'] ?? null,
//             'end_time' => $validatedData['end_time'] ?? null,
//             'reason' => $validatedData['reason'],
//             'contact_during_leave' => $validatedData['contact_during_leave'],
//             'status' => $validatedData['status'],
//             'last_updated_by' => Auth::user()->name,
//         ]);

//         // If status is approved, handle daily_tasks logic
//         if ($validatedData['status'] === 'approved') {
//             $attendance = Attendance::where('user_id', $leave->user_id)
//                 ->whereDate('clockin_time', $validatedData['start_date'])
//                 ->first();

//             if ($attendance) {
//                 $hours = 0;
                
//                 switch ($validatedData['type_of_leave']) {
//                     case 'Half Day Leave':
//                     case 'Work From Home Half Day':
//                         $hours = 4;
//                         break;
                        
//                     case 'Short Leave':
//                         $hours = (strtotime($validatedData['end_time']) - strtotime($validatedData['start_time'])) / 3600;
//                         break;
                        
//                     case 'Full Day Leave':
//                     case 'Work From Home Full Day':
//                         $hours = 8;
//                         break;
//                 }

//                 DailyTask::updateOrCreate(
//                     ['leave_id' => $leave->id],
//                     [
//                         'user_id' => $leave->user_id,
//                         'attendance_id' => $attendance->id,
//                         'project_id' => null,
//                         'project_name' => $validatedData['type_of_leave'],
//                         'leave_id' => $leave->id,
//                         'task_description' => $validatedData['reason'],
//                         'hours' => $hours,
//                         'task_status' => 'pending',
//                     ]
//                 );
//             }
//         }

//         return response()->json(['message' => 'Leave updated successfully!', 'leave' => $leave], 200);
//     } catch (ModelNotFoundException $e) {
//         return response()->json([
//             'message' => 'Leave not found',
//             'error' => $e->getMessage()
//         ], 404);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         \Log::error('Error occurred:', ['error' => $e->getMessage()]);
//         \Log::error('Validation failed:', $e->errors());
//         return response()->json(['errors' => $e->errors()], 422);
//     } catch (\Exception $e) {
//         \Log::error('Error occurred:', ['error' => $e->getMessage()]);
//         \Log::error('Error updating leave:', ['exception' => $e]);
//         return response()->json(['error' => 'An internal error occurred. Please try again later.'], 500);
//     }
// }

// public function updateTeamLeave(Request $request, Leave $leave)
// {
//     try {
//         \Log::info('Request to update leave:', $request->all());

//         // Validate request data
//         $validatedData = $request->validate([
//             'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home',
// 'half' => 'nullable|required_if:type_of_leave,Half Day Leave|in:First Half,Second Half',
//             'start_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Half Day Leave,Short Leave,Work From Home|date',
//             'end_date' => 'nullable|required_if:type_of_leave,Full Day Leave,Work From Home|date|after_or_equal:start_date',
//             'start_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i',
//             'end_time' => 'nullable|required_if:type_of_leave,Short Leave|date_format:H:i|after:start_time',
//             'reason' => 'required|string',
//             'contact_during_leave' => 'required|string|max:15',
//             'status' => 'required|in:pending,approved,disapproved,hold,canceled',
//         ]);
        

//         // Handle 'Work From Home' leave type-specific logic
//         if ($validatedData['type_of_leave'] === 'Work From Home') {
//             $validatedData['half'] = null; // Not applicable
//             $validatedData['start_time'] = null; // Not applicable
//             $validatedData['end_time'] = null; // Not applicable
//         }

//         // Update leave record
//         $leave->update([
//             'type_of_leave' => $validatedData['type_of_leave'],
//             'half' => $validatedData['half'] ?? null,
//             'start_date' => $validatedData['start_date'] ?? null,
//             'end_date' => in_array($validatedData['type_of_leave'], ['Full Day Leave', 'Work From Home']) ? ($validatedData['end_date'] ?? null) : null,
//             'start_time' => $validatedData['start_time'] ?? null,
//             'end_time' => $validatedData['end_time'] ?? null,
//             'reason' => $validatedData['reason'],
//             'contact_during_leave' => $validatedData['contact_during_leave'],
//             'status' => $validatedData['status'],
//             'last_updated_by' => Auth::user()->name,
//         ]);

//         // If status is approved, handle daily_tasks logic
//         if ($validatedData['status'] === 'approved' && in_array($validatedData['type_of_leave'], ['Short Leave', 'Half Day Leave'])) {
//             $attendance = Attendance::where('user_id', $leave->user_id)
//                 ->whereDate('clockin_time', $validatedData['start_date'])
//                 ->first();

//             if ($attendance) {
//                 $hours = $validatedData['type_of_leave'] === 'Half Day Leave' 
//                     ? 4 
//                     : ($validatedData['type_of_leave'] === 'Short Leave'
//                         ? (strtotime($validatedData['end_time']) - strtotime($validatedData['start_time'])) / 3600
//                         : 8); // Default 8 hours for Work From Home

//                 DailyTask::create([
//                     'user_id' => $leave->user_id,
//                     'attendance_id' => $attendance->id,
//                     'project_id' => null,
//                     'project_name' => $validatedData['type_of_leave'], // Leave type as project name
//                     'leave_id' => $leave->id,
//                     'task_description' => $validatedData['reason'], // Reason as description
//                     'hours' => $hours,
//                     'task_status' => 'pending', // Default status
//                 ]);
//             }
//         }

//         // Send email notification if the status is updated
//         $user = $leave->user;
//         Mail::to($user->email)->send(new LeaveStatusMail($leave));

//         return response()->json(['message' => 'Leave updated successfully!', 'leave' => $leave], 200);
//     } catch (ModelNotFoundException $e) {
//         return response()->json([
//             'message' => 'Leave not found',
//             'error' => $e->getMessage()
//         ], 404);
//     } catch (\Illuminate\Validation\ValidationException $e) {
//         \Log::error('Error occurred:', ['error' => $e->getMessage()]);
//         \Log::error('Validation failed:', $e->errors());
//         return response()->json(['errors' => $e->errors()], 422);
//     } catch (\Exception $e) {
//         \Log::error('Error occurred:', ['error' => $e->getMessage()]);
//         \Log::error('Error updating leave:', ['exception' => $e]);
//         return response()->json(['error' => 'An internal error occurred. Please try again later.'], 500);
//     }
// }
    

    
public function searchUser(Request $request)
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
            ? asset('uploads/' . $user->user_image) 
            : asset('img/CWlogo.jpeg'); // Fallback image
        return $user;
    });

    return response()->json($users);
}

    

// public function applyTeamLeave(Request $request)
// {
//     // Validate the incoming request data
//     $validator = Validator::make($request->all(), [
//         'type_of_leave' => 'required|in:Short Leave,Half Day Leave,Full Day Leave,Work From Home Full Day,Work From Home Half Day',
//         'half' => 'nullable|required_if:type_of_leave,Half Day Leave,Work From Home Half Day|in:First Half,Second Half',
//         'start_date' => 'required|date',
//         'end_date' => 'nullable|date|after_or_equal:start_date',
//         'start_time' => 'nullable|date_format:H:i',
//         'end_time' => 'nullable|date_format:H:i|after:start_time',
//         'reason' => 'required|string',
//         'contact_during_leave' => 'required|string|max:15',
//         'selected_user' => 'required|exists:users,id',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['error' => $validator->errors()], 422);
//     }

//     // Additional handling for Work From Home leave types
//     if (in_array($request->type_of_leave, ['Work From Home Full Day', 'Work From Home Half Day'])) {
//         $request->merge([
//             'start_time' => null,
//             'end_time' => null,
//         ]);
        
//         // Only clear half if it's Full Day
//         if ($request->type_of_leave === 'Work From Home Full Day') {
//             $request->merge(['half' => null]);
//         }
//     }

//     // Create the leave record
//     $leave = Leave::create([
//         'user_id' => $request->selected_user,
//         'type_of_leave' => $request->type_of_leave,
//         'half' => in_array($request->type_of_leave, ['Half Day Leave', 'Work From Home Half Day']) 
//                   ? $request->half 
//                   : null,
//         'start_date' => $request->start_date,
//         'end_date' => $request->type_of_leave === 'Short Leave' 
//                      ? null 
//                      : $request->end_date,
//         'start_time' => $request->start_time,
//         'end_time' => $request->end_time,
//         'reason' => $request->reason,
//         'contact_during_leave' => $request->contact_during_leave,
//         'last_updated_by' => Auth::user()->name,
//         'status' => 'pending',
//     ]);

//     return response()->json(['message' => 'Leave applied successfully!', 'data' => $leave], 201);
// }
    
    
public function show($id)
{
    // Fetch leave with user name and profile image
    $leave = Leave::join('users', 'leaves.user_id', '=', 'users.id')
        ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->where('leaves.id', $id)
        ->select(
            'leaves.*',
            'users.name as employee_name',
            'user_profiles.user_image'
        )
        ->first();

    if (!$leave) {
        return response()->json([
            'success' => false,
            'message' => 'Leave not found.',
        ], 404);
    }

    // Construct employee image URL
    $userImageUrl = $leave->user_image ? asset('uploads/' . $leave->user_image) : null;

    // Return enriched leave data
    return response()->json([
        'success' => true,
        'data' => [
            ...$leave->toArray(),
            'employee_name' => $leave->employee_name,
            'employee_image' => $userImageUrl,
        ],
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

    if ($request->filled('status')) {
        $query->where('leaves.status', $request->status);
    }

    if ($request->filled('created_date')) {
        $query->whereDate('leaves.created_at', $request->created_date);
    }

    // Join with users and user_profiles tables
    $query->join('users', 'leaves.user_id', '=', 'users.id')
          ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
          ->select(
              'leaves.*',
              'users.name as employee_name',
              'leaves.last_updated_by',
              'user_profiles.user_image'
          );

    // Fetch the leaves
    $leaves = $query->orderBy('leaves.created_at', 'desc')->get();

    // Transform the data
    $formattedLeaves = $leaves->map(function ($leave) {
        // Icons for leave types
        $leaveIcons = [
            'Full Day Leave' => '<i class="fas fa-umbrella-beach" style="color: #f39c12;"></i>',
            'Half Day Leave' => '<i class="fas fa-hourglass-half" style="color: #ff6347;"></i>',
            'Short Leave' => '<i class="fas fa-clock" style="color: #3498db;"></i>',
            'Work From Home Full Day' => '<i class="fas fa-home" style="color: #2ecc71;"></i>'
        ];

        // Determine icon for type_of_leave
        $icon = $leaveIcons[$leave->type_of_leave] ?? '<i class="fas fa-briefcase" style="color: #4682b4;"></i>';

        // Format leave type with icon
       $start = \Carbon\Carbon::parse($leave->start_date);
$end = \Carbon\Carbon::parse($leave->end_date);

$typeFormatted = $icon . ' ' . $leave->type_of_leave;


        // Day of the week formatting
        $startDayOfWeek = \Carbon\Carbon::parse($leave->start_date)->format('l');
        $endDayOfWeek = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('l') : $startDayOfWeek;

        // Time range formatting (IST timezone) - Convert 24-hour to 12-hour format
        if ($leave->start_time && $leave->end_time) {
            // Parse the time and format to 12-hour with AM/PM
            $startTimeIST = \Carbon\Carbon::createFromFormat('H:i:s', $leave->start_time)->format('h:i A');
            $endTimeIST = \Carbon\Carbon::createFromFormat('H:i:s', $leave->end_time)->format('h:i A');
            $typeFormatted .= "";
        }

        // User image URL
        $userImageUrl = $leave->user_image ? asset('uploads/' . $leave->user_image) : null;

        return [
            'id' => $leave->id,
            'employee_name' => $leave->employee_name,
            'employee_image' => $userImageUrl,
            'type' => $typeFormatted,
            'duration' => $this->calculateDuration($leave),
            'status' => ucfirst($leave->status),
            'created_at' => $leave->created_at->format('F d, Y'),
            'updated_by' => $leave->last_updated_by,
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
        ->whereIn('type_of_leave', ['Short Leave', 'Half Day Leave', 'Full Day Leave']) // Filter specific leave types
        ->whereHas('user', function ($query) {
            $query->where('status', '1'); // Fetch only users with status '1'
        })
        ->where(function ($query) use ($selectedDate) {
            // For Full Day Leave, check if the selected date is between start_date and end_date
            $query->where(function ($q) use ($selectedDate) {
                $q->whereIn('type_of_leave', ['Full Day Leave', 'Work From Home Full Day'])
                  ->whereDate('start_date', '<=', $selectedDate)
                  ->whereDate('end_date', '>=', $selectedDate);
            })
            // For Half Day Leave and Short Leave, check if the selected date matches the start_date
            ->orWhere(function ($q) use ($selectedDate) {
                $q->whereIn('type_of_leave', ['Half Day Leave', 'Short Leave', 'Work From Home Half Day'])
                  ->whereDate('start_date', $selectedDate);
            });
        })
        ->with(['user:id,name,status', 'user.profile:user_id,user_image']) // Load user details and profile
        ->get()
        ->map(function ($leave) {
            $user = $leave->user;

            // Ensure the user is active before including them
            if ($user->status == '0') {
                return null;
            }

            $userImage = $user->profile->user_image ?? null; // Fetch user image
            $userImageUrl = $userImage ? asset('uploads/' . $userImage) : null; // Generate full image URL

            // Determine the leave description
            $leaveDescription = $leave->type_of_leave;
            
            if ($leave->type_of_leave === 'Short Leave') {
                // Format time for Short Leave
                $startTime = date('h:i A', strtotime($leave->start_time));
                $endTime = date('h:i A', strtotime($leave->end_time));
                $leaveDescription .= " (from $startTime to $endTime)";
            } 
            elseif ($leave->type_of_leave === 'Half Day Leave' && $leave->half || $leave->type_of_leave === 'Work From Home Half Day' && $leave->half) {
                $leaveDescription .= " ({$leave->half})";
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $leave->status, // Leave status
                'type_of_leave' => $leaveDescription, // Updated leave description
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
    $selectedDate = $request->query('date'); 
    $date = \Carbon\Carbon::parse($selectedDate);

    // Fetch WFH Full Day
    $fullDayLeaves = Leave::where('leaves.type_of_leave', 'Work From Home Full Day')
        ->where('leaves.status', 'approved')
        ->whereDate('leaves.start_date', '<=', $date)
        ->whereDate('leaves.end_date', '>=', $date)
        ->join('users', 'leaves.user_id', '=', 'users.id')
        ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->where('users.status', '1')
        ->select(
            'users.name as user_name',
            'leaves.start_date',
            'leaves.end_date',
            'leaves.type_of_leave',
            'user_profiles.user_image'
        )
        ->orderBy('leaves.start_date', 'asc')
        ->get();

    // Fetch WFH Half Day only for the selected date
    $halfDayLeaves = Leave::where('leaves.type_of_leave', 'Work From Home Half Day')
        ->where('leaves.status', 'approved')
        ->whereDate('leaves.start_date', '=', $date)
        ->join('users', 'leaves.user_id', '=', 'users.id')
        ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->where('users.status', '1')
        ->select(
            'users.name as user_name',
            'leaves.start_date',
            'leaves.end_date',
            'leaves.type_of_leave',
            'leaves.half',
            'user_profiles.user_image'
        )
        ->orderBy('leaves.start_date', 'asc')
        ->get();

    // Merge and format results
    $mergedLeaves = collect();

    foreach ($fullDayLeaves as $leave) {
        $mergedLeaves->push([
            'user_name' => $leave->user_name,
            'user_image' => $leave->user_image 
                ? asset('uploads/' . $leave->user_image)
                : asset('img/CWlogo.jpeg'),
            'type_of_leave' => $leave->type_of_leave,
            'half' => null,
            'date_range' => [
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
            ],
        ]);
    }

    foreach ($halfDayLeaves as $leave) {
        $mergedLeaves->push([
            'user_name' => $leave->user_name,
            'user_image' => $leave->user_image
                ? asset('uploads/' . $leave->user_image)
                : asset('img/CWlogo.jpeg'),
            'type_of_leave' => $leave->type_of_leave,
            'half' => $leave->half,
            'date_range' => [
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
            ],
        ]);
    }

    return response()->json([
        'success' => true,
        'data' => $mergedLeaves,
    ]);
}

public function upcomingApprovedLeaves(Request $request)
{
    $months = (int)$request->input('months', 2);

    $today = now()->startOfDay(); // today's date
    $endDate = now()->addMonths($months)->endOfMonth(); // end of range

    $leaves = Leave::with(['user.profile'])
        ->whereIn('status', ['pending', 'approved', 'hold'])
        ->whereIn('type_of_leave', ['Short Leave', 'Half Day Leave', 'Full Day Leave',])
        ->where(function ($query) use ($today, $endDate) {
            $query->whereBetween('start_date', [$today, $endDate])
                  ->orWhereBetween('end_date', [$today, $endDate])
                  ->orWhere(function ($q) use ($today) {
                      $q->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today);
                  });
        })
        ->orderBy('start_date')
        ->get()
        ->map(function ($leave) {
            $startTime12hr = null;
            $endTime12hr = null;
            $hours = null;

            if ($leave->type_of_leave === 'Short Leave' && $leave->start_time && $leave->end_time) {
                $start = Carbon::createFromFormat('H:i:s', $leave->start_time);
                $end = Carbon::createFromFormat('H:i:s', $leave->end_time);

                $startTime12hr = $start->format('g:i A');
                $endTime12hr = $end->format('g:i A');
                $hours = $start->diffInHours($end);
            }

            $user = $leave->user;
            $userImage = $user->profile->user_image ?? null;
            $userImageUrl = $userImage ? asset('uploads/' . $userImage) : null;

            return [
                'id' => $leave->id,
                'type_of_leave' => $leave->type_of_leave,
                'half' => $leave->half,
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
                'created_at' => $leave->created_at,
                'start_time' => $startTime12hr,
                'end_time' => $endTime12hr,
                'hours' => $hours,
                'status' => $leave->status,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image' => $userImageUrl,
                ]
            ];
        });

    return response()->json($leaves);
}


public function upcomingWFHLeaves(Request $request)
{
    $months = (int)$request->input('months', 2);

    // Start from today instead of start of month
    $startDate = now()->startOfDay();
    $endDate = now()->addMonths($months)->endOfDay();
    

    $leaves = Leave::with(['user.profile'])
        ->whereIn('status', ['pending', 'approved', 'hold'])
        ->whereIn('type_of_leave', ['Work From Home Full Day', 'Work From Home Half Day'])
        ->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                      $q->where('start_date', '<', $startDate)
                        ->where('end_date', '>', $endDate);
                  });
        })
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($leave) {
            $user = $leave->user;
            $userImage = $user->profile->user_image ?? null;
            $userImageUrl = $userImage ? asset('uploads/' . $userImage) : null;

            return [
                'id' => $leave->id,
                'type_of_leave' => $leave->type_of_leave,
                'half' => $leave->half,
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
                'created_at' => $leave->created_at,
                'status' => $leave->status,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image' => $userImageUrl,
                ]
            ];
        });

    return response()->json([
        'success' => true,
        'data' => $leaves
    ]);
}


}
