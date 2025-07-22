<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyTask;
use App\Models\Attendance;
use App\Models\UserProfile;
use App\Models\User;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function storeTasks(Request $request)
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)->whereNotNull('clockin_time')->first();

        if (!$attendance) {
            return response()->json(['message' => 'User not clocked in.'], 403);
        }
        // This is test comment
        // Filter and validate tasks
        $tasks = collect($request->input('tasks', []))->filter(function ($task) {
            return !empty($task['project_id']) && !empty($task['hours']) && !empty($task['task_description']);
        });

        if ($tasks->isEmpty()) {
            return response()->json(['message' => 'All tasks must have valid fields.'], 400);
        }

        $request->merge(['tasks' => $tasks->toArray()]);
        $request->validate([
            'tasks.*.project_id' => 'required|exists:projects,id',
            'tasks.*.hours' => 'required|numeric|min:0',
            'tasks.*.task_description' => 'required|string',
            'tasks.*.id' => 'nullable|exists:daily_tasks,id',
        ], [
            'tasks.*.project_id.required' => 'Each task must have a project selected.',
            'tasks.*.project_id.exists' => 'Selected project does not exist.',
            'tasks.*.hours.required' => 'Each task must have hours specified.',
            'tasks.*.hours.numeric' => 'Hours must be a number.',
            'tasks.*.task_description.required' => 'Each task must have a description.',
        ]);

        foreach ($tasks as $task) {
            $project = Project::find($task['project_id']);
            $projectName = $project->name;

            if (isset($task['id'])) {
                $existingTask = DailyTask::where('id', $task['id'])->where('user_id', $user->id)->first();
                if ($existingTask) {
                    $existingTask->update([
                        'project_id' => $task['project_id'],
                        'project_name' => $projectName,
                        'hours' => (float) $task['hours'], // Cast to float to store exact value
                        'task_description' => $task['task_description'],
                        'task_status' => 'pending',
                    ]);
                }
            } else {
                DailyTask::create([
                    'user_id' => $user->id,
                    'attendance_id' => $attendance->id,
                    'project_id' => $task['project_id'],
                    'project_name' => $projectName,
                    'hours' => (float) $task['hours'], // Cast to float to store exact value
                    'task_description' => $task['task_description'],
                    'task_status' => 'pending',
                ]);
            }
        }


        return response()->json(['message' => 'Tasks saved/updated successfully.'], 200);
    }
    public function showTasks()
    {
        $tasks = DailyTask::with('project') // Eager load the related project
            ->where('user_id', Auth::id()) // Filter tasks by logged-in user
            ->whereDate('created_at', Carbon::today()) // Filter tasks created today
            ->get();

        return response()->json($tasks);
    }

    public function fetchProjects()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['projects' => []]);
        }

        // Get the first role name of the user (assumes one role per user)
        $role = $user->roles->pluck('name')->first();

        // If the user is an Admin, return all "Started" projects
        if ($role === 'Admin') {
            $projects = Project::where('status', 'Started')
                ->get(['id', 'name', 'user_id', 'developer_assign_list']);

            return response()->json(['projects' => $projects]);
        }

        // For non-admin users, filter based on assignment or ownership
        $projects = Project::where('status', 'Started')
            ->get(['id', 'name', 'user_id', 'developer_assign_list'])
            ->map(function ($project) {
                $project->developer_assign_list = json_decode($project->developer_assign_list, true) ?? [];
                return $project;
            })
            ->filter(function ($project) use ($user) {
                return in_array($user->id, $project->developer_assign_list) ||
                    $project->user_id == $user->id;
            })
            ->values();

        return response()->json(['projects' => $projects]);
    }

