<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoClockOutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:clockout';
    

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clock out users and set default productive hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get current time in Indian Standard Time (IST)
        $currentTime = Carbon::now('Asia/Kolkata');

        // Find users with null clockout_time and productive_hours
        $users = DB::table('attendances')
            ->whereNull('clockout_time')
            ->whereNull('productive_hours')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users found to clock out.');
            return;
        }

        // Update the records
        foreach ($users as $user) {
            // Get the corresponding token
            $token = DB::table('personal_access_tokens')
                ->where('tokenable_id', $user->user_id)
                ->where('name', 'ClockinToken')
                ->first();

            if ($token) {
                // Update attendance
                DB::table('attendances')
                    ->where('id', $user->id)
                    ->update([
                        'clockout_time' => $user->clockin_time,  // Same as clock-in time
                        'productive_hours' => '00:00:00',
                        'updated_at' => $currentTime,
                    ]);

                // Remove the ClockinToken
                DB::table('personal_access_tokens')
                    ->where('id', $token->id)
                    ->delete();

                $this->info("User ID {$user->user_id} clocked out successfully.");
            }
        }
    }
}
