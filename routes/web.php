<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\AttendanceController;



// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/{any}', function () {
    return view('master'); 
})->where('any', '.*');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/test', [HomeController::class, 'test'])->name('test');

// TASKS LIST ROUTE
Route::get('/task-list', [TaskController::class, 'showTaskList'])->name('task.list');
Route::get('/team-list', [TaskController::class, 'showTeamList'])->name('team.list');

// LEAVE ROUTE
Route::get('/leaves-list', [LeaveController::class, 'showLeaveList'])->name('leaves.list');
Route::get('/leave-details', [LeaveController::class, 'getLeaveDetails'])->name('leave.details');

Route::post('/attendance/toggle-clock', [AttendanceController::class, 'ClockIn']);
