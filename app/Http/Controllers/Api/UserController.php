<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
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
    
        $user = User::findOrFail($id);
    
        // Update User Table
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
            'password' => $request->filled('password') ? Hash::make($validated['password']) : $user->password,
        ]);
    
        // Handle User Profile Update
        $profileData = [
            'address' => $validated['address'] ?? null,
            'qualifications' => $validated['qualifications'] ?? null,
            'employee_code' => $validated['employee_code'] ?? null,
            'user_DOB' => $validated['user_DOB'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'contact' => $validated['contact'] ?? null,
        ];
    
        // Handle Image Upload
        if ($request->hasFile('user_image')) {
            $imagePath = $request->file('user_image')->store('profile_images', 'public');
            $profileData['user_image'] = $imagePath;
        }
    
        // Update or Create User Profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );
    
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
    // Retrieve user profile
    $userProfile = $user->profile; // Assuming the relationship is defined as 'profile' in User model

    return response()->json([
        'userData' => $user,
        'userProfile' => $userProfile,
    ]);
}

}