public function getUsersWithoutTasks()
{
    $today = now()->toDateString();

    // Subquery: Get one attendance record per user for today
    $attendanceSubquery = DB::table('attendances')
        ->select('user_id', DB::raw('MIN(id) as id'), DB::raw('MIN(clockin_time) as clockin_time'))
        ->whereDate('clockin_time', $today)
        ->groupBy('user_id');

    // Subquery: Sum task hours per user for today
    $taskHoursSubquery = DB::table('daily_tasks')
        ->select(
            'user_id',
            DB::raw('SUM(hours) as total_hours'),
            DB::raw('COUNT(*) as task_count')
        )
        ->whereDate('created_at', $today)
        ->groupBy('user_id');

    // Subquery: Get users on full-day leave today
    $onLeaveSubquery = DB::table('leaves')
        ->select('user_id')
        ->where('type_of_leave', 'Full Day Leave')
        ->where('status', 'approved')
        ->where(function ($query) use ($today) {
            $query->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today);
        });

    $usersWithIncompleteOrNoTasks = DB::table('users')
        ->leftJoinSub($attendanceSubquery, 'attendances', function ($join) {
            $join->on('users.id', '=', 'attendances.user_id');
        })
        ->leftJoinSub($taskHoursSubquery, 'task_hours', function ($join) {
            $join->on('users.id', '=', 'task_hours.user_id');
        })
        ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
        ->whereNotIn('users.id', $onLeaveSubquery)
        ->where(function ($query) {
            $query->whereNull('attendances.clockin_time') // Not clocked in
                  ->orWhere(function($q) {
                      $q->whereNotNull('attendances.clockin_time') // Clocked in but...
                        ->where(function($q2) {
                            $q2->whereNull('task_hours.total_hours') // No tasks
                               ->orWhere('task_hours.total_hours', '!=', 8); // Or incomplete tasks
                        });
                  });
        })
        ->where(function ($query) {
            $query->where('users.status', '1')
                ->orWhereNull('users.status');
        })
        ->where('users.id', '!=', 1)
        ->select(
            'users.id',
            DB::raw('CASE WHEN users.status = 0 THEN NULL ELSE users.name END AS name'),
            'user_profiles.user_image',
            'attendances.clockin_time',
            DB::raw('CASE WHEN task_hours.task_count IS NULL OR task_hours.task_count = 0 THEN true ELSE false END as no_task_entry'),
            DB::raw('IFNULL(task_hours.total_hours, 0) as total_hours')
        )
        ->groupBy(
            'users.id',
            'users.status',
            'users.name',
            'user_profiles.user_image',
            'attendances.clockin_time',
            'task_hours.total_hours',
            'task_hours.task_count'
        )
        ->get();

    return response()->json($usersWithIncompleteOrNoTasks);
}


    public function getDailyTasks(Request $request)
    {
        $date = $request->query('date', now()->toDateString()); // Default to today's date if no date is provided

        $dailyTasks = DailyTask::with(['user.profile', 'project'])
            ->whereDate('created_at', $date)
            ->get()
            ->groupBy('user_id')
            ->map(function ($tasks, $userId) {
                return [
                    'user' => [
                        'id' => $tasks->first()->user->id,
                        'name' => $tasks->first()->user->name,
                        'image' => $tasks->first()->user->profile->user_image ?? null,
                    ],
                    'projects' => $tasks->map(function ($task) {
                        return [
                            'project_name' => $task->project_name,
                            'task_description' => $task->task_description,
                            'hours' => $task->hours,
                            'leave_id' => $task->leave_id,
                        ];
                    }),
                ];
            });

        return response()->json($dailyTasks);
    }



    public function updateTask(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'hours' => 'required|numeric|min:0',
            'task_description' => 'required|string|max:1000',
            'task_status' => 'required|in:pending,in_progress,completed',
            'project_id' => 'required|exists:projects,id', // Ensure the project exists
        ]);

        // Find the task by ID
        $task = DailyTask::findOrFail($id);

        // Retrieve the project name using the project_id
        $project = Project::findOrFail($request->project_id);
        $projectName = $project->name;

        // Update the task with new data
        $task->update([
            'hours' => $request->hours,
            'task_description' => $request->task_description,
            'task_status' => $request->task_status,
            'project_id' => $request->project_id, // Update the project ID
            'project_name' => $projectName, // Save the project name
        ]);

        // Return the updated task as JSON
        return response()->json($task);
    }


    public function deleteTask($id)
    {
        $task = DailyTask::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
    public function getTasksForToday(Request $request)
    {
        // Get the logged-in user
        $user = $request->user();

        // Get today's date
        $today = Carbon::today();

        // Fetch the tasks for today for the logged-in user
        $tasks = DailyTask::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->with('project') // Include project details
            ->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    public function fetchUserTask($userId, Request $request)
    {
        try {
            $date = $request->input('date', Carbon::today()->toDateString());

            $tasks = DailyTask::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->with(['user.profile', 'project'])
                ->get();

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $tasks->first()?->user->id,
                    'name' => $tasks->first()?->user->name,
                    'image' => $tasks->first()?->user->profile->user_image ?? null, // Fetch user image
                ],
                'tasks' => $tasks,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user tasks.',
            ], 500);
        }
    }



    public function getUserDailyTasks()
    {
        try {
            $userId = Auth::id(); // Get the logged-in user ID
            $tasks = DailyTask::with('project') // Include project details in the tasks
                ->where('user_id', $userId)
                ->get();

            return response()->json($tasks);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch tasks'], 500);
        }
    }
    public function getUserTasks(Request $request)
    {
        // Fetch authenticated user
        $user = $request->user();

        // Fetch tasks for the logged-in user, created today, with related project details
        $tasks = DailyTask::where('user_id', $user->id)
            ->whereDate('created_at', now()) // Filter by today's date
            ->with('project') // Load project details along with tasks
            ->get();

        // Add additional properties to tasks based on `leave_id`
        $tasks = $tasks->map(function ($task) {
            $task->isReadonly = !is_null($task->leave_id); // Mark as readonly if `leave_id` is not null
            $task->hasNullProjectId = is_null($task->project_id); // Flag tasks with null project_id
            return $task;
        });

        // Return the tasks as JSON
        return response()->json($tasks);
    }



    public function deleteUserTask($id)
    {
        try {
            $task = DailyTask::findOrFail($id);
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the task.',
            ], 500);
        }
    }
    public function getMyDailyTasks(Request $request)
    {
        $user = Auth::user(); // Get authenticated user

        $dailyTasks = DailyTask::where('user_id', $user->id)
            ->select('id', 'project_name', 'hours', 'task_description', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($dailyTasks);
    }
    public function getAssignedProjectsWithHours(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['projects' => []]);
        }

        $projects = Project::where('status', 'Started')
            ->get()
            ->map(function ($project) {
                $project->developer_assign_list = json_decode($project->developer_assign_list, true) ?? [];
                return $project;
            })
            ->filter(function ($project) use ($user) {
                return in_array($user->id, $project->developer_assign_list) || $project->user_id == $user->id;
            })
            ->values();

        foreach ($projects as $project) {
            $project->total_hours = DailyTask::where('user_id', $user->id)
                ->where('project_id', $project->id)
                ->sum('hours');
        }

        return response()->json(['projects' => $projects]);
    }
}
