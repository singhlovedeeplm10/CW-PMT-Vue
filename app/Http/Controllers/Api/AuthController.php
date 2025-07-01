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

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
    }

    if ($user->status === '0') {
        return response()->json(['success' => false, 'message' => 'Your account is inactive. Please contact support.'], 403);
    }

    if ($user->status === '2') {
        return response()->json(['success' => false, 'message' => 'Your account is inactive. Please contact support.'], 403);
    }

    
    $tokenResult = $user->createToken('authToken');
    $token = $tokenResult->plainTextToken;
    $tokenResult->accessToken->expires_at = now()->addHours(12); // Set token with 12-hours expiration
    $tokenResult->accessToken->save();

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'lastLoginDate' => now()->toDateString(),
    ]);
}


    public function getUserDetails()
    {
        $user = Auth::user();
        $userProfile = $user->profile; // Ensure `profile()` is correctly defined in User model
    
        return response()->json([
            'user_name' => $user->name,
            'user_image' => $userProfile && $userProfile->user_image 
                ? asset('uploads/' . $userProfile->user_image) 
                : null, // Return full URL for the image
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
