<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class NoticeController extends Controller
{
    public function storeNotices(Request $request)
{
    // Validate the incoming request
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'order' => 'required|integer',
        'description' => 'required|string',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Process the description to extract and save images separately
    $description = $request->input('description');
    $dom = new \DOMDocument();

    // Load the description content while suppressing errors for invalid HTML
    @$dom->loadHTML($description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $images = $dom->getElementsByTagName('img');

    foreach ($images as $image) {
        $src = $image->getAttribute('src');

        // Check if the image is base64 encoded
        if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
            $data = substr($src, strpos($src, ',') + 1); // Extract base64 data
            $data = base64_decode($data); // Decode base64

            // Generate a unique file name
            $extension = $type[1]; // Get the image extension (e.g., jpeg, png)
            $fileName = uniqid() . '.' . $extension;
            $uploadPath = public_path('uploads/notices');

            // Ensure the directory exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true); // Create the directory with proper permissions
            }

            $filePath = $uploadPath . '/' . $fileName;

            // Save the file
            file_put_contents($filePath, $data);

            // Replace the base64 src with the file URL
            $image->setAttribute('src', url('uploads/notices/' . $fileName));
        }
    }

    // Save the modified HTML content
    $description = $dom->saveHTML();

    // Save the validated data into the database
    $notice = Notice::create([
        'title' => $request->input('title'),
        'order' => $request->input('order'),
        'description' => $description,
        'start_date' => $request->input('start_date'),
        'end_date' => $request->input('end_date'),
    ]);

    return response()->json(['message' => 'Notice created successfully', 'data' => $notice], 201);
}


public function getNotices(Request $request)
{
    $perPage = 3; // Number of notices per page
    $notices = Notice::orderBy('order', 'asc')->paginate($perPage);

    // Modify descriptions to include inline styles for images
    $notices->getCollection()->transform(function ($notice) {
        $dom = new \DOMDocument();
        @$dom->loadHTML($notice->description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            $img->setAttribute('style', 'max-width: 400px; max-height: 200px; object-fit: contain; margin: 5px;');
        }

        $notice->description = $dom->saveHTML();
        return $notice;
    });

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

    public function updateNotice(Request $request, $id)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'order' => 'required|integer',  // Added validation for 'order'
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Find the notice or fail if not found
        $notice = Notice::findOrFail($id);
    
        // Process the description to extract and save images separately
        $description = $request->input('description');
        $dom = new \DOMDocument();
    
        // Load the description content while suppressing errors for invalid HTML
        @$dom->loadHTML($description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $images = $dom->getElementsByTagName('img');
    
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
    
            // Check if the image is base64 encoded
            if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
                $data = substr($src, strpos($src, ',') + 1); // Extract base64 data
                $data = base64_decode($data); // Decode base64
    
                // Generate a unique file name
                $extension = $type[1]; // Get the image extension (e.g., jpeg, png)
                $fileName = uniqid() . '.' . $extension;
                $uploadPath = public_path('uploads/notices');
    
                // Ensure the directory exists
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true); // Create the directory with proper permissions
                }
    
                $filePath = $uploadPath . '/' . $fileName;
    
                // Save the file
                file_put_contents($filePath, $data);
    
                // Replace the base64 src with the file URL
                $image->setAttribute('src', url('uploads/notices/' . $fileName));
            }
        }
    
        // Save the modified HTML content
        $description = $dom->saveHTML();
    
        // Update the notice with the validated data, including 'order'
        $notice->update([
            'title' => $request->input('title'),
            'order' => $request->input('order'), // Added order field update
            'description' => $description,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);
    
        return response()->json(['message' => 'Notice updated successfully', 'data' => $notice], 200);
    }
    

    
    public function fetchNotices()
    {
        $today = Carbon::today(); // Get today's date
        
        // Fetch notices as per the original logic
        $notices = Notice::select('order', 'title', 'description')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->orderBy('order', 'asc')
            ->get();
    
        // Array of birthday wishes
        $birthdayWishes = [
            'Wishing you a fantastic birthday filled with laughter and joy!',
            'May this year bring new happiness, goals, and achievements to your life!',
            'Have a wonderful birthday and an amazing year ahead!',
            'Cheers to another year of greatness and success! Happy Birthday!',
            'Your life is about to pick up speed. Enjoy the ride! Happy Birthday!',
            'Hope your birthday is as amazing as you are!',
            'Wishing you a day filled with love, laughter, and endless fun!',
            'Happy Birthday! Keep shining like the star that you are!',
            'Enjoy your special day to the fullest!',
            'Wishing you health, happiness, and prosperity today and always!',
            'Let today be a reminder of how much you are loved!',
            'Have an unforgettable birthday celebration!',
            'Hope this year brings everything you’ve been wishing for!',
            'Smile and celebrate because it’s your day!',
            'May your birthday be filled with sweet moments and memories!',
        ];
    
        // Fetch birthdays matching today's date (only month and day)
        $birthdays = DB::table('user_profiles')
            ->join('users', 'user_profiles.user_id', '=', 'users.id')
            ->select('users.name', 'user_profiles.user_image')
            ->whereMonth('user_DOB', $today->month)
            ->whereDay('user_DOB', $today->day)
            ->get();
    
        // Create an array of birthday notices
        $birthdayNotices = collect();
    
        foreach ($birthdays as $birthday) {
            $randomWish = $birthdayWishes[array_rand($birthdayWishes)];
            $birthdayNotices->push([
                'order' => 0,
                'title' => 'Happy Birthday ' . $birthday->name,
                'description' => $randomWish,
                'user_image' => $birthday->user_image,
            ]);
        }
    
        // Merge birthday notices at the start
        $allNotices = $birthdayNotices->merge($notices);
    
        return response()->json($allNotices);
    }
    
    

public function upcomingBirthdays(Request $request)
{
    $date = $request->input('date'); // Format: YYYY-MM-DD
    $monthDay = date('m-d', strtotime($date)); // Extract MM-DD for comparison

    $birthdays = DB::table('users')
        ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->where('users.status', '1') // Only fetch active users
        ->where(DB::raw('DATE_FORMAT(user_profiles.user_DOB, "%m-%d")'), $monthDay)
        ->select('users.id', 'users.name', 'user_profiles.user_image', 'user_profiles.user_DOB')
        ->get();

    // Add the full URL for the images
    $birthdays = $birthdays->map(function ($user) {
        // Assuming images are stored in the 'public/profile_images' directory
        $user->user_image = $user->user_image ? Storage::url($user->user_image) : null; // Generate public URL or set to null
        return $user;
    });

    return response()->json($birthdays);
}


}
