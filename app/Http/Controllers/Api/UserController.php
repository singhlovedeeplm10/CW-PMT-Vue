<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function testUser()
    {
        return view('test');
    }

    public function index($page = 1)
    {
        // Fetch paginated users, setting the page parameter in the query
        $users = User::paginate(5, ['*'], 'page', $page);

        // Return the paginated data as JSON
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

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function updateUser(Request $request, $id)
{
    // Validate the request
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id, // Ensure unique email excluding the current user
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

    // Update the user details
    $user->name = $request->name;
    $user->email = $request->email;
    $user->status = $request->status;
    $user->save();

    // Return a success response
    return response()->json([
        'success' => true,
        'message' => 'User updated successfully.',
        'data' => $user,
    ]);
}
}
