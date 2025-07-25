<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Trainee;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TraineeController extends Controller
{
  public function addTrainee(Request $request)
{
    $validator = Validator::make($request->all(), [
        'trainee_name' => 'required|string|max:255',
        'trainee_email' => 'required|email|unique:trainees,trainee_email',
        'trainee_DOB' => 'nullable|date',
        'gender' => 'nullable|in:male,female',
        'trainee_qualifications' => 'nullable|string',
        'trainee_contact' => 'nullable|string|max:10',
        'training_start_date' => 'nullable|date',
        'training_end_date' => 'nullable|date|after_or_equal:training_start_date',
        'training_note' => 'nullable|string',
        'status' => 'nullable|in:active,completed,not completed',
        'trainee_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $traineeData = [
        'trainee_name' => $request->trainee_name,
        'trainee_email' => $request->trainee_email,
        'trainee_DOB' => $request->trainee_DOB,
        'gender' => $request->gender,
        'trainee_qualifications' => $request->trainee_qualifications,
        'trainee_contact' => $request->trainee_contact,
        'training_start_date' => $request->training_start_date,
        'training_end_date' => $request->training_end_date,
        'training_note' => $request->training_note,
        'status' => $request->status ?? 'active',
    ];

    // Handle image upload
    if ($request->hasFile('trainee_image')) {
        $uploadPath = public_path('uploads/trainee_images');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $imageName = time() . '_' . $request->file('trainee_image')->getClientOriginalName();
        $request->file('trainee_image')->move($uploadPath, $imageName);
        $traineeData['trainee_image'] = 'trainee_images/' . $imageName;
    }

    $trainee = Trainee::create($traineeData);

    return response()->json([
        'message' => 'Trainee added successfully.',
        'trainee' => $trainee,
    ], 201);
}
public function getTrainees(Request $request)
{
    $query = Trainee::query();

    // Search filter (name, email, or contact)
    if ($request->has('query') && $request->input('query') != '') {
        $searchTerm = $request->input('query');
        $query->where(function ($q) use ($searchTerm) {
            $q->where('trainee_name', 'like', '%' . $searchTerm . '%')
              ->orWhere('trainee_email', 'like', '%' . $searchTerm . '%')
              ->orWhere('trainee_contact', 'like', '%' . $searchTerm . '%');
        });
    }

    // Status filter
    if ($request->has('status') && $request->input('status') != '') {
        $query->where('status', $request->input('status'));
    }

    // Get all trainees without pagination
    $trainees = $query->orderBy('created_at', 'desc')->get();

    // Format the response without pagination data
    return response()->json([
        'data' => $trainees->map(function($trainee) {
            return [
                'id' => $trainee->id,
                'trainee_name' => $trainee->trainee_name,
                'trainee_email' => $trainee->trainee_email,
                'trainee_contact' => $trainee->trainee_contact,
                'status' => $trainee->status,
                'training_note' => $trainee->training_note,
                'training_start_date' => $trainee->training_start_date,
                'training_end_date' => $trainee->training_end_date,
                'trainee_image' => $trainee->trainee_image // This will be the filename only
            ];
        })
    ]);
}
public function show($id)
{
    $trainee = Trainee::findOrFail($id);
    return response()->json($trainee);
}
public function update(Request $request, Trainee $trainee)
{
    // Validate the request data
     $validatedData = $request->validate([
        'trainee_name' => 'required|string|max:255',
        'trainee_email' => 'required|email|max:255|unique:trainees,trainee_email,' . $trainee->id,
        'trainee_DOB' => 'nullable|date|sometimes',
        'gender' => 'nullable|in:male,female|sometimes',
        'trainee_qualifications' => 'nullable|string|sometimes',
        'trainee_contact' => 'nullable|digits:10|sometimes',
        'training_start_date' => 'nullable|date|sometimes',
        'training_end_date' => 'nullable|date|after_or_equal:training_start_date|sometimes',
        'training_note' => 'nullable|string|sometimes',
        'status' => 'required|in:active,completed,not completed',
        'trainee_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    // Handle image upload if present
    if ($request->hasFile('trainee_image')) {
        $uploadPath = public_path('uploads/trainee_images');
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Generate unique filename
        $imageName = time() . '_' . $request->file('trainee_image')->getClientOriginalName();
        
        // Move uploaded file
        $request->file('trainee_image')->move($uploadPath, $imageName);
        
        // Store relative path in database
        $validatedData['trainee_image'] = 'trainee_images/' . $imageName;

        // Delete old image if exists
        if ($trainee->trainee_image) {
            $oldImagePath = public_path('uploads/' . $trainee->trainee_image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }
  // Convert empty strings to null for nullable fields
    $fieldsToNullify = [
        'trainee_DOB',
        'gender',
        'trainee_qualifications',
        'trainee_contact',
        'training_start_date',
        'training_end_date',
        'training_note'
    ];

    foreach ($fieldsToNullify as $field) {
        if (array_key_exists($field, $validatedData) && $validatedData[$field] === '') {
            $validatedData[$field] = null;
        }
    }
    // Update the trainee
    $trainee->update($validatedData);

    return response()->json([
        'message' => 'Trainee updated successfully',
        'data' => $trainee
    ]);
}
}
