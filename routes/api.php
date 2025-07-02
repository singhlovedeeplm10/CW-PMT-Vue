<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\TechnologyController;
use App\Http\Controllers\Api\BillingCredentialController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\BreakController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TimelineController;
use App\Http\Controllers\Api\MailController;
use App\Http\Controllers\Api\PolicyController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\SalarySlipController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\NotificationController;



Route::get('/send-email', [MailController::class, 'sendTestEmail']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// LOGIN API ROUTE
// Route::post('/login', [LoginController::class, 'login']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// TASKS API ROUTE
// Route::get('/tasks', [TaskController::class, 'getTasks']);
Route::middleware(['auth:sanctum'])->post('/tasks', [TaskController::class, 'storeTasks']);
Route::middleware(['auth:sanctum'])->get('/tasks', [TaskController::class, 'showTasks']);
Route::middleware(['auth:sanctum'])->get('/user-projects', [TaskController::class, 'fetchProjects']);
Route::get('/users-without-tasks', [TaskController::class, 'getUsersWithoutTasks']);

Route::get('/daily-tasks', [TaskController::class, 'getDailyTasks']);
Route::put('/update-tasks/{id}', [TaskController::class, 'updateTask']);
Route::delete('/delete-tasks/{id}', [TaskController::class, 'deleteTask']);
Route::middleware('auth:sanctum')->get('/my-daily-tasks', [TaskController::class, 'getMyDailyTasks']);

Route::get('/fetch-user-tasks/{userId}', [TaskController::class, 'fetchUserTask']);
Route::get('/user-daily-tasks', [TaskController::class, 'getUserDailyTasks']);
Route::middleware('auth:sanctum')->get('/user-tasks', [TaskController::class, 'getUserTasks']);
Route::middleware('auth:sanctum')->delete('/tasks/{id}', [TaskController::class, 'deleteUserTask']);


// LEAVES API ROUTE
// leave routes (USER)
Route::middleware('auth:sanctum')->post('/apply-leave', [LeaveController::class, 'applyLeave']);
Route::middleware('auth:sanctum')->get('/leaves', [LeaveController::class, 'showLeavesUser']);
Route::middleware('auth:sanctum')->put('/update-leaves/{leave}', [LeaveController::class, 'updateLeaveUser']);
Route::get('/leaves/{id}', [LeaveController::class, 'show'])->middleware('auth:api');

// team leave routes (ADMIN)
Route::middleware('auth:sanctum')->post('/apply-team-leave', [LeaveController::class, 'applyTeamLeave']);
Route::middleware('auth:sanctum')->get('/team-leaves', [LeaveController::class, 'showteamLeaves']);
Route::middleware('auth:sanctum')->post('update-team-leaves/{leave}', [LeaveController::class, 'updateTeamLeave']);
Route::get('/users/search', [LeaveController::class, 'searchUser']);
Route::get('/users-on-leave', [LeaveController::class, 'getUsersLeave']);
Route::get('/work-from-home-members', [LeaveController::class, 'getMembersOnWFH']);


// USERS API ROUTE
Route::post('/users', [UserController::class, 'addUser']);
Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'showUsers']);
Route::middleware('auth:sanctum')->get('/user-details', [AuthController::class, 'getUserDetails']);
Route::middleware(['auth:sanctum'])->get('/user-role', [AuthController::class, 'getUserRole']);
Route::middleware(['auth:sanctum'])->post('/users/{id}', [UserController::class, 'updateUser']);
Route::put('/users/{id}/status', [UserController::class, 'updateStatus']);
Route::get('/users/{user}/edit', [UserController::class, 'edit']);
Route::get('/employee-attendances', [UserController::class, 'employeeAttendances']);
Route::post('/users/{id}/assign-role', [UserController::class, 'assignUserRole']);
Route::middleware('auth:sanctum')->get('/employee-time-logs', [UserController::class, 'getEmployeeTimeLogsById']);
Route::get('/employee-detailed-time-logs', [UserController::class, 'getEmployeeAttendanceTimelogs']);
Route::get('/employee-breaks', [UserController::class, 'getEmployeeBreaksTimelogs']);
Route::middleware('auth:sanctum')->get('/time-logs/all', [UserController::class, 'getAllEmployeeTimeLogs']);


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
Route::middleware('auth:sanctum')->post('/clock-out', [AttendanceController::class, 'clockOut']);
Route::middleware('auth:sanctum')->get('/weekly-hours', [AttendanceController::class, 'getWeeklyHours']);
Route::middleware(['auth:sanctum'])->get('/check-clock-in-status', [AttendanceController::class, 'checkClockInStatus']);
Route::middleware('auth:sanctum')->get('/daily-hours', [AttendanceController::class, 'getDailyHours']);
Route::middleware('auth:sanctum')->get('/get-clockin-token', [AttendanceController::class, 'getClockinToken']);
Route::get('/auto-clockout', [AttendanceController::class, 'autoClockOut']);
Route::middleware('auth:sanctum')->delete('/attendances/cleanup', [AttendanceController::class, 'cleanUpIncompleteAttendance']); 

