<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Leave;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Breaks;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function assignUserRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $role = $request->input('role');
    
        if (!in_array($role, ['Admin', 'Employee'])) {
            return response()->json(['success' => false, 'message' => 'Invalid role specified']);
        }
    
        // Remove existing roles and assign the new one
        $user->syncRoles([$role]);
    
        return response()->json(['success' => true, 'message' => 'Role updated successfully']);
    }
    
    public function testUser()
    {
        return view('test');
    }

    public function showUsers(Request $request, $page = 1)
{
    $loggedInUserId = auth()->id(); // Get the ID of the logged-in user

    $query = User::query()
        ->select(
            'users.*',
            'user_profiles.user_image',
            'user_profiles.contact', // Add contact field
            'roles.name as role_name',
            DB::raw("CASE WHEN users.id = $loggedInUserId THEN 1 ELSE 0 END as logged_in_status"),
            DB::raw("CASE WHEN roles.name = 'Admin' THEN 'inherit' ELSE 'inherit' END as name_color"),
            DB::raw("CASE WHEN users.id = $loggedInUserId THEN 'none' ELSE 'none' END as border_color")
        )
        ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id');

    // Apply filters
    if ($request->has('name') && $request->name != '') {
        $query->where('users.name', 'like', '%' . $request->name . '%');
    }

    if ($request->has('email') && $request->email != '') {
        $query->where('users.email', 'like', '%' . $request->email . '%');
    }

    if ($request->has('status') && $request->status != '') {
        $query->where('users.status', $request->status);
    }

    // Fetch paginated users
    $users = $query->paginate(20);

    return response()->json($users);
}
    

//     public function addUser(Request $request)
// {
//     // Validate the incoming request
//     $validator = Validator::make($request->all(), [
//         'name' => 'required|string|max:255',
//         'email' => 'required|string|email|max:255|unique:users',
//         'password' => 'required|string|min:8',
//     ]);

//     if ($validator->fails()) {
//         return response()->json($validator->errors(), 422);
//     }

//     // Create a new user
//     $user = User::create([
//         'name' => $request->name,
//         'email' => $request->email,
//         'password' => Hash::make($request->password),
//     ]);

//     // Generate Employee Code (CW00{id} or CW{id})
//     $employee_code = 'CW' . str_pad($user->id, 3, '0', STR_PAD_LEFT);

//     // Create User Profile with Employee Code
//     UserProfile::create([
//         'user_id' => $user->id,
//         'employee_code' => $employee_code,
//     ]);

//     // Assign the default role "Employee" to the new user
//     $user->syncRoles(['Employee']);

//     // Send Welcome Email
//     try {
//         Mail::to($user->email)->send(new WelcomeMail(['name' => $user->name]));
//     } catch (\Exception $e) {
//         return response()->json(['message' => 'User created but email could not be sent. ' . $e->getMessage()], 500);
//     }

//     return response()->json(['message' => 'User created successfully, and email sent!', 'user' => $user], 201);
// }

