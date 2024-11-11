<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyTask;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function storeTasks(Request $request)
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)->whereNotNull('clockin_time')->first();

        if (!$attendance) {
            return response()->json(['message' => 'User not clocked in.'], 403);
        }

        $tasks = $request->input('tasks');

        $request->validate([
            'tasks.*.project_name' => 'required|string|max:255',
            'tasks.*.hours' => 'required|numeric|min:0',
            'tasks.*.task_description' => 'required|string',
        ]);

        foreach ($tasks as $task) {
            DailyTask::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'project_name' => $task['project_name'],
                'hours' => $task['hours'],
                'task_description' => $task['task_description'],
                'task_status' => 'pending',
            ]);
        }

        return response()->json(['message' => 'Tasks saved successfully.'], 200);
    }


public function showTasks()
{
    $tasks = DailyTask::all(); // Or add filtering logic if needed
    return response()->json($tasks);
}
}
