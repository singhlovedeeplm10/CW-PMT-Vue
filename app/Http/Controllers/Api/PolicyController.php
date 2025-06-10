<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class PolicyController extends Controller
{
    public function savePolicy(Request $request)
{
    $request->validate([
        'policy_title' => 'required|string|max:255',
        'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:2048',
    ]);

    // Create directory if it doesn't exist
    $uploadPath = public_path('uploads/policies');
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    // Generate unique filename and move file
    $fileName = time().'_'.$request->file('document')->getClientOriginalName();
    $request->file('document')->move($uploadPath, $fileName);
    $documentPath = 'policies/'.$fileName;

    $policy = Policy::create([
        'user_id' => auth()->id(),
        'policy_title' => $request->input('policy_title'),
        'last_updated_at' => now(),
        'document_path' => $documentPath, // Stores relative path
    ]);

    return response()->json([
        'message' => 'Policy added successfully',
        'policy' => $policy,
    ]);
}
    

public function getPolicies()
{
    // Fetch policies sorted alphabetically by title
    $policies = Policy::orderBy('policy_title', 'asc')->get();

    // Add the full document URL to each policy
    foreach ($policies as $policy) {
        $policy->document_url = url('uploads/' . $policy->document_path);
    }

    return response()->json($policies);
}


public function deletePolicies($id)
    {
        // Find the policy by ID
        $policy = Policy::find($id);

        if (!$policy) {
            return response()->json(['message' => 'Policy not found'], 404);
        }

        // Delete the policy
        $policy->delete();

        // Return a success response
        return response()->json(['message' => 'Policy deleted successfully'], 200);
    }

   public function updatePolicies(Request $request, $id)
{
    $policy = Policy::findOrFail($id);
    $policy->policy_title = $request->input('policy_title');

    if ($request->hasFile('document')) {
        // Delete old file if exists
        if ($policy->document_path && file_exists(public_path($policy->document_path))) {
            unlink(public_path($policy->document_path));
        }

        // Create directory if it doesn't exist
        $uploadPath = public_path('uploads/policies');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Generate unique filename and move file
        $fileName = time().'_'.$request->file('document')->getClientOriginalName();
        $request->file('document')->move($uploadPath, $fileName);
        $policy->document_path = 'policies/'.$fileName;
    }

    $policy->last_updated_at = now();
    $policy->save();

    return response()->json($policy);
}
    
}
