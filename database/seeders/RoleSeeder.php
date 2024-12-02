<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['title' => 'Super Admin', 'description' => 'Super Administrator role'],
            ['title' => 'Admin', 'description' => 'Super Administrator role'],
            ['title' => 'HR', 'description' => 'Human Resource role'],
            ['title' => 'User', 'description' => 'User role'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
