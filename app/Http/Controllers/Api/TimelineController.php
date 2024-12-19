<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Timeline;
use App\Models\TimelineUpload;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TimelineController extends Controller
{
    public function uploadTimeline(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'uploadMedia' => 'nullable|array|max:10',  // maximum 10 images
            'uploadMedia.*' => 'nullable|mimes:jpg,jpeg,png,mp4|max:10240', // max 10MB for images/videos
            'uploadLink' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the timeline entry
        $timeline = Timeline::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'timeline_uploads_ids' => json_encode([]), // Placeholder for now
        ]);

        // Prepare to store file uploads
        $filePaths = [];
        $fileIds = [];

        // Handle media uploads (images/videos)
        if ($request->hasFile('uploadMedia')) {
            foreach ($request->file('uploadMedia') as $file) {
                $filePath = $file->store('timelines', 'public');
                $fileType = in_array($file->getClientOriginalExtension(), ['mp4', 'mov']) ? 'video' : 'image';
                $fileUpload = TimelineUpload::create([
                    'timeline_id' => $timeline->id,
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                    'file_link' => null,
                ]);
                $filePaths[] = $filePath;
                $fileIds[] = $fileUpload->id;
            }
        }

        // Handle the external media link (if any)
        if ($request->uploadLink) {
            $fileUpload = TimelineUpload::create([
                'timeline_id' => $timeline->id,
                'file_path' => null,
                'file_type' => 'image',  // default file type
                'file_link' => $request->uploadLink,
            ]);
            $fileIds[] = $fileUpload->id;
        }

        // Update the timeline entry with the uploaded file IDs
        $timeline->update([
            'timeline_uploads_ids' => json_encode($fileIds),
        ]);

        return response()->json([
            'message' => 'Timeline uploaded successfully!',
            'timeline' => $timeline,
            'uploads' => $filePaths,
        ], 201);
    }

public function getTimelineData()
{
    // Fetch timelines along with related uploads and the associated user, for all users
    $timelines = Timeline::with(['timelineUploads', 'user'])  // Eager load the timeline uploads and user data
        ->get();

    return response()->json($timelines);
}

}
