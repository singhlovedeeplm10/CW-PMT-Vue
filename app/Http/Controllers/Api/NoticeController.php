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
        'Happy Birthday! Wishing you a year filled with continued success, growth, and exciting opportunities. Your hard work and dedication are truly appreciated, and we are fortunate to have you on our team. May this year bring you closer to your personal and professional aspirations. Enjoy your special day to the fullest!',

        'Happy Birthday to an amazing team member. On your special day, we want to express
our appreciation for your hard work and dedication. You make a significant impact on our
success, and we look forward to many more years of working together. May your birthday be
filled with joy and the year ahead with great achievements!',
        
        'On your birthday, we want to take a moment to recognize and celebrate all of your accomplishments. Your contributions to the team and the company are invaluable. May this year bring you both personal happiness and professional growth. We look forward to achieving even greater success together in the year ahead. Have a fantastic birthday!',
        
        'Happy Birthday! Your professionalism, dedication, and positive attitude make you an integral part of our team. As you celebrate today, may you reflect on all the achievements you’ve made so far and look forward to even greater success in the future. Here’s to another year of collaboration and growth. Enjoy your special day!',
        
        'Wishing you a very happy birthday! Your hard work, commitment, and positive energy make a huge difference in the workplace. May this year bring you exciting challenges, rewarding opportunities, and all the success you deserve. We’re grateful to have you with us—here’s to an amazing year ahead!',
        
        'Happy Birthday! Your professional growth, enthusiasm, and leadership are a true inspiration to all of us. As you celebrate today, we want to thank you for everything you contribute to the team. May the year ahead bring you both personal joy and career success. We look forward to many more achievements together. Enjoy your day!',
        
        'Wishing you a very special birthday! Your passion for excellence and your dedication to the team make you stand out in every way. We’re so glad to have you as part of the company. May the year ahead bring you more growth, happiness, and success. Take some time today to relax and enjoy every moment—happy birthday!',
        
        'Happy Birthday! On your special day, we want to thank you for your hard work and dedication. You bring great energy to our team, and your contributions are truly valued. May this year bring you success, happiness, and exciting new opportunities. Here’s to another amazing year of working together. Enjoy your day to the fullest!',
        
        'Happy Birthday! Your ability to tackle challenges with a positive attitude and your outstanding contributions to the team make you a true asset. May this year bring you not only professional success but also personal happiness. We are lucky to have you on our team. Enjoy your special day, and here’s to a fantastic year ahead!',
        
        'Wishing you a wonderful birthday filled with joy and laughter. Your hard work and dedication inspire all of us, and we appreciate all that you do for the company. May the year ahead bring you the fulfillment of your personal and professional goals. We look forward to achieving even greater things together. Have a fantastic celebration!',
        
        'Happy Birthday! Today is a day to celebrate you and all the wonderful things you bring to the workplace. Your commitment, creativity, and teamwork are truly invaluable. We’re excited to see all the incredible things you’ll accomplish this year, both personally and professionally. Enjoy your day, and here’s to many more years of success together!',

        'Wishing a very Happy Birthday to one of our most valued team members. Your hard
work and positive attitude brighten up the office every day. May this birthday mark the
beginning of a year filled with personal growth, professional success, and joy. Enjoy your
special day!',

        'Happy Birthday, It’s truly a pleasure working alongside you. Your dedication, passion,
and teamwork make a real difference in our workplace. May this year be your best yet, filled
with success, joy, and many wonderful moments. Enjoy your day to the fullest!',

        'Happy Birthday. On your special day, we want to thank you for all the hard work, passion,
and positivity you bring to the team. We truly appreciate everything you do. May the coming
year be filled with health, happiness, and continued professional success. Enjoy your day!',

        'Happy Birthday On your special day, we want to take a moment to celebrate your hard
work and dedication. Your contributions to the team are truly valued, and we feel lucky to
have you with us. May this year bring you success, happiness, and continued growth both
personally and professionally. Enjoy your day to the fullest!',
    ];

    // Fetch birthdays matching today's date and active users (status = 1)
    $birthdays = DB::table('user_profiles')
        ->join('users', 'user_profiles.user_id', '=', 'users.id')
        ->select('users.name', 'user_profiles.user_image')
        ->whereMonth('user_profiles.user_DOB', $today->month) // Ensure we're using the correct column for DOB
        ->whereDay('user_profiles.user_DOB', $today->day)
        ->where('users.status', '1') // Only fetch users where status is '1'
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
