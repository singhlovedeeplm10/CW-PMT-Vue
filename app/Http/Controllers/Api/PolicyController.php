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
    
        // Store the document in the 'public/policies' directory
        $documentPath = $request->file('document')->store('policies', 'public');
    
        $policy = Policy::create([
            'user_id' => auth()->id(),
            'policy_title' => $request->input('policy_title'),
            'last_updated_at' => now(),
            'document_path' => $documentPath, // This will store the relative path like 'policies/filename.ext'
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
        $policy->document_url = url('storage/' . $policy->document_path);
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
    
        // Update policy title
        $policy->policy_title = $request->input('policy_title');
    
        // Handle file upload
        if ($request->hasFile('document')) {
            // Delete the old document if it exists
            if ($policy->document_path && Storage::exists('public/' . $policy->document_path)) {
                Storage::delete('public/' . $policy->document_path);
            }
    
            // Store the new document
            $path = $request->file('document')->store('policies', 'public');
            $policy->document_path = $path;
        }
    
        // Update the last updated date
        $policy->last_updated_at = now();
    
        // Save the policy
        $policy->save();
    
        return response()->json($policy);
    }
    

}
