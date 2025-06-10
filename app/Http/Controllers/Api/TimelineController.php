<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LikesComment;
use App\Models\Timeline;
use App\Models\TimelineUpload;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
        'uploadMedia' => 'nullable|array|max:10',  // Max 10 images/videos
        'uploadMedia.*' => 'nullable|mimes:jpg,jpeg,png,mp4|max:10240', // Max 10MB each
        'uploadLink' => 'nullable|url',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Sanitize title
    $title = $request->input('title');
    if ($title) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML(mb_convert_encoding('<div>' . $title . '</div>', 'HTML-ENTITIES', 'UTF-8'), 
                        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $title = strip_tags($dom->saveHTML());
        $title = str_replace(["\n", "\r"], '', $title);
    }

    // Process and sanitize description
    $description = $request->input('description');
    $dom = new \DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $images = $dom->getElementsByTagName('img');

    foreach ($images as $image) {
        $src = $image->getAttribute('src');

        if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
            $data = substr($src, strpos($src, ',') + 1);
            $data = base64_decode($data);
            $extension = $type[1];
            $fileName = uniqid() . '.' . $extension;
            $uploadPath = public_path('uploads/timelines');

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $filePath = $uploadPath . '/' . $fileName;
            file_put_contents($filePath, $data);

            $image->setAttribute('src', url('uploads/timelines/' . $fileName));
        }
    }

    $description = $dom->saveHTML();

    // Create timeline
    $timeline = Timeline::create([
        'user_id' => Auth::id(),
        'title' => $title,
        'description' => $description,
        'timeline_uploads_ids' => json_encode([]),
    ]);

    $filePaths = [];
    $fileIds = [];
    $fileOrder = 0; // Start order at 0

    // Handle media uploads
    if ($request->hasFile('uploadMedia')) {
        $uploadPath = public_path('uploads/timelines');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        foreach ($request->file('uploadMedia') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move($uploadPath, $fileName);
            $relativePath = 'timelines/' . $fileName;

            $fileType = in_array($file->getClientOriginalExtension(), ['mp4', 'mov']) ? 'video' : 'image';

            $fileUpload = TimelineUpload::create([
                'timeline_id' => $timeline->id,
                'file_path' => $relativePath,
                'file_type' => $fileType,
                'file_link' => null,
                'file_order' => $fileOrder++, // Assign file order
            ]);

            $filePaths[] = $relativePath;
            $fileIds[] = $fileUpload->id;
        }
    }

    // Handle external media link
    if ($request->uploadLink) {
        $fileUpload = TimelineUpload::create([
            'timeline_id' => $timeline->id,
            'file_path' => null,
            'file_type' => null,
            'file_link' => $request->uploadLink,
            'file_order' => $fileOrder++, // Assign file order
        ]);

        $fileIds[] = $fileUpload->id;
    }

    // Update timeline with file IDs
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
       $timelines = Timeline::with([
    'timelineUploads' => function ($query) {
        $query->orderBy('file_order'); // Order by file_order
    },
    'user'
])
    ->orderBy('created_at', 'desc')
    ->get();

        
        foreach ($timelines as $timeline) {
            // Calculate the total likes count for each timeline
            $likesCount = DB::table('likes_comments')
                ->where('timeline_id', $timeline->id)
                ->sum('likes'); // Sum up all likes as 'likes' is now an integer
            
            // Add the likes count to the timeline object
            $timeline->likes_count = $likesCount;
    
$likedUsers = DB::table('likes_comments')
    ->join('users', 'likes_comments.user_id', '=', 'users.id')
    ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')  // LEFT JOIN to include users without profiles
    ->where('likes_comments.timeline_id', $timeline->id)
    ->where('likes_comments.likes', 1)  // Fetch only users with likes = 1
    ->select('users.name', 'user_profiles.user_image')  // Select both name and user_image
    ->get();

// Add liked users with images to the timeline object
$timeline->liked_users = $likedUsers->map(function ($user) {
    // Generate the URL for the user image if it exists
    if ($user->user_image) {
        $user->user_image_url = asset('uploads/' . $user->user_image);
    } else {
        $user->user_image_url = asset('img/CWlogo.jpeg'); // Use default image
    }
    return $user;
});

    
            // Process each timeline upload (images, videos, etc.)
            foreach ($timeline->timelineUploads as $upload) {
                if ($upload->file_type === 'video' || $upload->file_type === 'image') {
                    $upload->file_url = $upload->file_path ? asset('uploads/' . $upload->file_path) : null;
                }
    
                // Add thumbnail and icon for external links
                if ($upload->file_link) {
                    if (strpos($upload->file_link, 'youtube.com') !== false) {
                        $upload->thumbnail = $this->getYouTubeThumbnail($upload->file_link);
                        $upload->icon = 'fab fa-youtube'; 
                        // $upload->icon = 'img/youtube2.png'; 
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
            'user_image' => $userProfile && $userProfile->user_image ? asset('uploads/' . $userProfile->user_image) : asset('/img/CWlogo.jpeg'),
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

    // Process the title to retain emojis and strip unwanted tags
    $title = $validatedData['title'];
    if ($title) {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML(mb_convert_encoding('<div>' . $title . '</div>', 'HTML-ENTITIES', 'UTF-8'), 
                        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $title = strip_tags($dom->saveHTML());
        $title = str_replace(["\n", "\r"], '', $title);
    }

    // Process the description to store emojis and base64 image uploads
    $description = $validatedData['description'];
    $dom = new \DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML(mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8'), 
                    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $images = $dom->getElementsByTagName('img');
    foreach ($images as $image) {
        $src = $image->getAttribute('src');

        if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
            $data = substr($src, strpos($src, ',') + 1);
            $data = base64_decode($data);
            $extension = $type[1];
            $fileName = uniqid() . '.' . $extension;
            $uploadPath = public_path('uploads/timelines');

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $filePath = $uploadPath . '/' . $fileName;
            file_put_contents($filePath, $data);

            $image->setAttribute('src', url('uploads/timelines/' . $fileName));
        }
    }

    $description = $dom->saveHTML();

    // Update timeline
    $timeline->title = $title;
    $timeline->description = $description;
    $timeline->save();

    // Return success response
    return response()->json(['success' => true, 'timeline' => $timeline]);
}
public function deleteImage($uploadId)
{
    try {
        // Find the timeline upload record
        $upload = TimelineUpload::find($uploadId);
        
        if (!$upload) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        // Delete the file from storage if it exists
        if ($upload->file_path && file_exists(public_path('uploads/' . $upload->file_path))) {
            unlink(public_path('uploads/' . $upload->file_path));
        }

        // Delete the record from database
        $upload->delete();

        return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to delete image: ' . $e->getMessage()], 500);
    }
}
public function updateImageOrder(Request $request)
{
    $request->validate([
        'timeline_id' => 'required|exists:timelines,id',
        'image_order' => 'required|array',
        'image_order.*.id' => 'required|exists:timeline_uploads,id',
        'image_order.*.order' => 'required|integer',
    ]);

    try {
        DB::beginTransaction();

        foreach ($request->image_order as $item) {
            TimelineUpload::where('id', $item['id'])
                ->where('timeline_id', $request->timeline_id)
                ->update(['file_order' => $item['order']]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Image order updated successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Failed to update image order: ' . $e->getMessage()
        ], 500);
    }
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
