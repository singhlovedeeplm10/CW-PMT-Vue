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
    
        // Validate credentials and status
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }
    
        if ($user->status === '0') { // Check if user is inactive
            return response()->json(['success' => false, 'message' => 'Your account is inactive. Please contact support.'], 403);
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

    public function getUserRole(Request $request)
    {
        return response()->json([
            'role' => Auth::user()->roles->pluck('name')->first(),
        ]);
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
