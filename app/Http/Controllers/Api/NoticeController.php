<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;
use Carbon\Carbon;
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
            'description' => $description,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);
    
        return response()->json(['message' => 'Notice created successfully', 'data' => $notice], 201);
    }
    


    public function getNotices()
{
    $notices = Notice::all();

    // Update the description to include inline styles for images
    $notices->transform(function ($notice) {
        $dom = new \DOMDocument();

        // Suppress warnings for invalid HTML
        @$dom->loadHTML($notice->description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Get all images in the description
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            $img->setAttribute('style', 'max-width: 400px; max-height: 200px; object-fit: contain; margin: 5px;');
        }

        // Save the modified description back to the notice
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

    // Update the notice with the validated data
    $notice->update([
        'title' => $request->input('title'),
        'description' => $description,
        'start_date' => $request->input('start_date'),
        'end_date' => $request->input('end_date'),
    ]);

    return response()->json(['message' => 'Notice updated successfully', 'data' => $notice], 200);
}

    
    public function fetchNotices()
    {
        $today = Carbon::today(); // Get today's date
        $notices = Notice::select('title', 'description')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->get();
    
        return response()->json($notices);
    }

}
