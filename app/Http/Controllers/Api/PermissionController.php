<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function addPermissions(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        // Create new permission
        $permission = Permission::create([
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        // Return response
        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully',
            'data' => $permission
        ], 201);
    }

    public function assignPermissionToUser(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'permission_id' => 'required|exists:permissions,id', // Remove user_id validation
        ]);
    
        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }
    
        // Get the logged-in user's ID
        $userId = auth()->id(); // Assuming you have implemented authentication
    
        // Create new user permission
        $userPermission = UserPermission::create([
            'user_id' => $userId, // Use logged-in user's ID
            'permission_id' => $request->permission_id,
        ]);
    
        // Return response
        return response()->json([
            'success' => true,
            'message' => 'Permission assigned to user successfully',
            'data' => $userPermission
        ], 201);
    }    
    
}
