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
                'developer_assign_list' => $request->developer_assign_list ?? [],
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
    public function index()
    {
        $devices = Device::orderBy('created_at', 'desc')->get();

        $devices = $devices->map(function ($device) {
            $developerIds = is_array($device->developer_assign_list)
                ? $device->developer_assign_list
                : json_decode($device->developer_assign_list, true);

            // Join users with user_profiles to get name and user_image
            $developers = DB::table('users')
                ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
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

            // Track changes (for history)
            $changes = [];
            foreach (['device', 'device_number', 'note', 'developer_assign_list', 'date_of_assign', 'status'] as $field) {
                $oldValue = $device->{$field};
                $newValue = $request->{$field};

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
                'developer_assign_list' => $request->developer_assign_list ?? [],
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
                    $ids = array_merge(
                        $ids,
                        $entry['changes']['developer_assign_list']['from'] ?? [],
                        $entry['changes']['developer_assign_list']['to'] ?? []
                    );
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

                $changes['developer_assign_list'] = [
                    'from' => $this->mapDeveloperIdsToNames($devChanges['from'] ?? [], $users),
                    'to' => $this->mapDeveloperIdsToNames($devChanges['to'] ?? [], $users),
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