public function addUser(Request $request)
{
    // Validate the incoming request
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Create a new user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    // Generate Employee Code (CW00{id} or CW{id})
    $employee_code = 'CW' . str_pad($user->id, 3, '0', STR_PAD_LEFT);

    // Create User Profile with Employee Code
    UserProfile::create([
        'user_id' => $user->id,
        'employee_code' => $employee_code,
    ]);

    // Assign the default role "Employee" to the new user
    $user->syncRoles(['Employee']);

    return response()->json(['message' => 'User created successfully!', 'user' => $user], 201);
}

   public function updateUser(Request $request, $id)
{
    // Define the rule for email validation with the exception for the current user's email
    $emailRule = 'required|email|unique:users,email,' . $id;

    // Validate the request
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => $emailRule,
        'status' => 'required|in:0,1',
        'password' => 'nullable|min:6',
        'address' => 'nullable|string|max:255',
        'qualifications' => 'nullable|string|max:255',
        'employee_code' => 'nullable|string|max:255',
        'user_DOB' => 'nullable|date',
        'user_image' => 'nullable|image|mimes:jpeg,png,jpg',
        'gender' => 'nullable|in:male,female',
        'contact' => 'nullable|digits:10',
    ]);

    // Find the user by ID
    $user = User::findOrFail($id);

    // Update the user table
    $user->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'status' => $validated['status'],
        'password' => $request->filled('password') ? Hash::make($validated['password']) : $user->password,
    ]);

    // Prepare profile data
    $profileData = [
        'address' => $validated['address'] ?? null,
        'qualifications' => $validated['qualifications'] ?? null,
        'employee_code' => $validated['employee_code'] ?? null,
        'user_DOB' => $validated['user_DOB'] ?? null,
        'gender' => $validated['gender'] ?? null,
        'contact' => $validated['contact'] ?? null,
    ];

    // Handle image upload - Updated to use custom path
    if ($request->hasFile('user_image')) {
        // Create directory if it doesn't exist
        $uploadPath = public_path('uploads/profile_images');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Generate unique filename
        $imageName = time().'_'.$request->file('user_image')->getClientOriginalName();
        
        // Move the file to the desired location
        $request->file('user_image')->move($uploadPath, $imageName);
        
        // Store relative path in database
        $profileData['user_image'] = 'profile_images/'.$imageName;

        // Delete old image if exists
        if ($user->profile && $user->profile->user_image) {
            $oldImagePath = public_path($user->profile->user_image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }

    // Update or create the user's profile
    $user->profile()->updateOrCreate(
        ['user_id' => $user->id],
        $profileData
    );

    // Return the response
    return response()->json([
        'success' => true,
        'message' => 'User updated successfully.',
        'data' => $user->load('profile'),
    ]);
}



public function updateStatus(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'status' => 'required|in:0,1', // Ensure valid status
        ]);

        // Find the user by ID
        $user = User::find($id);

        // If user not found, return an error response
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Update the user status
        $user->status = $request->status;
        $user->save();

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'data' => $user,
        ]);
    }

    public function userAccountDetails()
    {
        $user = auth()->user(); // Get logged-in user
        $profile = $user->profile; // Use the relationship to get profile data
    
        // Generate employee_code based on user ID
        $userId = $user->id;
        $formattedId = str_pad($userId, 3, "0", STR_PAD_LEFT); // Ensure minimum 3 digits
        $employeeCode = "CW" . $formattedId;
    
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'dob' => $profile?->user_DOB ?? null, // Check if $profile exists
            'contact' => $profile?->contact ?? null,
            'address' => $profile?->address ?? null,
            'user_image' => $profile?->user_image ?? null,
            'employee_code' => $employeeCode, // Dynamically generated employee code
        ]);
    }
    
public function edit(User $user)
{
    // Ensure eager loading of the user profile
    $user->load('profile');

    return response()->json([
        'userData' => $user,
        'userProfile' => $user->profile,
    ]);
}



// public function employeeAttendances(Request $request)
// {
//     // Set default previous month range if no date is provided
//     $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
//     $endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

//     // Apply date filters if provided
//     if ($request->has('month') && $request->has('year')) {
//         $startDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth()->toDateString();
//         $endDate = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth()->toDateString();
//     }

//     $query = User::with([
//         'attendances', 
//         'leaves' => function ($query) {
//             $query->where('status', 'approved');
//         },
//         'profile:user_id,user_image'
//     ])
//     ->where('id', '!=', 1) // This line excludes the user with ID 1
//     ->orderBy('name', 'asc');
    

//     // Apply search filters
//     if ($request->has('name')) {
//         $query->where('name', 'like', '%' . $request->name . '%');
//     }

//     // Apply status filter (default to active if not provided)
//     $status = $request->has('status') ? $request->status : '1';
//     $query->where('status', $status);

//     $employees = $query->get()->map(function ($user) use ($startDate, $endDate) {
//         // Fetch attendance records for the given month
//         $attendances = $user->attendances->whereBetween('clockin_time', [$startDate, $endDate]);

//         // Group attendance records by date
//         $uniqueAttendances = $attendances->groupBy(function ($attendance) {
//             return Carbon::parse($attendance->clockin_time)->toDateString();
//         });

//         // Fetch work-from-home leaves
//         $wfhLeaves = $user->leaves->where('type_of_leave', 'Work From Home Full Day')
//             ->filter(function ($leave) use ($startDate, $endDate) {
//                 return ($leave->start_date <= $endDate && (!$leave->end_date || $leave->end_date >= $startDate));
//             });

