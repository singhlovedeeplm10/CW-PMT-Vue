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
                'email' => 'adbbharat@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'status' => '1', // Active
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vineet Sharma',
                'email' => 'vineet@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('vineetsharma'),
                'status' => '1', // Active
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Steve Rogers',
                'email' => 'steve@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('steverogers'),
                'status' => '0', // Inactive
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rohan Sharma',
                'email' => 'rohan@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('rohansharma'),
                'status' => '1', // Active
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bruce Wayne',
                'email' => 'bruce@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('brucewayne'),
                'status' => '0', // Inactive
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Clark Kent',
                'email' => 'clark@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('clarkkent'),
                'status' => '1', // Active
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peter Parker',
                'email' => 'peter@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('peterparker'),
                'status' => '1', // Active
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gaurav Singh',
                'email' => 'gaurav@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('gauravsingh'),
                'status' => '0', // Inactive
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jitu Singh',
                'email' => 'jitu@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('jitusingh'),
                'status' => '1', // Active
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('janesmith'),
                'status' => '1', // Active
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Assign roles to users:
        User::find(1)->assignRole($adminRole); // Assign Admin role to the first user
        User::find(2)->assignRole($employeeRole); // Assign Employee role to the second user
    }
}
