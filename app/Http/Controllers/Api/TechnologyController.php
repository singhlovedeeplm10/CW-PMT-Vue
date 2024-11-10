<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TechnologyController extends Controller
{
    public function addTech(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:frontend,backend,dbms,frontend & backend',
            'admin_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create a new technology entry
        $technology = Technology::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'admin_id' => $request->admin_id,
        ]);

        return response()->json(['message' => 'Technology created successfully', 'technology' => $technology], 201);
    }
}
