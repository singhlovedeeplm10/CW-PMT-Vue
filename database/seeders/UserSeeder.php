<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $employeeRole = Role::where('name', 'Employee')->first();

        DB::table('users')->insert([
            [
                'name' => 'Bharat Chauhan',
                'email' => 'contriwhiz@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('M7)-B45!?pSt'),
                'status' => '1', // Active
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'name' => 'Vineet Sharma',
            //     'email' => 'vineet@gmail.com',
            //     'email_verified_at' => now(),
            //     'password' => Hash::make('vineetsharma'),
            //     'status' => '1', // Active
            //     'remember_token' => Str::random(10),
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
        ]);

        // Assign roles to users:
        User::find(1)->assignRole($adminRole); // Assign Admin role to the first user
        // User::find(2)->assignRole($employeeRole); // Assign Employee role to the second user
    }
}
