<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LikesComment;
use App\Models\Timeline;
use App\Models\TimelineUpload;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
            'uploadMedia' => 'nullable|array|max:10',  // Maximum 10 images/videos
            'uploadMedia.*' => 'nullable|mimes:jpg,jpeg,png,mp4|max:10240', // Max 10MB for each file
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
                'file_path' => null, // No file path for external links
                'file_type' => null, // File type is null for links
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
        $timelines = Timeline::with(['timelineUploads', 'user'])->get();
    
        foreach ($timelines as $timeline) {
            // Calculate likes count for each timeline
            $likesCount = DB::table('likes_comments')
                ->where('timeline_id', $timeline->id)
                ->sum(DB::raw('JSON_LENGTH(likes)'));
            
            $timeline->likes_count = $likesCount; // Add likes count to timeline object
    
            foreach ($timeline->timelineUploads as $upload) {
                if ($upload->file_type === 'video' || $upload->file_type === 'image') {
                    $upload->file_url = $upload->file_path ? asset('storage/' . $upload->file_path) : null;
                }
    
                // Add thumbnail and icon for external links
                if ($upload->file_link) {
                    if (strpos($upload->file_link, 'youtube.com') !== false) {
                        $upload->thumbnail = $this->getYouTubeThumbnail($upload->file_link);
                        $upload->icon = 'fab fa-youtube'; // YouTube icon
                    } else {
                        $upload->thumbnail = $upload->file_link; // Assume it's an image URL
                        $upload->icon = 'fas fa-external-link-alt'; // Generic external link icon
                    }
                }
            }
        }
    
        return response()->json($timelines);
    }
    
    
    // Helper function to get YouTube thumbnail
    private function getYouTubeThumbnail($youtubeUrl)
    {
        preg_match('/v=([^&]+)/', $youtubeUrl, $matches);
        return isset($matches[1]) ? "https://img.youtube.com/vi/{$matches[1]}/hqdefault.jpg" : null;
    }

    public function likePost(Request $request)
    {
        $validated = $request->validate([
            'timeline_id' => 'required|integer',
            'timeline_uploads_id' => 'required|integer',
        ]);
    
        $like = DB::table('likes_comments')->where([
            ['timeline_id', '=', $validated['timeline_id']],
            ['timeline_uploads_id', '=', $validated['timeline_uploads_id']],
        ])->first();
    
        if ($like) {
            // Increment the like count
            $likes = json_decode($like->likes, true) ?? [];
            $likes[] = auth()->id(); // Add current user ID to likes array
    
            DB::table('likes_comments')
                ->where('id', $like->id)
                ->update(['likes' => json_encode($likes)]);
        } else {
            // Create a new like entry
            DB::table('likes_comments')->insert([
                'timeline_id' => $validated['timeline_id'],
                'timeline_uploads_id' => $validated['timeline_uploads_id'],
                'likes' => json_encode([auth()->id()]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    
        // Return the total likes count
        $totalLikes = count(json_decode($like->likes ?? '[]', true) ?? []);
        return response()->json(['success' => true, 'totalLikes' => $totalLikes]);
    } 
    
    public function postComment(Request $request)
    {
        $request->validate([
            'timeline_id' => 'required|json',
            'timeline_uploads_id' => 'required|json',
            'comment' => 'required|string',
        ]);

        LikesComment::create([
            'timeline_id' => $request->timeline_id,
            'timeline_uploads_id' => $request->timeline_uploads_id,
            'comments' => $request->comment,
        ]);

        return response()->json(['success' => true, 'message' => 'Comment added successfully!']);
    }

    // Fetch comments for a specific post
    public function fetchComments(Request $request)
    {
        $request->validate([
            'timeline_id' => 'required|array', // Ensure that timeline_id is an array
            'timeline_uploads_id' => 'required|array', // Ensure that timeline_uploads_id is an array
        ]);
    
        // Fetch comments from the LikesComment model based on the timeline_id and timeline_uploads_id
        $comments = LikesComment::whereIn('timeline_id', $request->query('timeline_id'))
            ->whereIn('timeline_uploads_id', $request->query('timeline_uploads_id'))
            ->pluck('comments');
    
        return response()->json(['comments' => $comments]);
    }
    
    

}