// BREAKS API ROUTE
Route::middleware('auth:sanctum')->post('/break', [BreakController::class, 'storeBreak']);
Route::middleware('auth:sanctum')->post('/start-break', [BreakController::class, 'startBreak']);
Route::middleware('auth:sanctum')->post('/end-break', [BreakController::class, 'endBreak']);
Route::middleware('auth:sanctum')->get('/daily-breaks', [BreakController::class, 'getDailyBreaks']);
Route::middleware('auth:sanctum')->get('/weekly-break-time', [BreakController::class, 'getWeeklyBreakTime']);
Route::get('/break-entries', [BreakController::class, 'getBreakEntries']);
Route::get('/user-break-details', [BreakController::class, 'getUserBreakDetails']);
Route::middleware('auth:sanctum')->get('/user-breaks', [BreakController::class, 'getUserBreaks']);
Route::middleware('auth:sanctum')->get('/get-breakin-token', [BreakController::class, 'getBreakinToken']);


// PROJECTS API ROUTE
Route::middleware('auth:sanctum')->post('/add-project', [ProjectController::class, 'storeProjectWithDetails']);
Route::middleware('auth:sanctum')->post('/add-projects', [ProjectController::class, 'storeProjects']);
Route::get('/projects', [ProjectController::class, 'getAllProjects']);
Route::middleware('auth:sanctum')->put('/update-projects/{id}', [ProjectController::class, 'updateProject']);
Route::put('/projects/{project}/remove-developer', [ProjectController::class, 'removeDeveloper']);

// TIMELINE API ROUTES
Route::middleware('auth:sanctum')->post('/upload-timeline', [TimelineController::class, 'uploadTimeline']);
Route::middleware('auth:sanctum')->get('/timelines', [TimelineController::class, 'getTimelineData']);
Route::middleware('auth:sanctum')->post('/like-post', [TimelineController::class, 'likePost']);
Route::middleware('auth:sanctum')->post('/timeline/comment', [TimelineController::class, 'postComment']);
Route::get('/timeline/fetch-comments', [TimelineController::class, 'fetchComments']);
Route::middleware('auth:sanctum')->put('/update-timelines/{id}', [TimelineController::class, 'updateTimeline']);
Route::middleware('auth:sanctum')->delete('/delete/timelines/{id}', [TimelineController::class, 'deleteTimeline']);
Route::middleware('auth:sanctum')->delete('/delete-timeline-image/{uploadId}', [TimelineController::class, 'deleteImage']);
Route::middleware('auth:sanctum')->post('/update-image-order', [TimelineController::class, 'updateImageOrder']);

// POLICIES API ROUTES
Route::middleware('auth:sanctum')->post('/save-policies', [PolicyController::class, 'savePolicy']);
Route::get('/policies', [PolicyController::class, 'getPolicies']);
Route::delete('/delete-policies/{id}', [PolicyController::class, 'deletePolicies']);
Route::post('/update-policies/{id}', [PolicyController::class, 'updatePolicies']);

// NOTICES API ROUTES
Route::post('/store-notices', [NoticeController::class, 'storeNotices']);
Route::get('/get-notices', [NoticeController::class, 'getNotices']);
Route::delete('/delete-notice/{id}', [NoticeController::class, 'deleteNotice']);
Route::put('/edit-notice/{id}', [NoticeController::class, 'updateNotice']);
Route::get('/notices', [NoticeController::class, 'fetchNotices']);
Route::get('/upcoming-birthdays', [NoticeController::class, 'upcomingBirthdays']);

// SalarySlip ROUTES
Route::post('/upload-salary-slip', [SalarySlipController::class, 'upload']);
Route::middleware('auth:sanctum')->get('/get-salary', [SalarySlipController::class, 'getSalary']);
Route::get('/view-salary-slip/{employeeCode}', [SalarySlipController::class, 'viewSalarySlip']);
Route::put('/update-salary-slip/{id}', [SalarySlipController::class, 'updateSalarySlip']);
Route::delete('/delete-salary-slip/{id}', [SalarySlipController::class, 'deleteSalarySlip']);

// DEVICES ROUTES
Route::middleware('auth:sanctum')->post('/add-devices', [DeviceController::class, 'addDevice']);
Route::get('/devices', [DeviceController::class, 'index']);
Route::middleware('auth:sanctum')->put('/update-devices/{id}', [DeviceController::class, 'updateDevice']);
Route::get('/devices/{id}/history', [DeviceController::class, 'history']);
Route::middleware('auth:sanctum')->put('/update-devices-status/{id}', [DeviceController::class, 'toggleStatus']);

// My-Account ROUTES
Route::middleware('auth:sanctum')->get('/user/projects', [TaskController::class, 'getAssignedProjectsWithHours']);
Route::middleware('auth:sanctum')->get('/user/credentials', [UserController::class, 'getUserCredentials']);
Route::middleware('auth:sanctum')->get('/user-assigned-devices', [DeviceController::class, 'getUserAssignedDevices']);
Route::middleware('auth:sanctum')->get('/user-account-details', [UserController::class, 'userAccountDetails']);

// NOTIFICATION ROUTES
Route::middleware('auth:sanctum')->get('/project-notifications', [NotificationController::class, 'getProjectNotifications']);

