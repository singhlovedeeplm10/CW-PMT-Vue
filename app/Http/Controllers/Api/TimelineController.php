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
    
            // Fetch the users who liked the timeline, including their images from user_profiles
            $likedUsers = DB::table('likes_comments')
                ->join('users', 'likes_comments.user_id', '=', 'users.id')
                ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')  // Join user_profiles to get user image
                ->where('likes_comments.timeline_id', $timeline->id)
                ->where('likes_comments.likes', 1)  // Fetch only users with likes = 1
                ->select('users.name', 'user_profiles.user_image')  // Select both name and user_image
                ->get();
            
            // Add liked users with images to the timeline object
            $timeline->liked_users = $likedUsers->map(function ($user) {
                // Generate the URL for the user image if it exists
                if ($user->user_image) {
                    $user->user_image_url = asset('storage/' . $user->user_image); // Get the full URL of the user image
                } else {
                    $user->user_image_url = null; // Default to null if no image is found
                }
                return $user;
            });
    
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
    
        // Return the timelines with likes count and liked users
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
    
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        $postIdentifier = [
            'timeline_id' => $validated['timeline_id'],
            'timeline_uploads_id' => $validated['timeline_uploads_id'],
        ];
    
        // Check if the user has already liked this post
        $existingLike = DB::table('likes_comments')
            ->where($postIdentifier)
            ->where('user_id', $userId)
            ->first();
    
        if ($existingLike) {
            // Unlike the post by deleting the user's like entry
            DB::table('likes_comments')->where('id', $existingLike->id)->delete();
    
            // Decrement the likes count
            $totalLikes = DB::table('likes_comments')
                ->where($postIdentifier)
                ->count(); // Recalculate likes based on the remaining entries
        } else {
            // Like the post by adding a new entry for the user
            DB::table('likes_comments')->insert([
                'timeline_id' => $validated['timeline_id'],
                'timeline_uploads_id' => $validated['timeline_uploads_id'],
                'user_id' => $userId,
                'likes' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Increment the likes count
            $totalLikes = DB::table('likes_comments')
                ->where($postIdentifier)
                ->count(); // Recalculate likes based on the new entry
        }
    
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
        ->with(['user.profile']) // Eager load the user profile relationship
        ->get(['comments', 'user_id']);

    // Map the comments to include the user's name and image
    $comments = $comments->map(function ($comment) {
        $userProfile = $comment->user ? $comment->user->profile : null;
        return [
            'comment' => $comment->comments,
            'user_name' => $comment->user ? $comment->user->name : 'Unknown User', // Get the user's name
            'user_image' => $userProfile ? asset('storage/' . $userProfile->user_image) : null, // Get the user's image path if available
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

    public function deleteTimeline($id)
{
    try {
        // Find the timeline by ID
        $timeline = Timeline::findOrFail($id);

        // Delete the associated uploads
        $timeline->timelineUploads()->delete();

        // Delete the timeline
        $timeline->delete();

        return response()->json(['success' => true, 'message' => 'Timeline deleted successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error deleting timeline', 'error' => $e->getMessage()], 500);
    }
}

    
}