//         // Count days where the user has both an approved WFH leave and an attendance record
//         $totalWFH = $wfhLeaves->reduce(function ($count, $leave) use ($uniqueAttendances) {
//             $daysInLeaveRange = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date ?: $leave->start_date)) + 1;
//             $daysWithAttendance = $uniqueAttendances->filter(function ($attendance, $date) use ($leave) {
//                 return Carbon::parse($date)->between($leave->start_date, $leave->end_date ?: $leave->start_date);
//             })->count();
//             return $count + min($daysInLeaveRange, $daysWithAttendance);
//         }, 0);

//         // Count WFO days excluding WFH
//         $totalWFO = $uniqueAttendances->count() - $totalWFH;

//         // Count total leave days
//         $totalLeaves = $user->leaves
//             ->filter(function ($leave) use ($startDate, $endDate) {
//                 return in_array($leave->type_of_leave, ['Full Day Leave', 'Half Day Leave']) &&
//                     ($leave->start_date <= $endDate && (!$leave->end_date || $leave->end_date >= $startDate));
//             })
//             ->reduce(function ($sum, $leave) {
//                 return $sum + ($leave->type_of_leave === 'Full Day Leave' ? 1 : 0.5);
//             }, 0);

//         $totalWorkingDays = $totalWFO + $totalWFH;

//         // Generate employee code
//         $employeeCode = sprintf("CW%03d", $user->id);

//         return [
//             'id' => $employeeCode,
//             'image' => $user->profile && $user->profile->user_image 
//                 ? asset('storage/' . $user->profile->user_image) 
//                 : asset('img/CWlogo.jpeg'), // Default image if no user image exists
//             'name' => $user->name,
//             'status' => $user->status,
//             'totalWFO' => $totalWFO,
//             'totalWFH' => $totalWFH,
//             'totalLeave' => $totalLeaves,
//             'totalWorkingDays' => $totalWorkingDays,
//         ];
//     });

//     return response()->json($employees);
// }
public function employeeAttendances(Request $request)
{
    // Set default previous month range if no date is provided
    $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
    $endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

    // Apply date filters if provided
    if ($request->has('month') && $request->has('year')) {
        $startDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth()->toDateString();
    }

    $query = User::with([
        'attendances', 
        'leaves' => function ($query) {
            $query->where('status', 'approved');
        },
        'profile:user_id,user_image'
    ])
    ->where('id', '!=', 1) // Exclude user with ID 1
    ->orderBy('name', 'asc');
    
    // Apply search filters
    if ($request->has('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    // Apply status filter (default to active if not provided)
    $status = $request->has('status') ? $request->status : '1';
    $query->where('status', $status);

    $employees = $query->get()->map(function ($user) use ($startDate, $endDate) {
        // Fetch attendance records for the given month
        $attendances = $user->attendances->whereBetween('clockin_time', [$startDate, $endDate]);

        // Group attendance records by date
        $uniqueAttendances = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->clockin_time)->toDateString();
        });

        // Fetch all approved leaves for the period
        $approvedLeaves = $user->leaves->filter(function ($leave) use ($startDate, $endDate) {
            return ($leave->start_date <= $endDate && (!$leave->end_date || $leave->end_date >= $startDate));
        });

        // Separate WFH leaves
        $wfhLeaves = $approvedLeaves->where('type_of_leave', 'Work From Home Full Day');

        // Count WFH days with attendance
        $totalWFH = $wfhLeaves->reduce(function ($count, $leave) use ($uniqueAttendances) {
            $daysInLeaveRange = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date ?: $leave->start_date)) + 1;
            $daysWithAttendance = $uniqueAttendances->filter(function ($attendance, $date) use ($leave) {
                return Carbon::parse($date)->between($leave->start_date, $leave->end_date ?: $leave->start_date);
            })->count();
            return $count + min($daysInLeaveRange, $daysWithAttendance);
        }, 0);

        // Calculate total WFO days (initial count before adjusting for leaves)
        $totalWFO = $uniqueAttendances->count() - $totalWFH;

        // Process all leaves (excluding WFH which we already handled)
        $otherLeaves = $approvedLeaves->reject(function ($leave) {
            return $leave->type_of_leave === 'Work From Home Full Day';
        });

        // Adjust WFO days for leaves
        foreach ($otherLeaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date);
            $leaveEnd = Carbon::parse($leave->end_date ?: $leave->start_date);
            
            // Check each day in the leave period
            for ($date = $leaveStart; $date->lte($leaveEnd); $date->addDay()) {
                $dateStr = $date->toDateString();
                
                // Only process if within our date range and user has attendance
                if ($date->between($startDate, $endDate) && $uniqueAttendances->has($dateStr)) {
                    if ($leave->type_of_leave === 'Half Day Leave') {
                        // Subtract 0.5 for half day leaves
                        $totalWFO -= 0.5;
                    } elseif ($leave->type_of_leave === 'Full Day Leave') {
                        // Subtract 1 for full day leaves
                        $totalWFO -= 1;
                    }
                }
            }
        }

        // Count total leave days (for display)
        $totalLeaves = $otherLeaves->reduce(function ($sum, $leave) {
            return $sum + ($leave->type_of_leave === 'Full Day Leave' ? 1 : 0.5);
        }, 0);

        $totalWorkingDays = $totalWFO + $totalWFH;

        // Generate employee code
        $employeeCode = sprintf("CW%03d", $user->id);

        return [
            'id' => $employeeCode,
            'image' => $user->profile && $user->profile->user_image 
                ? asset('uploads/' . $user->profile->user_image) 
                : asset('img/CWlogo.jpeg'),
            'name' => $user->name,
            'status' => $user->status,
            'totalWFO' => max(0, $totalWFO), // Ensure we don't go negative
            'totalWFH' => $totalWFH,
            'totalLeave' => $totalLeaves,
            'totalWorkingDays' => max(0, $totalWorkingDays), // Ensure we don't go negative
        ];
    });

    return response()->json($employees);
}

