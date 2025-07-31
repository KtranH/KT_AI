<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class admin_users_seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUsers = [
            [
                'id' => 1,
                'name' => 'Admin Super',
                'email' => 'admin@ktai.com',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'last_login_at' => now(),
                'status_admin' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Moderator 1',
                'email' => 'moderator1@ktai.com',
                'password' => Hash::make('moderator123'),
                'role' => 'moderator',
                'last_login_at' => now(),
                'status_admin' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Moderator 2',
                'email' => 'moderator2@ktai.com',
                'password' => Hash::make('moderator123'),
                'role' => 'moderator',
                'last_login_at' => now(),
                'status_admin' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Admin Support',
                'email' => 'support@ktai.com',
                'password' => Hash::make('support123'),
                'role' => 'admin',
                'last_login_at' => now(),
                'status_admin' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Content Manager',
                'email' => 'content@ktai.com',
                'password' => Hash::make('content123'),
                'role' => 'admin',
                'last_login_at' => now(),
                'status_admin' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('admin_users')->insert($adminUsers);
    }
} 