<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    // Override the login method to handle both API and web login
    public function login(Request $request)
    {
        // Validate the login credentials
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user();

            // Generate and delete any existing tokens for the user (optional)
            $user->tokens()->delete();
            $token = $user->createToken('authToken')->plainTextToken;

            // Check if the request expects JSON (API request)
            if ($request->expectsJson()) {
                // Return JSON response for API
                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $user
                ]);
            } else {
                // Redirect to home with response for web login
                return redirect($this->redirectTo)->with([
                    'loginResponse' => json_encode([
                        'message' => 'Login successful',
                        'token' => $token,
                        'user' => $user
                    ])
                ]);
            }
        }

        // If login attempt fails, return appropriate response
        return $this->sendFailedLoginResponse($request);
    }
}
