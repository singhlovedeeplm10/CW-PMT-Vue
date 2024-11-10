<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    public function addUserdetails(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'address' => 'nullable|string|max:255',
            'qualifications' => 'nullable|string|max:255',
            'employee_code' => 'nullable|string|max:50',
            'academic_documents' => 'nullable|string|max:255',
            'identification_documents' => 'nullable|string|max:255',
            'offer_letter' => 'nullable|string|max:255',
            'joining_letter' => 'nullable|string|max:255',
            'contract' => 'nullable|string|max:255',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        // Create new user profile
        $userProfile = UserProfile::create([
            'user_id' => $request->user_id,
            'address' => $request->address,
            'qualifications' => $request->qualifications,
            'employee_code' => $request->employee_code,
            'academic_documents' => $request->academic_documents,
            'identification_documents' => $request->identification_documents,
            'offer_letter' => $request->offer_letter,
            'joining_letter' => $request->joining_letter,
            'contract' => $request->contract,
        ]);

        // Return response
        return response()->json([
            'success' => true,
            'message' => 'User profile created successfully',
            'data' => $userProfile
        ], 201);
    }
}
