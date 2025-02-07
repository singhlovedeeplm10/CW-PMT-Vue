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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function testUser()
    {
        return view('test');
    }

    public function index(Request $request, $page = 1)
    {
        $query = User::query();
    
        // Filter by name
        if ($request->has('name') && $request->name != '') {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
    
        // Filter by email
        if ($request->has('email') && $request->email != '') {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
    
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
    
        // Fetch paginated users
        $users = $query->paginate(5, ['*'], 'page', $page);
    
        // Return the filtered and paginated data as JSON
        return response()->json($users);
    }
    

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

        // Send Welcome Email
        try {
            Mail::to($user->email)->send(new WelcomeMail(['name' => $user->name]));
        } catch (\Exception $e) {
            return response()->json(['message' => 'User created but email could not be sent. ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'User created successfully, and email sent!', 'user' => $user], 201);
    }

    public function updateUser(Request $request, $id)
{
    // Define the rule for email validation with the exception for the current user's email
    $emailRule = 'required|email|unique:users,email,' . $id;

    // Validate the request
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => $emailRule, // Use the rule with the exception for the current user's email
        'status' => 'required|in:0,1',
        'password' => 'nullable|min:6',
        'address' => 'nullable|string|max:255',
        'qualifications' => 'nullable|string|max:255',
        'employee_code' => 'nullable|string|max:255',
        'user_DOB' => 'nullable|date',
        'user_image' => 'nullable|image|mimes:jpeg,png,jpg', // Image validation
        'gender' => 'nullable|in:male,female', // Validation for gender
        'contact' => 'nullable|digits:10', // Validation for contact
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

    // Handle image upload
    if ($request->hasFile('user_image')) {
        $imagePath = $request->file('user_image')->store('profile_images', 'public');
        $profileData['user_image'] = $imagePath;
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

    public function getUserProfile()
{
    $user = auth()->user(); // Get logged-in user
    $profile = $user->profile; // Use the relationship to get profile data

    return response()->json([
        'name' => $user->name,
        'email' => $user->email,
        'status' => $user->status,
        'dob' => $profile->user_DOB,
        'address' => $profile->address,
        'user_image' => $profile->user_image,
        'employee_code' => $profile->employee_code,
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

    $query = User::with(['profile', 'attendances', 'leaves' => function ($query) {
        $query->where('status', 'approved');
    }])
    ->whereHas('profile')
    ->orderBy('name', 'asc');

    // Apply search filters
    if ($request->has('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    // Apply status filter (default to active if not provided)
    $status = $request->has('status') ? $request->status : '1';
    $query->where('status', $status);

    $employees = $query->get()->map(function ($user) use ($startDate, $endDate) {
        $totalWFO = $user->attendances->whereBetween('clockin_time', [$startDate, $endDate])
            ->whereNull('leave_id')
            ->count();

        // Calculate total WFH days by computing the difference between start_date and end_date (inclusive)
        $totalWFH = $user->leaves->where('type_of_leave', 'Work From Home')
            ->filter(function ($leave) use ($startDate, $endDate) {
                return ($leave->start_date >= $startDate && $leave->start_date <= $endDate) ||
                       ($leave->end_date && $leave->end_date >= $startDate && $leave->end_date <= $endDate);
            })
            ->reduce(function ($sum, $leave) {
                $days = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
                return $sum + $days;
            }, 0);

        $totalLeaves = $user->leaves
            ->filter(function ($leave) use ($startDate, $endDate) {
                return in_array($leave->type_of_leave, ['Full Day Leave', 'Half Day Leave']) &&
                    (
                        ($leave->start_date >= $startDate && $leave->start_date <= $endDate) ||
                        ($leave->end_date && $leave->end_date >= $startDate && $leave->end_date <= $endDate)
                    );
            })
            ->reduce(function ($sum, $leave) {
                return $sum + ($leave->type_of_leave === 'Full Day Leave' ? 1 : 0.5);
            }, 0);

        $totalWorkingDays = $totalWFO + $totalWFH + $totalLeaves;

        return [
            'id' => $user->profile->employee_code ?? 'N/A',
            'image' => $user->profile->user_image ? asset('storage/' . $user->profile->user_image) : null,
            'name' => $user->name,
            'status' => $user->status,
            'totalWFO' => $totalWFO,
            'totalWFH' => $totalWFH,
            'totalLeave' => $totalLeaves,
            'totalWorkingDays' => $totalWorkingDays,
        ];
    });

    return response()->json($employees);
}


}
