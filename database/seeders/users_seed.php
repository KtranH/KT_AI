<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class users_seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Nguyễn Văn An',
                'email' => 'an.nguyen@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 45,
                'sum_img' => 12,
                'remaining_credits' => 15,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Trần Thị Bình',
                'email' => 'binh.tran@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 23,
                'sum_img' => 8,
                'remaining_credits' => 20,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Lê Văn Cường',
                'email' => 'cuong.le@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 67,
                'sum_img' => 25,
                'remaining_credits' => 8,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Phạm Thị Dung',
                'email' => 'dung.pham@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 34,
                'sum_img' => 15,
                'remaining_credits' => 12,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Hoàng Văn Em',
                'email' => 'em.hoang@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 89,
                'sum_img' => 32,
                'remaining_credits' => 5,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Vũ Thị Phương',
                'email' => 'phuong.vu@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 56,
                'sum_img' => 18,
                'remaining_credits' => 18,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Đỗ Văn Giang',
                'email' => 'giang.do@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 12,
                'sum_img' => 5,
                'remaining_credits' => 25,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Ngô Thị Hoa',
                'email' => 'hoa.ngo@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 78,
                'sum_img' => 28,
                'remaining_credits' => 10,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Lý Văn Inh',
                'email' => 'inh.ly@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 45,
                'sum_img' => 16,
                'remaining_credits' => 14,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'Trịnh Thị Kim',
                'email' => 'kim.trinh@example.com',
                'password' => Hash::make('password123'),
                'avatar_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover.jpg',
                'sum_like' => 67,
                'sum_img' => 22,
                'remaining_credits' => 8,
                'status_user' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}
