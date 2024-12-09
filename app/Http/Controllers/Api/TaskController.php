<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyTask;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Project;
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
    
        $request->validate([
            'tasks.*.project_id' => 'required|exists:projects,id',
            'tasks.*.hours' => 'required|numeric|min:0',
            'tasks.*.task_description' => 'required|string',
        ]);
    
        foreach ($request->input('tasks') as $task) {
            DailyTask::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'project_id' => $task['project_id'],
                'hours' => $task['hours'],
                'task_description' => $task['task_description'],
                'task_status' => 'pending',
            ]);
        }
    
        return response()->json(['message' => 'Tasks saved successfully.'], 200);
    }
    
    
    public function showTasks()
{
    $tasks = DailyTask::with('project') // Eager load the related project
        ->get();

    return response()->json($tasks);
}

    public function fetchProjects()
    {
        $projects = Project::all(['id', 'name']); // Adjust fields as needed
        return response()->json(['projects' => $projects]);
    }

    public function getUsersWithoutTasks()
    {
        $today = now()->toDateString(); // Get today's date in 'Y-m-d' format
    
        $usersWithoutTasks = DB::table('users')
            ->leftJoin('daily_tasks', function ($join) use ($today) {
                $join->on('users.id', '=', 'daily_tasks.user_id')
                    ->whereDate('daily_tasks.created_at', $today);
            })
            ->whereNull('daily_tasks.user_id')
            ->select('users.id', 'users.name', 'users.email')
            ->get();
    
        return response()->json($usersWithoutTasks);
    }
    

    public function getDailyTasks()
{
    $dailyTasks = DailyTask::with(['user', 'project'])
        ->whereDate('created_at', now()->toDateString()) // Filter by today's date
        ->get();

    return response()->json($dailyTasks);
}

public function updateTask(Request $request, $id)
{
    $task = DailyTask::findOrFail($id);
    $task->update([
        'hours' => $request->hours,
        'task_description' => $request->task_description,
        'task_status' => $request->task_status,
    ]);

    return response()->json($task);
}

public function deleteTask($id)
{
    $task = DailyTask::findOrFail($id);
    $task->delete();

    return response()->json(['message' => 'Task deleted successfully']);
}

}
