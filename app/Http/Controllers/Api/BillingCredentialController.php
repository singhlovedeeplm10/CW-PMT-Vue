<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillingCredential;
use Illuminate\Support\Facades\Validator;

class BillingCredentialController extends Controller
{
    /**
     * Store a new billing credential.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCredentials(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:Upwork,Freelancer',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'admin_id' => 'required|exists:users,id',
        ]);

        // If validation fails, return a response with errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create and save the billing credential
        $billingCredential = BillingCredential::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
            'username' => $request->username,
            'password' => bcrypt($request->password), // Encrypting password for security
            'admin_id' => $request->admin_id,
        ]);

        // Return a success response with the created data
        return response()->json(['message' => 'Billing Credential created successfully', 'data' => $billingCredential], 201);
    }
}
