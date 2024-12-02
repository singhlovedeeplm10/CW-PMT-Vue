<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        // Check user credentials
        $user = User::where('email', $request->email)->first();
    
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }
    
        // Generate token
        $token = $user->createToken('authToken')->plainTextToken;
    
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'lastLoginDate' => now()->toDateString(),
        ]);
    }
    

    public function getUser(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }

    public function getUserRole()
    {
        $user = Auth::user(); // Get the authenticated user
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $role = $user->role; // Assuming role is defined as a relationship
        return response()->json(['role' => $role->title]);
    }

    public function logout(Request $request)
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
