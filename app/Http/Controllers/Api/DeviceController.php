<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
   public function addDevice(Request $request)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'device' => 'required|string|max:255',
        'device_number' => 'required|string|max:255',
        'note' => 'nullable|string',
        'developer_assign_list' => 'nullable|array',
        'developer_assign_list.*' => 'integer|exists:users,id',
        'date_of_assign' => 'nullable|date',
        'status' => 'nullable|in:0,1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Create the device with history tracking
        $device = Device::create([
            'device' => $request->device,
            'device_number' => $request->device_number,
            'note' => $request->note,
            'developer_assign_list' => json_encode($request->developer_assign_list ?? []),
            'date_of_assign' => $request->date_of_assign,
            'status' => $request->status ?? '1',
            'history' => [
                [
                    'changed_at' => now()->toDateTimeString(),
                    'changes' => [
                        'created' => 'Device record created'
                    ],
                    'changed_by' => auth()->id()
                ]
            ]
        ]);

        // Handle notifications if developers are assigned
        if (!empty($request->developer_assign_list)) {
            $developerList = $request->developer_assign_list;

            if (!is_array($developerList)) {
                $developerList = [$developerList];
            }

            foreach ($developerList as $developerId) {
                \App\Models\Notification::create([
                    'from_user_id' => auth()->id(),
                    'to_user_id' => $developerId,
                    'type' => 'devices',
                    'type_id' => $device->id,
                    'notification_message' => "{$device->device} ({$device->device_number}) Assigned",
                    // 'notification_message' => "Device Assigned: {$device->device} ({$device->device_number})",
                    'is_read' => false,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Device created successfully',
            'data' => $device
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create device',
            'error' => $e->getMessage()
        ], 500);
    }
}
//  public function addDevice(Request $request)
//     {
//         // Validate the request data
//         $validator = Validator::make($request->all(), [
//             'device' => 'required|string|max:255',
//             'device_number' => 'required|string|max:255',
//             'note' => 'nullable|string',
//             'developer_assign_list' => 'nullable|array',
//             'developer_assign_list.*' => 'integer|exists:users,id',
//             'date_of_assign' => 'nullable|date',
//             'status' => 'nullable|in:0,1',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Validation failed',
//                 'errors' => $validator->errors()
//             ], 422);
//         }

//         try {
//             // Create the device with history tracking
//             $device = Device::create([
//                 'device' => $request->device,
//                 'device_number' => $request->device_number,
//                 'note' => $request->note,
//                 'developer_assign_list' => $request->developer_assign_list ?? [],
//                 'date_of_assign' => $request->date_of_assign,
//                 'status' => $request->status ?? '1',
//                 'history' => [
//                     [
//                         'changed_at' => now()->toDateTimeString(),
//                         'changes' => [
//                             'created' => 'Device record created'
//                         ],
//                         'changed_by' => auth()->id()
//                     ]
//                 ]
//             ]);

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Device created successfully',
//                 'data' => $device
//             ], 201);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Failed to create device',
//                 'error' => $e->getMessage()
//             ], 500);
//         }
//     }
    public function index()
    {
        $devices = Device::orderBy('created_at', 'desc')->get();

        $devices = $devices->map(function ($device) {
            $developerIds = is_array($device->developer_assign_list)
                ? $device->developer_assign_list
                : json_decode($device->developer_assign_list, true);

            // Join users with user_profiles to get name and user_image
            $developers = DB::table('users')
                ->leftJoin('user_profiles', 'users.id', '=', 'user_profiles.user_id')
                ->whereIn('users.id', $developerIds)
                ->select('users.id', 'users.name', 'user_profiles.user_image')
                ->get();

            $device->developers = $developers;
            return $device;
        });

        return response()->json($devices);
    }