public function getEmployeeTimeLogsById(Request $request)
{
    $user = Auth::user(); // Get the logged-in user

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401); // Handle unauthorized access
    }

    $selectedMonth = $request->input('month', date('Y-m'));
    $searchQuery = $request->input('search', '');
    $startOfMonth = Carbon::parse($selectedMonth)->startOfMonth();
    $endOfMonth = Carbon::parse($selectedMonth)->endOfMonth();

    // Join the user_profiles table to fetch the user_image
    $query = User::with(['attendances' => function ($query) use ($startOfMonth, $endOfMonth) {
        $query->whereBetween('clockin_time', [$startOfMonth, $endOfMonth])
              ->with('breaks');
    }])->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
       ->select('users.*', 'user_profiles.user_image'); // Select the user_image from user_profiles

    // Always include the logged-in user's data
    $query->where('users.id', $user->id);

    // If the user is an admin and a search query is provided, search for other users
    if ($user->hasRole('Admin') && $searchQuery) {
        $query->orWhere('users.name', 'like', '%' . $searchQuery . '%');
    }

    $users = $query->get();  // Get the users with their time logs and breaks

    $timeLogs = [];

    foreach ($users as $user) {
        $groupedAttendances = [];

        foreach ($user->attendances as $attendance) {
            $date = Carbon::parse($attendance->clockin_time)->format('Y-m-d');

            if (!isset($groupedAttendances[$date])) {
                $groupedAttendances[$date] = [
                    'clockin_time' => $attendance->clockin_time,
                    'clockout_time' => $attendance->clockout_time,
                    'total_hours' => $attendance->productive_hours ? Carbon::parse($attendance->productive_hours)->secondsSinceMidnight() : 0,
                    'total_break_time' => 0,
                ];
            } else {
                $groupedAttendances[$date]['clockin_time'] = min($groupedAttendances[$date]['clockin_time'], $attendance->clockin_time);
                $groupedAttendances[$date]['clockout_time'] = max($groupedAttendances[$date]['clockout_time'], $attendance->clockout_time);
                $groupedAttendances[$date]['total_hours'] += $attendance->productive_hours ? Carbon::parse($attendance->productive_hours)->secondsSinceMidnight() : 0;
            }

            $groupedAttendances[$date]['total_break_time'] += $attendance->breaks->sum(function ($break) {
                return Carbon::parse($break->break_time)->secondsSinceMidnight();
            });
        }

        foreach ($groupedAttendances as $date => $data) {
            // Format clockin_time and clockout_time in 12-hour format with AM/PM
            $clockInTime = $data['clockin_time'] ? Carbon::parse($data['clockin_time'])->format('h:i:s A') : 'N/A';
            $clockOutTime = $data['clockout_time'] ? Carbon::parse($data['clockout_time'])->format('h:i:s A') : 'N/A';
            $totalBreakFormatted = gmdate('H:i:s', $data['total_break_time']);
            $totalHoursFormatted = gmdate('H:i:s', $data['total_hours']);
            $totalProductiveHoursInSeconds = max(0, $data['total_hours'] - $data['total_break_time']);
            $totalProductiveHoursFormatted = gmdate('H:i:s', $totalProductiveHoursInSeconds);
            $imagePath = $user->user_image ? asset('uploads/' . $user->user_image) : asset('img/CWlogo.jpeg');

            $timeLogs[] = [
                'id' => $user->id,
                'image' => $imagePath,
                'name' => $user->name,
                'status' => $user->status,
                'date' => $date,
                'clock_in_out' => $clockInTime . ' / ' . $clockOutTime,
                'total_break' => $totalBreakFormatted,
                'total_hours' => $totalHoursFormatted,
                'total_productive_hours' => $totalProductiveHoursFormatted,
            ];
        }
    }

    return response()->json($timeLogs);
}


