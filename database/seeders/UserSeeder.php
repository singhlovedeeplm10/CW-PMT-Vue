<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadminRole = Role::where('title', 'Super Admin')->first();
        $adminRole = Role::where('title', 'Admin')->first();
        $hrRole = Role::where('title', 'HR')->first();
        $userRole = Role::where('title', 'User')->first();

        $users = [
            [
                'name' => 'Bharat Chauhan',
                'email' => 'adbbharat@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'role_id' => $adminRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('janesmith'), // password hashing
                'role_id' => $hrRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Steve Rogers',
                'email' => 'steve@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('steverogers'), // password hashing
                'role_id' => $superadminRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rohan Sharma',
                'email' => 'rohan@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('rohansharma'), // password hashing
                'role_id' => $adminRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bruce Wayne',
                'email' => 'bruce@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('brucewayne'), // password hashing
                'role_id' => $hrRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Clark Kent',
                'email' => 'clark@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('clarkkent'), // password hashing
                'role_id' => $adminRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peter Parker',
                'email' => 'peter@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('peterparker'), // password hashing
                'role_id' => $superadminRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gaurav Singh',
                'email' => 'gaurav@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('gauravsingh'), // password hashing
                'role_id' => $userRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jitu Singh',
                'email' => 'jitu@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('jitusingh'), // password hashing
                'role_id' => $userRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vineet Sharma',
                'email' => 'vineet@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('vineetsharma'), // password hashing
                'role_id' => $userRole->id,
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
        
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
