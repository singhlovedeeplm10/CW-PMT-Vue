<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaskController extends Controller
{
     // Method to return task data
     public function getTasks()
     {
         // Sample task data
         $tasks = [
             ['id' => 1, 'name' => 'Task 1', 'description' => 'This is task 1'],
             ['id' => 2, 'name' => 'Task 2', 'description' => 'This is task 2'],
         ];
 
         return response()->json($tasks);
     }
 
     // Method to load task_list.blade.php view (optional if you don't use this controller for web routing)
     public function showTaskList()
     {
         return view('tasks.task_list');
     }

     public function showTeamList()
     {
         return view('tasks.task_team_list');
     }
}
