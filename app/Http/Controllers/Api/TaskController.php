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
        $usersWithoutTasks = DB::table('users')
            ->leftJoin('daily_tasks', 'users.id', '=', 'daily_tasks.user_id')
            ->whereNull('daily_tasks.user_id')
            ->select('users.id', 'users.name', 'users.email')
            ->get();
    
        return response()->json($usersWithoutTasks);
    }

}