public function updateDevice(Request $request, $id)
{
    // Validate the request data
    $validator = Validator::make($request->all(), [
        'device' => 'required|string|max:255',
        'device_number' => 'required|string|max:255',
        'note' => 'nullable|string',
        'developer_assign_list' => 'nullable|array',
        'developer_assign_list.*' => 'integer|exists:users,id',
        'date_of_assign' => 'nullable|date',
        'status' => 'nullable|in:0,1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Find device
        $device = Device::findOrFail($id);

        // Get current and new developer lists - ensure they are arrays
        $currentDeveloperList = is_array($device->developer_assign_list) 
            ? $device->developer_assign_list 
            : (array) json_decode($device->developer_assign_list ?? '[]', true);
        
        $newDeveloperList = $request->developer_assign_list ?? [];

        // Track changes (for history)
        $changes = [];
        foreach (['device', 'device_number', 'note', 'developer_assign_list', 'date_of_assign', 'status'] as $field) {
            $oldValue = $device->{$field};
            $newValue = $request->{$field};

            // Special handling for developer_assign_list to ensure array comparison
            if ($field === 'developer_assign_list') {
                $oldValue = is_array($oldValue) ? $oldValue : (array) json_decode($oldValue ?? '[]', true);
                $newValue = is_array($newValue) ? $newValue : (array) json_decode($newValue ?? '[]', true);
            }

            // Serialize to JSON to compare arrays like developer_assign_list
            if (is_array($oldValue) || is_array($newValue)) {
                if (json_encode($oldValue) !== json_encode($newValue)) {
                    $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
                }
            } elseif ($oldValue != $newValue) {
                $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
            }
        }

        // Update fields
        $device->update([
            'device' => $request->device,
            'device_number' => $request->device_number,
            'note' => $request->note,
            'developer_assign_list' => $newDeveloperList,
            'date_of_assign' => $request->date_of_assign,
            'status' => $request->status ?? '1'
        ]);

        // Append change log to history
        if (!empty($changes)) {
            $history = $device->history ?? [];
            $history[] = [
                'changed_at' => now()->toDateTimeString(),
                'changes' => $changes,
                'changed_by' => auth()->id()
            ];
            $device->history = $history;
            $device->save();
        }

        // Handle notifications only if developer assignments changed
        if (isset($changes['developer_assign_list'])) {
            $deviceName = $device->device;
            $deviceNumber = $device->device_number;
            $notificationBaseMessage = "{$deviceName} ({$deviceNumber})";

            // Get all existing notifications for this device assignment
            $existingNotifications = \App\Models\Notification::where('type', 'devices')
                ->where('type_id', $device->id)
                ->where('from_user_id', auth()->id())
                ->whereIn('notification_message', [
                    "{$notificationBaseMessage} Assigned",
                    "{$notificationBaseMessage} Unassigned"
                ])
                ->get();

            // Ensure both lists are arrays before diffing
            $newDeveloperList = is_array($newDeveloperList) ? $newDeveloperList : [];
            $currentDeveloperList = is_array($currentDeveloperList) ? $currentDeveloperList : [];

            // Process newly assigned developers
            $newlyAssigned = array_diff($newDeveloperList, $currentDeveloperList);
            foreach ($newlyAssigned as $developerId) {
                // Check if this user already has an assignment notification
                $alreadyNotified = $existingNotifications->contains(function ($notification) use ($developerId) {
                    return $notification->to_user_id == $developerId && 
                           str_contains($notification->notification_message, 'Assigned');
                });

                if (!$alreadyNotified) {
                    \App\Models\Notification::create([
                        'from_user_id' => auth()->id(),
                        'to_user_id' => $developerId,
                        'type' => 'devices',
                        'type_id' => $device->id,
                        'notification_message' => "{$notificationBaseMessage} Assigned",
                        'is_read' => false,
                    ]);
                }
            }

            // Process unassigned developers
            $newlyUnassigned = array_diff($currentDeveloperList, $newDeveloperList);
            foreach ($newlyUnassigned as $developerId) {
                // Check if this user already has an unassignment notification
                $alreadyNotified = $existingNotifications->contains(function ($notification) use ($developerId) {
                    return $notification->to_user_id == $developerId && 
                           str_contains($notification->notification_message, 'Unassigned');
                });

                if (!$alreadyNotified) {
                    \App\Models\Notification::create([
                        'from_user_id' => auth()->id(),
                        'to_user_id' => $developerId,
                        'type' => 'devices',
                        'type_id' => $device->id,
                        'notification_message' => "{$notificationBaseMessage} Unassigned",
                        'is_read' => false,
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Device updated successfully',
            'data' => $device
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update device',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // public function updateDevice(Request $request, $id)
    // {
    //     // Validate the request data
    //     $validator = Validator::make($request->all(), [
    //         'device' => 'required|string|max:255',
    //         'device_number' => 'required|string|max:255',
    //         'note' => 'nullable|string',
    //         'developer_assign_list' => 'nullable|array',
    //         'developer_assign_list.*' => 'integer|exists:users,id',
    //         'date_of_assign' => 'nullable|date',
    //         'status' => 'nullable|in:0,1',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         // Find device
    //         $device = Device::findOrFail($id);

    //         // Track changes (for history)
    //         $changes = [];
    //         foreach (['device', 'device_number', 'note', 'developer_assign_list', 'date_of_assign', 'status'] as $field) {
    //             $oldValue = $device->{$field};
    //             $newValue = $request->{$field};

    //             // Serialize to JSON to compare arrays like developer_assign_list
    //             if (is_array($oldValue) || is_array($newValue)) {
    //                 if (json_encode($oldValue) !== json_encode($newValue)) {
    //                     $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
    //                 }
    //             } elseif ($oldValue != $newValue) {
    //                 $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
    //             }
    //         }

    //         // Update fields
    //         $device->update([
    //             'device' => $request->device,
    //             'device_number' => $request->device_number,
    //             'note' => $request->note,
    //             'developer_assign_list' => $request->developer_assign_list ?? [],
    //             'date_of_assign' => $request->date_of_assign,
    //             'status' => $request->status ?? '1'
    //         ]);

    //         // Append change log to history
    //         if (!empty($changes)) {
    //             $history = $device->history ?? [];
    //             $history[] = [
    //                 'changed_at' => now()->toDateTimeString(),
    //                 'changes' => $changes,
    //                 'changed_by' => auth()->id()
    //             ];
    //             $device->history = $history;
    //             $device->save();
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Device updated successfully',
    //             'data' => $device
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to update device',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
public function history($id)
{
    $device = Device::findOrFail($id);
    $historyData = $device->history ?? [];

    // Sort history data by changed_at in descending order
    usort($historyData, function ($a, $b) {
        $dateA = strtotime($a['changed_at'] ?? '1970-01-01');
        $dateB = strtotime($b['changed_at'] ?? '1970-01-01');
        return $dateB <=> $dateA; // Descending order
    });

    // Get all unique user IDs from both changed_by and developer lists
    $userIds = collect($historyData)
        ->flatMap(function ($entry) {
            $ids = [$entry['changed_by'] ?? null];

            // Get developer IDs from changes if they exist
            if (isset($entry['changes']['developer_assign_list'])) {
                $devChanges = $entry['changes']['developer_assign_list'];

                // Normalize 'from' and 'to' to arrays
                $from = $devChanges['from'] ?? [];
                $to = $devChanges['to'] ?? [];

                $from = is_array($from) ? $from : [$from];
                $to = is_array($to) ? $to : [$to];

                $ids = array_merge($ids, $from, $to);
            }

            return $ids;
        })
        ->filter()
        ->unique()
        ->values();

    // Get all users in one query
    $users = User::whereIn('id', $userIds)
        ->get(['id', 'name'])
        ->keyBy('id');

    // Transform history with proper names
    $transformedHistory = array_map(function ($entry) use ($users) {
        $changes = $entry['changes'] ?? [];

        // Transform developer changes to include names
        if (isset($changes['developer_assign_list'])) {
            $devChanges = $changes['developer_assign_list'];

            // Normalize 'from' and 'to' again before mapping
            $from = $devChanges['from'] ?? [];
            $to = $devChanges['to'] ?? [];

            $from = is_array($from) ? $from : [$from];
            $to = is_array($to) ? $to : [$to];

            $changes['developer_assign_list'] = [
                'from' => $this->mapDeveloperIdsToNames($from, $users),
                'to' => $this->mapDeveloperIdsToNames($to, $users),
            ];
        }

        return [
            'changed_at' => $entry['changed_at'] ?? null,
            'changes' => $changes,
            'changed_by' => $entry['changed_by'] ?? null,
            'changed_by_name' => $users[$entry['changed_by'] ?? null]->name ?? 'Unknown User',
        ];
    }, $historyData);

    return response()->json($transformedHistory);
}

protected function mapDeveloperIdsToNames(array $developerIds, $users)
{
    return array_map(function ($id) use ($users) {
        return [
            'id' => $id,
            'name' => $users[$id]->name ?? 'Unknown User'
        ];
    }, $developerIds);
}

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:0,1',
        ]);

        $device = Device::findOrFail($id);
        $originalStatus = $device->status;

        if ($originalStatus == $request->status) {
            return response()->json(['message' => 'Status is already set.'], 200);
        }

        // Prepare change history
        $changes = [
            'status' => [
                'from' => $originalStatus,
                'to' => $request->status,
            ]
        ];

        // Append to history
        $history = $device->history ?? [];
        $history[] = [
            'changed_at' => now()->toDateTimeString(),
            'changes' => $changes,
            'changed_by' => auth()->id()
        ];

        // Save updated status and history
        $device->status = $request->status;
        $device->history = $history;
        $device->save();

        return response()->json(['message' => 'Status updated successfully.'], 200);
    }

    public function getUserDevices(Request $request)
    {
        $userId = $request->user()->id;

        $devices = Device::whereJsonContains('developer_assign_list', (string)$userId)
            ->select('id', 'device', 'device_number', 'note', 'date_of_assign', 'status')
            ->get();

        return response()->json([
            'success' => true,
            'devices' => $devices
        ]);
    }

    public function getUserAssignedDevices(Request $request)
    {
        $userId = Auth::id(); // assumes token-based auth

        $devices = Device::whereJsonContains('developer_assign_list', [$userId])
            ->where('status', '1') // filter only active devices
            ->get();

        return response()->json($devices);
    }
}