public function getEmployeeAttendanceTimelogs(Request $request)
{
    $userId = $request->query('user_id');
    $date = $request->query('date');

    // Fetch user details
    $user = User::find($userId);
    if (!$user) {
        return response()->json([]);
    }

    // Fetch detailed time logs
    $detailedLogs = Attendance::where('user_id', $userId)
        ->whereDate('clockin_time', $date)
        ->get(['clockin_time', 'clockout_time', 'productive_hours']);

    return response()->json($detailedLogs);
}

public function getEmployeeBreaksTimelogs(Request $request)
{
    $userId = $request->query('user_id');
    $date = $request->query('date');

    // Fetch user from users table
    $user = User::find($userId);
    if (!$user) {
        return response()->json([]);
    }

    // Fetch break details
    $breakLogs = Breaks::where('user_id', $userId)
        ->whereDate('end_time', $date)
        ->get(['end_time', 'break_time', 'reason'])
        ->map(function ($break) {
            $break->start_time = $break->end_time 
                ? \Carbon\Carbon::parse($break->end_time)->subSeconds(\Carbon\Carbon::parse($break->break_time)->secondsSinceMidnight())->toDateTimeString() 
                : null;
            return $break;
        });

    return response()->json($breakLogs);
}

public function getAllEmployeeTimeLogs()
{
    $currentMonth = now()->format('Y-m');
    $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
    $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

    $users = User::with(['attendances' => function ($query) use ($startOfMonth, $endOfMonth) {
        $query->whereBetween('clockin_time', [$startOfMonth, $endOfMonth])
              ->with('breaks');
    }])->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
       ->select('users.*', 'user_profiles.user_image')
       ->get();

    $timeLogs = [];

    foreach ($users as $user) {
        // Group attendances by date
        $groupedByDate = $user->attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->clockin_time)->format('Y-m-d');
        });

        foreach ($groupedByDate as $date => $attendances) {
            // Get the first clock-in and last clock-out
            $firstClockIn = $attendances->min('clockin_time');
            $lastClockOut = $attendances->max('clockout_time');

            // Sum breaks from all records for the day
            $totalBreakSeconds = $attendances->sum(function ($attendance) {
                return $attendance->breaks->sum(function ($break) {
                    return Carbon::parse($break->break_time)->secondsSinceMidnight();
                });
            });

            // Total hours = sum of productive_hours
            $totalHoursSeconds = $attendances->sum(function ($attendance) {
                return $attendance->productive_hours ? Carbon::parse($attendance->productive_hours)->secondsSinceMidnight() : 0;
            });

            $productiveSeconds = max(0, $totalHoursSeconds - $totalBreakSeconds);

            $imagePath = $user->user_image ? asset('uploads/' . $user->user_image) : asset('img/CWlogo.jpeg');

            $timeLogs[] = [
                'id' => $user->id,
                'image' => $imagePath,
                'name' => $user->name,
                'status' => $user->status,
                'date' => $date,
                'clock_in_out' => ($firstClockIn ? Carbon::parse($firstClockIn)->format('h:i:s A') : 'N/A') . ' / ' .
                                  ($lastClockOut ? Carbon::parse($lastClockOut)->format('h:i:s A') : 'N/A'),
                'total_break' => gmdate('H:i:s', $totalBreakSeconds),
                'total_hours' => gmdate('H:i:s', $totalHoursSeconds),
                'total_productive_hours' => gmdate('H:i:s', $productiveSeconds),
            ];
        }
    }

    return response()->json($timeLogs);
}

}
