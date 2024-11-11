<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyTask;
use Illuminate\Validation\ValidationException;

class DailyTaskController extends Controller
{
    // Fetch tasks associated with a specific attendance_id
    public function getTasks($attendance_id)
    {
        $tasks = DailyTask::where('attendance_id', $attendance_id)->get();
        return response()->json($tasks);
    }

    // Store a new task
    public function storeTask(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'project_name' => 'required|string',
            'task_description' => 'required|string',
            'hours' => 'required|integer'
        ]);

        $task = DailyTask::create([
            'attendance_id' => $request->attendance_id,
            'project_name' => $request->project_name,
            'task_description' => $request->task_description,
            'hours' => $request->hours
        ]);

        return response()->json($task, 201);
    }

    // Update an existing task
    public function updateTask(Request $request, $id)
    {
        $request->validate([
            'project_name' => 'string',
            'task_description' => 'string',
            'hours' => 'integer'
        ]);

        $task = DailyTask::findOrFail($id);
        $task->update($request->all());

        return response()->json($task);
    }
}
