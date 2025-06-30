<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users with their profiles
        $usersWithProfiles = DB::table('users')
            ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->select('users.email', 'user_profiles.id as profile_id')
            ->get();

        foreach ($usersWithProfiles as $user) {
            $credentials = [
                [
                    "label" => "Pmt-Tool",
                    "username" => $user->email,
                    "password" => "", 
                    "note" => ""
                ]
            ];

            DB::table('user_profiles')
                ->where('id', $user->profile_id)
                ->update(['credentials' => json_encode($credentials)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all credentials to null when rolling back
        DB::table('user_profiles')->update(['credentials' => null]);
    }
};