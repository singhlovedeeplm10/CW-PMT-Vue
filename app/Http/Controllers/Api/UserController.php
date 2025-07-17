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
        'password' => Hash::make($request->password), // Secure hashed password
    ]);

    // Generate Employee Code (CW00{id} or CW{id})
    $employee_code = 'CW' . str_pad($user->id, 3, '0', STR_PAD_LEFT);

    // Prepare credentials JSON
    $credentials = [
        [
            "label" => "Pmt-Tool",
            "username" => $request->email,
            "password" => $request->password, // Plaintext as requested
            "note" => " "
        ]
    ];

    // Create User Profile with Employee Code and Credentials
    UserProfile::create([
        'user_id' => $user->id,
        'employee_code' => $employee_code,
        'credentials' => json_encode($credentials),
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
        'password' => 'nullable|min:6',
        'permanent_address' => 'nullable|string|max:255',
        'temporary_address' => 'nullable|string',
        'qualifications' => 'nullable|string|max:255',
        'employee_code' => 'nullable|string|max:255',
        'user_DOB' => 'nullable|date',
        'user_image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
        'gender' => 'nullable|in:male,female',
        'contact' => 'nullable|digits:10',
        'alternate_contact_number' => 'nullable|digits_between:10,15',
        'date_of_joining' => 'nullable|date',
        'date_of_releaving' => 'nullable|date',
        'releaving_note' => 'nullable|string',
        'next_appraisal_month' => 'nullable|string',
        'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        'designation' => 'nullable|string|max:255',
        'current_salary' => 'nullable|numeric',
        'send_notification' => 'nullable|boolean',
        'appraisals' => 'nullable|json',
        'credentials' => 'nullable|json',
        'employee_personal_email' => 'nullable|email',
    ]);

    // Find the user by ID
    $user = User::findOrFail($id);

    // Initialize password variable with existing password
    $password = $user->password;

    // Check if password is provided in the request
    if ($request->filled('password')) {
        $password = Hash::make($validated['password']);
    }
    
    // Check if credentials are provided and look for Pmt-Tool password
    if ($request->filled('credentials')) {
        $credentials = json_decode($request->input('credentials'), true);
        foreach ($credentials as $credential) {
            if (isset($credential['label']) && $credential['label'] === 'Pmt-Tool' && isset($credential['password'])) {
                $password = Hash::make($credential['password']);
                break;
            }
        }
    }

    // Update basic user information
    $user->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => $password,
    ]);

    // Rest of your existing code remains the same...
    // Set status based on presence of date_of_releaving
    if (!empty($validated['date_of_releaving'])) {
        $user->status = '2'; // Releaved
    } else {
        $user->status = '1'; // Active
    }
    $user->save(); // Save the updated status

    // Prepare profile data
    $profileData = [
        'permanent_address' => $validated['permanent_address'] ?? null,
        'temporary_address' => $validated['temporary_address'] ?? null,
        'qualifications' => $validated['qualifications'] ?? null,
        'employee_code' => $validated['employee_code'] ?? null,
        'user_DOB' => $validated['user_DOB'] ?? null,
        'gender' => $validated['gender'] ?? null,
        'contact' => $validated['contact'] ?? null,
        'alternate_contact_number' => $validated['alternate_contact_number'] ?? null,
        'date_of_joining' => $validated['date_of_joining'] ?? null,
        'date_of_releaving' => $validated['date_of_releaving'] ?? null,
        'releaving_note' => $validated['releaving_note'] ?? null,
        'next_appraisal_month' => $validated['next_appraisal_month'] ?? null,
        'blood_group' => $validated['blood_group'] ?? null,
        'designation' => $validated['designation'] ?? null,
        'current_salary' => $validated['current_salary'] ?? null,
        'employee_personal_email' => $validated['employee_personal_email'] ?? null,
    ];

    // Handle JSON data for appraisals
    if ($request->filled('appraisals')) {
        $appraisals = json_decode($request->input('appraisals'), true);
        $profileData['appraisals'] = $this->sanitizeAppraisals($appraisals);
    }

    // Handle JSON data for credentials
    if ($request->filled('credentials')) {
        $credentials = json_decode($request->input('credentials'), true);
        $profileData['credentials'] = $this->sanitizeCredentials($credentials);
    }

    // Handle image upload
    if ($request->hasFile('user_image')) {
        $uploadPath = public_path('uploads/profile_images');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $imageName = time() . '_' . $request->file('user_image')->getClientOriginalName();
        $request->file('user_image')->move($uploadPath, $imageName);
        $profileData['user_image'] = 'profile_images/' . $imageName;

        // Delete old image if exists
        if ($user->profile && $user->profile->user_image) {
            $oldImagePath = public_path('uploads/' . $user->profile->user_image);
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

    // Return response
    return response()->json([
        'success' => true,
        'message' => 'User updated successfully.',
        'data' => $user->load('profile'),
    ]);
}


    /**
     * Sanitize and validate appraisals data
     */
    protected function sanitizeAppraisals(array $appraisals): array
{
    return array_map(function ($appraisal) {
        return [
            'date' => $appraisal['date'] ?? null,
            'amount' => isset($appraisal['amount']) ? (float)$appraisal['amount'] : null,
            'final_amount' => isset($appraisal['final_amount']) ? (float)$appraisal['final_amount'] : null,
            'note' => $appraisal['note'] ?? null,
            'show_to_employee' => isset($appraisal['show_to_employee']) ? (int)$appraisal['show_to_employee'] : 0,
            // Convert boolean to integer (1 or 0)
        ];
    }, $appraisals);
}

    /**
     * Sanitize and validate credentials data
     */
    protected function sanitizeCredentials(array $credentials): array
    {
        return array_map(function ($credential) {
            return [
                'label' => $credential['label'] ?? null,
                'username' => $credential['username'] ?? null,
                'password' => $credential['password'] ?? null, 
                'note' => $credential['note'] ?? null,
            ];
        }, $credentials);
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
    $user = auth()->user();
    $profile = $user->profile;

    $userId = $user->id;
    $formattedId = str_pad($userId, 3, "0", STR_PAD_LEFT);
    $employeeCode = "CW" . $formattedId;

    // Process appraisal data if exists
$appraisals = [];
if ($profile && !empty($profile->appraisals)) {
    $rawAppraisals = $profile->appraisals;
    
    // Debug: Log the raw data
    // error_log('Raw appraisals: ' . print_r($rawAppraisals, true));
    
    // Handle both string (JSON) and array formats
    if (is_string($rawAppraisals)) {
        $rawAppraisals = json_decode($rawAppraisals, true) ?? [];
    } elseif (!is_array($rawAppraisals)) {
        $rawAppraisals = [];
    }

    // Debug: Log after JSON decode
    // error_log('Decoded appraisals: ' . print_r($rawAppraisals, true));
    
    // Ensure we have an array of appraisals (not an associative array)
    if (isset($rawAppraisals[0]) && is_array($rawAppraisals[0])) {
        // Proper array of appraisals
        $appraisalsCollection = $rawAppraisals;
    } else {
        // Might be an associative array with numeric keys
        $appraisalsCollection = array_values($rawAppraisals);
    }

    // Filter appraisals to only include those marked to show to employee
    $filteredAppraisals = array_filter($appraisalsCollection, function($item) {
        // Debug: Check each item
        // error_log('Checking item: ' . print_r($item, true));
        
        return isset($item['show_to_employee']) && $item['show_to_employee'] == 1;
    });

    // Debug: Log filtered results
    // error_log('Filtered appraisals: ' . print_r($filteredAppraisals, true));
    
    // Only process and include appraisals if there are any to show
    if (!empty($filteredAppraisals)) {
        $appraisals = array_values(array_map(function($item) {
            return [
                'date' => $item['date'] ?? null,
                'appraisal_amount' => $item['amount'] ?? null,
                'revised_amount' => $item['final_amount'] ?? null,
                'note' => $item['note'] ?? null,
                'show_to_employee' => $item['show_to_employee'] ?? 0,
            ];
        }, $filteredAppraisals));
    }
}

// Debug: Final output
// error_log('Final appraisals: ' . print_r($appraisals, true));

    return response()->json([
        'name' => $user->name,
        'email' => $user->email,
        'status' => $user->status,
        'role' => $user->roles->first()->name ?? 'NA',
        'employee_code' => $employeeCode,

        // Profile fields
        'dob' => $profile?->user_DOB ?? null,
        'contact' => $profile?->contact ?? null,
        'gender' => $profile?->gender ?? null,
        'employee_personal_email' => $profile?->employee_personal_email ?? null,
        'permanent_address' => $profile?->permanent_address ?? null,
        'qualifications' => $profile?->qualifications ?? null,
        'academic_documents' => $profile?->academic_documents ?? null,
        'identification_documents' => $profile?->identification_documents ?? null,
        'offer_letter' => $profile?->offer_letter ?? null,
        'joining_letter' => $profile?->joining_letter ?? null,
        'contract' => $profile?->contract ?? null,
        'user_image' => $profile?->user_image ?? null,
        'date_of_joining' => $profile?->date_of_joining ?? null,
        'date_of_releaving' => $profile?->date_of_releaving ?? null,
        'releaving_note' => $profile?->releaving_note ?? null,
        'next_appraisal_month' => $profile?->next_appraisal_month ?? null,
        'blood_group' => $profile?->blood_group ?? null,
        'temporary_address' => $profile?->temporary_address ?? null,
        'alternate_contact_number' => $profile?->alternate_contact_number ?? null,
        'designation' => $profile?->designation ?? null,
        'current_salary' => $profile?->current_salary ?? null,
        
        // Appraisal data - will be empty array if no appraisals should be shown
        'appraisals' => $appraisals,
    ]);
}

    public function getUserCredentials(Request $request)
    {
        $userId = $request->user()->id;

        $profile = UserProfile::where('user_id', $userId)
            ->select('credentials')
            ->first();

        return response()->json([
            'success' => true,
            'credentials' => $profile->credentials ?? []
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
    $startDate = Carbon::now()->subMonth()->startOfMonth();
    $endDate = Carbon::now()->subMonth()->endOfMonth();

    // Apply date filters if provided
    if ($request->has('month') && $request->has('year')) {
        $startDate = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth();
    }

    $query = User::with([
        'attendances',
        'leaves' => function ($query) {
            $query->where('status', 'approved');
        },
        'profile:user_id,user_image'
    ])
    ->orderBy('name', 'asc');

    // Apply search filters
    if ($request->has('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    // Apply status filter (default to active if not provided)
    $status = $request->has('status') ? $request->status : '1';
    $query->where('status', $status);

    $employees = $query->get()->map(function ($user) use ($startDate, $endDate) {
        // Fetch attendance records for the given month range (including time component)
        $attendances = $user->attendances->filter(function ($attendance) use ($startDate, $endDate) {
            $clockIn = Carbon::parse($attendance->clockin_time);
            return $clockIn->between($startDate, $endDate);
        });

        // Group attendance records by date
        $uniqueAttendances = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->clockin_time)->toDateString();
        });

        // Fetch all approved leaves for the period
        $approvedLeaves = $user->leaves->filter(function ($leave) use ($startDate, $endDate) {
            $leaveStart = Carbon::parse($leave->start_date);
            $leaveEnd = $leave->end_date ? Carbon::parse($leave->end_date) : $leaveStart;
            
            return $leaveStart->lte($endDate) && $leaveEnd->gte($startDate);
        });

        // Separate WFH full-day and half-day leaves
        $wfhFullDayLeaves = $approvedLeaves->where('type_of_leave', 'Work From Home Full Day');
        $wfhHalfDayLeaves = $approvedLeaves->where('type_of_leave', 'Work From Home Half Day');

        // Count WFH full-day days with attendance
        $totalWFHFullDay = $wfhFullDayLeaves->reduce(function ($count, $leave) use ($uniqueAttendances, $startDate, $endDate) {
            $leaveStart = Carbon::parse($leave->start_date)->max($startDate);
            $leaveEnd = $leave->end_date 
                ? Carbon::parse($leave->end_date)->min($endDate)
                : $leaveStart->min($endDate);

            $daysInLeaveRange = $leaveStart->diffInDays($leaveEnd) + 1;
            
            $daysWithAttendance = $uniqueAttendances->filter(function ($attendance, $date) use ($leaveStart, $leaveEnd) {
                $currentDate = Carbon::parse($date);
                return $currentDate->between($leaveStart, $leaveEnd);
            })->count();

            return $count + min($daysInLeaveRange, $daysWithAttendance);
        }, 0);

        // Count WFH half-day days with attendance (add 0.5 for each day)
        $totalWFHHalfDay = $wfhHalfDayLeaves->reduce(function ($count, $leave) use ($uniqueAttendances, $startDate, $endDate) {
            $leaveStart = Carbon::parse($leave->start_date)->max($startDate);
            $leaveEnd = $leave->end_date 
                ? Carbon::parse($leave->end_date)->min($endDate)
                : $leaveStart->min($endDate);

            $daysInLeaveRange = $leaveStart->diffInDays($leaveEnd) + 1;
            
            $daysWithAttendance = $uniqueAttendances->filter(function ($attendance, $date) use ($leaveStart, $leaveEnd) {
                $currentDate = Carbon::parse($date);
                return $currentDate->between($leaveStart, $leaveEnd);
            })->count();

            return $count + (min($daysInLeaveRange, $daysWithAttendance) * 0.5);
        }, 0);

        // Combine both WFH types
        $totalWFH = $totalWFHFullDay + $totalWFHHalfDay;

        // Calculate initial WFO days before adjusting for other leaves
        $totalWFO = $uniqueAttendances->count() - $totalWFHFullDay - ($totalWFHHalfDay * 2); // Half days count as 0.5 WFH but reduce WFO by 1

        // Process other leaves (excluding both WFH types)
        $otherLeaves = $approvedLeaves->reject(function ($leave) {
            return in_array($leave->type_of_leave, ['Work From Home Full Day', 'Work From Home Half Day']);
        });

        // Adjust WFO based on other leave types
        foreach ($otherLeaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date)->max($startDate);
            $leaveEnd = $leave->end_date 
                ? Carbon::parse($leave->end_date)->min($endDate)
                : $leaveStart;

            for ($date = $leaveStart->copy(); $date->lte($leaveEnd); $date->addDay()) {
                $dateStr = $date->toDateString();

                if ($uniqueAttendances->has($dateStr)) {
                    if ($leave->type_of_leave === 'Half Day Leave') {
                        $totalWFO -= 0.5;
                    } elseif ($leave->type_of_leave === 'Full Day Leave') {
                        $totalWFO -= 1;
                    }
                }
            }
        }

        // Count total leave days (excluding WFH types)
        $totalLeaves = $otherLeaves->reduce(function ($sum, $leave) use ($startDate, $endDate) {
            $leaveStart = Carbon::parse($leave->start_date)->max($startDate);
            $leaveEnd = $leave->end_date 
                ? Carbon::parse($leave->end_date)->min($endDate)
                : $leaveStart;

            $count = 0;

            for ($date = $leaveStart->copy(); $date->lte($leaveEnd); $date->addDay()) {
                if ($leave->type_of_leave === 'Half Day Leave') {
                    $count += 0.5;
                } else {
                    $count += 1;
                }
            }

            return $sum + $count;
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
            'totalWFO' => max(0, $totalWFO),
            'totalWFH' => $totalWFH,
            'totalLeave' => $totalLeaves,
            'totalWorkingDays' => max(0, $totalWorkingDays),
        ];
    });

    return response()->json($employees);
}


   public function getEmployeeTimeLogsById(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $selectedMonth = $request->input('month', date('Y-m'));
    $searchQuery = $request->input('search', '');
    $startOfMonth = Carbon::parse($selectedMonth)->startOfMonth();
    $endOfMonth = Carbon::parse($selectedMonth)->endOfMonth();

    // Get all leaves for the month (WFH Full Day and Half Day)
    $leavesQuery = Leave::whereIn('type_of_leave', ['Work From Home Full Day', 'Work From Home Half Day'])
        ->where('status', 'Approved')
        ->where(function($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                  ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
                  ->orWhere(function($query) use ($startOfMonth, $endOfMonth) {
                      $query->where('start_date', '<=', $startOfMonth)
                            ->where('end_date', '>=', $endOfMonth);
                  });
        });

    $query = User::with(['attendances' => function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('clockin_time', [$startOfMonth, $endOfMonth])
                ->with('breaks')
                ->orderBy('clockin_time', 'desc');
        }])
        ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->select('users.*', 'user_profiles.user_image');

    $query->where('users.id', $user->id);

    if ($user->hasRole('Admin') && $searchQuery) {
        $query->orWhere('users.name', 'like', '%' . $searchQuery . '%');
    }

    $users = $query->get();
    $timeLogs = [];

    foreach ($users as $user) {
        // Get all WFH days for this user
        $userLeaves = (clone $leavesQuery)->where('user_id', $user->id)->get();
        $wfhDays = [];

        foreach ($userLeaves as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            
            for ($date = $start; $date->lte($end); $date->addDay()) {
                if ($date->between($startOfMonth, $endOfMonth)) {
                    $dayKey = $date->format('Y-m-d');
                    if ($leave->type_of_leave === 'Work From Home Full Day') {
                        $wfhDays[$dayKey] = 1.0;
                    } elseif ($leave->type_of_leave === 'Work From Home Half Day') {
                        $wfhDays[$dayKey] = 0.5;
                    }
                }
            }
        }

        $groupedAttendances = [];

        foreach ($user->attendances as $attendance) {
            $date = Carbon::parse($attendance->clockin_time)->format('Y-m-d');
            
            if (!isset($groupedAttendances[$date])) {
                $groupedAttendances[$date] = [
                    'clockin_time' => $attendance->clockin_time,
                    'clockout_time' => $attendance->clockout_time,
                    'total_hours' => $attendance->productive_hours ? Carbon::parse($attendance->productive_hours)->secondsSinceMidnight() : 0,
                    'total_break_time' => 0,
                    'is_wfh' => $wfhDays[$date] ?? 0,
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
        $clockInTime = $data['clockin_time'] ? Carbon::parse($data['clockin_time'])->format('h:i:s A') : 'NA';
        $clockOutTime = $data['clockout_time'] ? Carbon::parse($data['clockout_time'])->format('h:i:s A') : 'NA';
        $totalBreakFormatted = gmdate('H:i:s', $data['total_break_time']);
        $totalHoursFormatted = gmdate('H:i:s', $data['total_hours']);
        $totalProductiveHoursInSeconds = max(0, $data['total_hours'] - $data['total_break_time']);
        $totalProductiveHoursFormatted = gmdate('H:i:s', $totalProductiveHoursInSeconds);
        $imagePath = $user->user_image ? asset('uploads/' . $user->user_image) : asset('img/CWlogo.jpeg');

        // Determine WFH status
        $wfhStatus = '';
        if ($data['is_wfh'] == 1.0) {
            $wfhStatus = 'full';
        } elseif ($data['is_wfh'] == 0.5) {
            $wfhStatus = 'half';
        } else {
            // Check if it's a regular WFH day (not through leave)
            $isRegularWFH = $user->attendances->where('date', $date)->first()->is_wfh ?? false;
            if ($isRegularWFH) {
                $wfhStatus = 'full';
            }
        }

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
            'is_wfh' => $data['is_wfh'],
            'wfh_status' => $wfhStatus, // 'full', 'half', or empty string
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
                    'clock_in_out' => ($firstClockIn ? Carbon::parse($firstClockIn)->format('h:i:s A') : 'NA') . ' / ' .
                        ($lastClockOut ? Carbon::parse($lastClockOut)->format('h:i:s A') : 'NA'),
                    'total_break' => gmdate('H:i:s', $totalBreakSeconds),
                    'total_hours' => gmdate('H:i:s', $totalHoursSeconds),
                    'total_productive_hours' => gmdate('H:i:s', $productiveSeconds),
                ];
            }
        }

        return response()->json($timeLogs);
    }

       public function checkProfilePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $profile = UserProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $hasPassword = !empty($profile->profile_password);

        return response()->json([
            'has_password' => $hasPassword
        ]);
    }

    public function setProfilePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $profile = UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            []
        );

        $profile->profile_password = Hash::make($request->password);
        $profile->save();

        return response()->json(['message' => 'Profile password set successfully']);
    }

    public function verifyProfilePassword(Request $request)
{
    $request->validate([
        'password' => 'required|string'
    ]);

    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $profile = UserProfile::where('user_id', $user->id)->first();

    if (!$profile || !$profile->profile_password) {
        return response()->json(['error' => 'Profile password not set'], 404);
    }

    if (!Hash::check($request->password, $profile->profile_password)) {
        return response()->json(['success' => false, 'message' => 'Incorrect password']);
    }

    return response()->json(['success' => true, 'message' => 'Password verified']);
}
public function resetProfilePassword($id)
{
    try {
        // Find the user profile record
        $profile = UserProfile::where('user_id', $id)->first();

        if (!$profile) {
            return response()->json([
                'message' => 'User profile not found.'
            ], 404);
        }

        // Reset profile_password field
        $profile->profile_password = null;
        $profile->save();

        return response()->json([
            'message' => 'Profile password reset successfully.'
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Reset profile password error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to reset profile password.'
        ], 500);
    }
}

}
