<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Device;
use App\Models\User;
use App\Models\Leave;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
public function getProjectNotifications(Request $request)
{
    // Get the authenticated user's ID
    $userId = $request->user()->id;

    // Fetch project notifications for the user
    $notifications = Notification::where('type', 'projects')
        ->where('to_user_id', $userId)
        ->with(['fromUser'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Transform the notifications
    $transformedNotifications = $notifications->map(function ($notification) {
        // Load related project
        $project = Project::find($notification->type_id);

        return [
            'id' => $notification->id,
            'from_user' => [
                'id' => $notification->fromUser->id,
                'name' => $notification->fromUser->name,
            ],
            'project' => $project ? [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
                'type' => $project->type,
            ] : null,
            'notification_message' => $notification->notification_message,
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at
                ? $notification->created_at->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s')
                : null,
            'updated_at' => $notification->updated_at->format('Y-m-d H:i:s'),
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $transformedNotifications,
        'meta' => [
            'total' => $transformedNotifications->count(),
            'unread_count' => $transformedNotifications->where('is_read', false)->count(),
        ]
    ]);
}
public function markProjectNotificationsAsRead(Request $request)
{
    $userId = $request->user()->id;

    Notification::where('type', 'projects')
        ->where('to_user_id', $userId)
        ->where('is_read', false)
        ->update(['is_read' => true]);

    return response()->json([
        'success' => true,
        'message' => 'All project notifications marked as read'
    ]);
}
public function getDeviceNotifications(Request $request)
{
    // Get the authenticated user's ID
    $userId = $request->user()->id;

    // Fetch device notifications for the user
    $notifications = Notification::where('type', 'devices')
        ->where('to_user_id', $userId)
        ->with(['fromUser'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Transform the notifications
    $transformedNotifications = $notifications->map(function ($notification) {
        // Load related device
        $device = Device::find($notification->type_id);

        return [
            'id' => $notification->id,
            'from_user' => [
                'id' => $notification->fromUser->id,
                'name' => $notification->fromUser->name,
            ],
            'device' => $device ? [
                'id' => $device->id,
                'name' => $device->device,
                'device_number' => $device->device_number,
                'status' => $device->status,
            ] : null,
            'notification_message' => $notification->notification_message,
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at
                ? $notification->created_at->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s')
                : null,
            'updated_at' => $notification->updated_at
                ? $notification->updated_at->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s')
                : null,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $transformedNotifications,
        'meta' => [
            'total' => $transformedNotifications->count(),
            'unread_count' => $transformedNotifications->where('is_read', false)->count(),
        ]
    ]);
}

public function markDeviceNotificationsAsRead(Request $request)
{
    $userId = $request->user()->id;

    Notification::where('type', 'devices')
        ->where('to_user_id', $userId)
        ->where('is_read', false)
        ->update(['is_read' => true]);

    return response()->json([
        'success' => true,
        'message' => 'All device notifications marked as read'
    ]);
}
public function getLeaveNotifications(Request $request)
{
    $userId = $request->user()->id;

    $notifications = Notification::where('type', 'leaves')
        ->where('to_user_id', $userId)
        ->latest()
        ->get();

    $transformed = $notifications->map(function ($notification) {
        $leave = Leave::find($notification->type_id);
        
        // Process the notification message to add styling
        $message = $this->formatNotificationMessage($notification->notification_message);

        return [
            'id' => $notification->id,
            'from_user_id' => $notification->from_user_id,
            'from_user_name' => optional($notification->fromUser)->name,
            'leave_id' => optional($leave)->id,
            'leave_type' => optional($leave)->type_of_leave,
            'notification_message' => $message,
            'raw_message' => $notification->notification_message, // Keep original for reference
            'is_read' => $notification->is_read,
            'created_at' => $notification->created_at
                ? $notification->created_at->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s')
                : null,
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $transformed,
        'meta' => [
            'total' => $transformed->count(),
            'unread_count' => $transformed->where('is_read', false)->count(),
        ]
    ]);
}

protected function formatNotificationMessage($message)
{
    // Status colors
    $message = preg_replace('/\b(APPROVED|CONFIRMED)\b/i', '<span style="color: #2ecc71;">$0</span>', $message);
    $message = preg_replace('/\b(REJECTED|DENIED|CANCELED)\b/i', '<span style="color: #e74c3c;">$0</span>', $message);
    $message = preg_replace('/\b(PENDING|WAITING)\b/i', '<span style="color: #f39c12;">$0</span>', $message);
    
    // Highlight changes (from -> to patterns)
    $message = preg_replace('/(updated from)(.*?)(to)/i', '$1<span style="color: #e74c3c;">$2</span>$3<span style="color: #2ecc71;">', $message);
    $message = preg_replace('/(changed from)(.*?)(to)/i', '$1<span style="color: #e74c3c;">$2</span>$3<span style="color: #2ecc71;">', $message);
    
    // Close any open spans
    if (strpos($message, '<span') !== false && substr_count($message, '<span') > substr_count($message, '</span>')) {
        $message .= '</span>';
    }
    
    return $message;
}

    public function markLeaveNotificationsAsRead(Request $request)
{
    $userId = $request->user()->id;

    Notification::where('type', 'leaves')
        ->where('to_user_id', $userId)
        ->where('is_read', false)
        ->update(['is_read' => true]);

    return response()->json([
        'success' => true,
        'message' => 'All leave notifications marked as read'
    ]);
}
}
