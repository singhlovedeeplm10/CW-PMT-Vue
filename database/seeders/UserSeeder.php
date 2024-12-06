<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Bharat Chauhan',
                'email' => 'adbbharat@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('janesmith'), // password hashing
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Steve Rogers',
                'email' => 'steve@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('steverogers'), // password hashing
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rohan Sharma',
                'email' => 'rohan@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('rohansharma'), // password hashing
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bruce Wayne',
                'email' => 'bruce@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('brucewayne'), // password hashing
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Clark Kent',
                'email' => 'clark@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('clarkkent'), // password hashing
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peter Parker',
                'email' => 'peter@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('peterparker'), // password hashing
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gaurav Singh',
                'email' => 'gaurav@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('gauravsingh'), // password hashing
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jitu Singh',
                'email' => 'jitu@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('jitusingh'), // password hashing
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vineet Sharma',
                'email' => 'vineet@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('vineetsharma'), // password hashing
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}