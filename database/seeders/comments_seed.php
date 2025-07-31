<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class comments_seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comments = [
            // Comments cho image 1
            [
                'id' => 1,
                'user_id' => 2,
                'image_id' => 1,
                'parent_id' => 0,
                'content' => 'Ảnh đẹp quá! Bạn dùng prompt gì vậy?',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'id' => 2,
                'user_id' => 3,
                'image_id' => 1,
                'parent_id' => 1,
                'content' => 'Tôi cũng muốn biết prompt này!',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'image_id' => 1,
                'parent_id' => 1,
                'content' => 'Cảm ơn! Tôi dùng prompt: 1girl, cute, smile, beautiful, detailed, high quality',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            
            // Comments cho image 2
            [
                'id' => 4,
                'user_id' => 4,
                'image_id' => 2,
                'parent_id' => 0,
                'content' => 'Phong cách anime rất đẹp!',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'id' => 5,
                'user_id' => 5,
                'image_id' => 2,
                'parent_id' => 0,
                'content' => 'Màu sắc rất hài hòa',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => 6,
                'user_id' => 6,
                'image_id' => 2,
                'parent_id' => 4,
                'content' => 'Tôi cũng thích phong cách này!',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            
            // Comments cho image 3
            [
                'id' => 7,
                'user_id' => 7,
                'image_id' => 3,
                'parent_id' => 0,
                'content' => 'Cảnh đẹp như tranh vẽ!',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => 8,
                'user_id' => 8,
                'image_id' => 3,
                'parent_id' => 0,
                'content' => 'Ánh sáng hoàng hôn rất ấn tượng',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'id' => 9,
                'user_id' => 9,
                'image_id' => 3,
                'parent_id' => 7,
                'content' => 'Tôi muốn đi du lịch đến nơi này!',
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ],
            
            // Comments cho image 4
            [
                'id' => 10,
                'user_id' => 10,
                'image_id' => 4,
                'parent_id' => 0,
                'content' => 'Chuyển đổi từ sketch rất tốt!',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'id' => 11,
                'user_id' => 1,
                'image_id' => 4,
                'parent_id' => 10,
                'content' => 'Cảm ơn! Tôi đã thử nhiều lần mới được kết quả này',
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            
            // Comments cho image 5
            [
                'id' => 12,
                'user_id' => 2,
                'image_id' => 5,
                'parent_id' => 0,
                'content' => 'Chuyển đổi sang anime rất thành công!',
                'created_at' => now()->subHours(8),
                'updated_at' => now()->subHours(8),
            ],
            [
                'id' => 13,
                'user_id' => 3,
                'image_id' => 5,
                'parent_id' => 0,
                'content' => 'Màu tóc hồng rất đẹp',
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ],
            
            // Comments cho image 6
            [
                'id' => 14,
                'user_id' => 4,
                'image_id' => 6,
                'parent_id' => 0,
                'content' => 'Nhân vật 3D dễ thương quá!',
                'created_at' => now()->subHours(10),
                'updated_at' => now()->subHours(10),
            ],
            [
                'id' => 15,
                'user_id' => 5,
                'image_id' => 6,
                'parent_id' => 14,
                'content' => 'Tôi cũng thích phong cách chibi này',
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
            
            // Comments cho image 7
            [
                'id' => 16,
                'user_id' => 6,
                'image_id' => 7,
                'parent_id' => 0,
                'content' => 'Cảnh rừng ma thuật rất ấn tượng!',
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ],
            [
                'id' => 17,
                'user_id' => 7,
                'image_id' => 7,
                'parent_id' => 0,
                'content' => 'Nấm phát sáng rất đẹp',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            
            // Comments cho image 9
            [
                'id' => 18,
                'user_id' => 8,
                'image_id' => 9,
                'parent_id' => 0,
                'content' => 'Thành phố cyberpunk rất cool!',
                'created_at' => now()->subMinutes(45),
                'updated_at' => now()->subMinutes(45),
            ],
            [
                'id' => 19,
                'user_id' => 9,
                'image_id' => 9,
                'parent_id' => 18,
                'content' => 'Ánh đèn neon rất ấn tượng',
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],
            
            // Comments cho image 10
            [
                'id' => 20,
                'user_id' => 10,
                'image_id' => 10,
                'parent_id' => 0,
                'content' => 'Phong cách watercolor rất mềm mại',
                'created_at' => now()->subMinutes(15),
                'updated_at' => now()->subMinutes(15),
            ],
        ];

        DB::table('comments')->insert($comments);
    }
} 