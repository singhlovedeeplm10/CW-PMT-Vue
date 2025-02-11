<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectDetail;
use App\Models\Milestone;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function storeProjectWithDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:Long,Medium,Short',
            'status' => 'required|in:Awaiting,Started,Paused,Completed',
            'project_details' => 'required|array',
            'project_details.start_date' => 'nullable|date',
            'project_details.end_date' => 'nullable|date',
            'project_details.billing_type' => 'nullable|in:Online,Offline,Fixed',
            'project_details.billing_hours' => 'nullable|integer',
            'milestones' => 'nullable|array',
            'milestones.*.start_date' => 'nullable|date',
            'milestones.*.end_date' => 'nullable|date',
            'milestones.*.hours' => 'nullable|integer',
            'milestones.*.description' => 'nullable|string',
            'milestones.*.status' => 'nullable|in:Awaiting,Started,Paused,Completed',
            'uploads' => 'nullable|array',
            'uploads.*.file_name' => 'nullable|string',
            'uploads.*.file_description' => 'nullable|string',
            'uploads.*.path' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Step 1: Create the Project
            $project = Project::create([
                'user_id' => $request->user_id,
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'status' => $request->status,
            ]);

            // Step 2: Create Project Details
            $projectDetailsData = $request->input('project_details');
            $projectDetailsData['project_id'] = $project->id;
            $projectDetail = ProjectDetail::create($projectDetailsData);

            // Step 3: Create Milestones if any
            $milestoneIds = [];
            if ($request->has('milestones')) {
                foreach ($request->milestones as $milestoneData) {
                    $milestoneData['project_id'] = $project->id;
                    $milestone = Milestone::create($milestoneData);
                    $milestoneIds[] = $milestone->id;
                }
                $projectDetail->milestone_ids = json_encode($milestoneIds);
                $projectDetail->save();
            }

            // Step 4: Create Uploads if any
            $uploadIds = [];
            if ($request->has('uploads')) {
                foreach ($request->uploads as $uploadData) {
                    $uploadData['admin_id'] = $request->user_id; // assuming the user is admin
                    $upload = Upload::create($uploadData);
                    $uploadIds[] = $upload->id;
                }
                $projectDetail->upload_ids = json_encode($uploadIds);
                $projectDetail->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project, details, milestones, and uploads created successfully',
                'data' => [
                    'project' => $project,
                    'project_details' => $projectDetail,
                    'milestones' => $milestoneIds,
                    'uploads' => $uploadIds,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create project and related data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeProjects(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:Long,Medium,Short',
            'status' => 'required|in:Awaiting,Started,Paused,Completed',
            'comments' => 'nullable|string',
            'developer_assign_list' => 'nullable|array',
            'developer_assign_list.*' => 'exists:users,id', // Ensure each ID exists in the users table
        ]);
    
        // Save the project data
        try {
            $project = Project::create([
                'user_id' => auth()->id(), // Or provide the correct user ID
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'type' => $validatedData['type'],
                'status' => $validatedData['status'],
                'comment' => $validatedData['comments'],
                'developer_assign_list' => json_encode($validatedData['developer_assign_list'] ?? []),
            ]);
    
            return response()->json([
                'message' => 'Project added successfully',
                'data' => $project,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add project',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    public function getAllProjects()
    {
        try {
            $projects = Project::all();
    
            // Decode developer_assign_list and fetch user names
            $projects = $projects->map(function ($project) {
                $developerIds = json_decode($project->developer_assign_list, true) ?? [];
                $developerNames = User::whereIn('id', $developerIds)->pluck('name')->toArray();
                $project->developer_assign_list = $developerNames; // Replace IDs with names
                return $project;
            });
    
            return response()->json($projects);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch projects'], 500);
        }
    }

    public function updateProject(Request $request, $id)
    {
        // Validate the incoming request data with enum values for type and status
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:Long,Medium,Short', // Validate against enum values
            'status' => 'required|string|in:Awaiting,Started,Paused,Completed', // Validate against enum values
            'comment' => 'nullable|string',
            'developer_assign_list' => 'nullable|array', // Validate developer_assign_list as an array
        ]);
    
        // Find the project by ID
        $project = Project::find($id);
    
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }
    
        // Update the project with the validated data
        $project->name = $validatedData['name'];
        $project->description = $validatedData['description'] ?? $project->description;
        $project->type = $validatedData['type'];
        $project->status = $validatedData['status'];
        $project->comment = $validatedData['comment'] ?? $project->comment;
    
        // Only update developer_assign_list if it is provided in the request
        if (isset($validatedData['developer_assign_list'])) {
            $project->developer_assign_list = json_encode($validatedData['developer_assign_list']);
        }
    
        // Save the changes
        $project->save();
    
        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project,
        ], 200);
    }
        
}
