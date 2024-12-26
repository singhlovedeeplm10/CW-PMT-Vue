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
        // Retrieve timelines with associated uploads and user
        $timelines = Timeline::with(['timelineUploads', 'user'])->get();
        
        foreach ($timelines as $timeline) {
            // Calculate the total likes count for each timeline
            $likesCount = DB::table('likes_comments')
                ->where('timeline_id', $timeline->id)
                ->sum('likes'); // Sum up all likes as 'likes' is now an integer
            
            // Add the likes count to the timeline object
            $timeline->likes_count = $likesCount;
        
            // Process each timeline upload (images, videos, etc.)
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
    
        // Return the timelines with likes count
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
        
        // Ensure the user is authenticated and we have a valid user_id
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        // Check if the user has already liked this post
        $existingLike = DB::table('likes_comments')->where([
            ['timeline_id', '=', $validated['timeline_id']],
            ['timeline_uploads_id', '=', $validated['timeline_uploads_id']],
            ['user_id', '=', $userId],
        ])->first();
    
        if ($existingLike) {
            // User has already liked, so remove the like (dislike)
            DB::table('likes_comments')
                ->where('id', $existingLike->id)
                ->delete();
            
            // Decrement the like count by 1
            DB::table('likes_comments')
                ->where([
                    ['timeline_id', '=', $validated['timeline_id']],
                    ['timeline_uploads_id', '=', $validated['timeline_uploads_id']],
                ])
                ->decrement('likes', 1); // Decrement likes count by 1
        } else {
            // User has not liked, so add a like
            DB::table('likes_comments')->insert([
                'timeline_id' => $validated['timeline_id'],
                'timeline_uploads_id' => $validated['timeline_uploads_id'],
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Increment the like count by 1
            DB::table('likes_comments')
                ->where([
                    ['timeline_id', '=', $validated['timeline_id']],
                    ['timeline_uploads_id', '=', $validated['timeline_uploads_id']],
                ])
                ->increment('likes', 1); // Increment likes count by 1
        }
    
        // Get the updated like count after the action
        $totalLikes = DB::table('likes_comments')
            ->where([
                ['timeline_id', '=', $validated['timeline_id']],
                ['timeline_uploads_id', '=', $validated['timeline_uploads_id']],
            ])
            ->value('likes');
        
        return response()->json(['success' => true, 'totalLikes' => $totalLikes, 'likedByUser' => !$existingLike]);
    }      
    
    
    public function postComment(Request $request)
    {
        $request->validate([
            'timeline_id' => 'required|json',
            'timeline_uploads_id' => 'required|json',
            'comment' => 'required|string',
        ]);
    
        // Ensure the user is authenticated and we have a valid user_id
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        LikesComment::create([
            'timeline_id' => $request->timeline_id,
            'timeline_uploads_id' => $request->timeline_uploads_id,
            'comments' => $request->comment,
            'user_id' => $userId, // Ensure user_id is set
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

    // Fetch comments along with user data, excluding null comments
    $comments = LikesComment::whereIn('timeline_id', $request->query('timeline_id'))
        ->whereIn('timeline_uploads_id', $request->query('timeline_uploads_id'))
        ->whereNotNull('comments') // Exclude null comments
        ->with('user') // Eager load the user relationship
        ->get(['comments', 'user_id']);

    // Map the comments to include the user's name
    $comments = $comments->map(function ($comment) {
        return [
            'comment' => $comment->comments,
            'user_name' => $comment->user ? $comment->user->name : 'Unknown User', // Get the user's name
        ];
    });

    return response()->json(['comments' => $comments]);
}

         
    public function updateTimeline(Request $request, $id)
    {
        // Find the timeline post by its ID
        $timeline = Timeline::find($id);

        // Check if the timeline exists
        if (!$timeline) {
            return response()->json(['error' => 'Timeline not found'], 404);
        }

        // Validate the incoming data
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Update the timeline's title and description
        $timeline->title = $validatedData['title'];
        $timeline->description = $validatedData['description'];

        // Save the changes to the database
        $timeline->save();

        // Return a success response
        return response()->json(['success' => true, 'timeline' => $timeline]);
    }
    
}
