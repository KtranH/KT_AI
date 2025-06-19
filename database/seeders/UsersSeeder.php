<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $users = [
            [
                'id' => '1',
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => '$2y$12$5iQh1KbeP06bS0xTtKUVc.GgJmfC2dhCEdV4UtOVFKNyxTui4IcKy', 
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png',
                'sum_like' => 0,
                'sum_img' => 0,
                'remaining_credits' => 0,
                'status_user' => 'active',
            ]
        ];
        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }
    }
}
