<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\TechnologyController;
use App\Http\Controllers\Api\BillingCredentialController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\UserProfileController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\BreakController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TaskController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// LOGIN API ROUTE
// Route::post('/login', [LoginController::class, 'login']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// TASKS API ROUTE
// Route::get('/tasks', [TaskController::class, 'getTasks']);

Route::middleware(['auth:sanctum'])->post('/tasks', [TaskController::class, 'storeTasks']);
Route::get('/tasks', [TaskController::class, 'showTasks']);



// LEAVES API ROUTE
Route::middleware('auth:sanctum')->post('/leaves', [LeaveController::class, 'addLeave']);

// USERS API ROUTE
Route::post('/users', [UserController::class, 'addUser']);
Route::post('/user-profiles', [UserProfileController::class, 'addUserdetails']);
Route::get('/users/{page?}', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->get('/username', [AuthController::class, 'getUser']);


// TECHNOLOGIES API ROUTE
Route::post('/technologies', [TechnologyController::class, 'addTech']);

// BILLING CREDENTIALS API ROUTE
Route::post('/billing-credentials', [BillingCredentialController::class, 'addCredentials']);

// PERMISSIONS API ROUTE
Route::post('/permissions', [PermissionController::class, 'addPermissions']);
Route::middleware('auth:sanctum')->post('/user-permissions', [PermissionController::class, 'assignPermissionToUser']);

// ATTENDANCE API ROUTE 
Route::middleware('auth:sanctum')->post('/attendance', [AttendanceController::class, 'storeAttendance']);

Route::middleware('auth:sanctum')->post('/clock-in', [AttendanceController::class, 'clockIn']);


// BREAKS API ROUTE
Route::middleware('auth:sanctum')->post('/break', [BreakController::class, 'storeBreak']);

// PROJECTS API ROUTE
Route::middleware('auth:sanctum')->post('/project', [ProjectController::class, 'storeProjectWithDetails']);


Route::post('/apply-leave', [LeaveController::class, 'store'])->name('apply.leave');
