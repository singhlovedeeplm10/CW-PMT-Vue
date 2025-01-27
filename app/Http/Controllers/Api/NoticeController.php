<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use Illuminate\Support\Facades\Validator;


class NoticeController extends Controller
{
    public function storeNotices(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Save the validated data into the database
        $notice = Notice::create($request->only('title', 'description', 'start_date', 'end_date'));

        return response()->json(['message' => 'Notice created successfully', 'data' => $notice], 201);
    }

    public function getNotices()
    {
        $notices = Notice::all();
        return response()->json($notices);
    }

    public function deleteNotice($id)
    {
        try {
            $notice = Notice::findOrFail($id); // Find the notice by ID
            $notice->delete(); // Delete the notice
            return response()->json(['message' => 'Notice deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting notice', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateNotice(Request $request, $id) {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
    
        $notice = Notice::findOrFail($id);
        $notice->update($request->all());
    
        return response()->json(['message' => 'Notice updated successfully'], 200);
    }
    
}
