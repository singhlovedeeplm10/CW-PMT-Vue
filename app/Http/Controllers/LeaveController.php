<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class LeaveController extends Controller
{
    public function showLeaveList()
     {
         return view('leaves.leaves_list');
     }

     public function getLeaveDetails()
    {
        // Get the currently logged-in user's ID
        $userId = Auth::id();

        // Retrieve the user's latest leave record
        $leave = Leave::where('user_id', $userId)->latest()->first();

        // Check if leave data exists
        if ($leave) {
            return response()->json($leave);
        } else {
            return response()->json(['message' => 'No leave records found.'], 404);
        }
    }

     
     
}
