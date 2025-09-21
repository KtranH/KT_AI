<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class images_seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $images = [
            [
                'id' => 1,
                'user_id' => 1,
                'features_id' => 1,
                'title' => 'Ảnh test 1',
                'prompt' => '1girl, cute, smile, beautiful, detailed, high quality',
                'image_url' => json_encode(['https://picsum.photos/512/768?random=1']),
                'sum_like' => 15,
                'sum_comment' => 3,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'width' => 512,
                    'height' => 768,
                    'seed' => 123456789,
                    'steps' => 20,
                    'cfg_scale' => 7.5
                ]),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'features_id' => 1,
                'title' => 'Ảnh test 2',
                'prompt' => 'anime style, 1girl, blue hair, school uniform, detailed',
                'image_url' => json_encode(['https://picsum.photos/512/768?random=2']),
                'sum_like' => 23,
                'sum_comment' => 7,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'width' => 512,
                    'height' => 768,
                    'seed' => 987654321,
                    'steps' => 25,
                    'cfg_scale' => 8.0
                ]),
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'features_id' => 1,
                'title' => 'Ảnh test 3',
                'prompt' => 'landscape, sunset, mountains, beautiful nature, realistic',
                'image_url' => json_encode(['https://picsum.photos/1024/768?random=3']),
                'sum_like' => 45,
                'sum_comment' => 12,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'width' => 1024,
                    'height' => 768,
                    'seed' => 456789123,
                    'steps' => 30,
                    'cfg_scale' => 7.0
                ]),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'features_id' => 1,
                'title' => 'Ảnh test 4',
                'prompt' => 'sketch to image, line art, colorful, detailed',
                'image_url' => json_encode(['https://picsum.photos/512/512?random=4']),
                'sum_like' => 18,
                'sum_comment' => 5,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'width' => 512,
                    'height' => 512,
                    'seed' => 789123456,
                    'steps' => 20,
                    'cfg_scale' => 7.5
                ]),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => 5,
                'user_id' => 5,
                'features_id' => 1,
                'title' => 'Ảnh test 5',
                'prompt' => 'anime conversion, 1girl, pink hair, detailed face',
                'image_url' => json_encode(['https://picsum.photos/512/768?random=5']),
                'sum_like' => 34,
                'sum_comment' => 8,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'width' => 512,
                    'height' => 768,
                    'seed' => 321654987,
                    'steps' => 25,
                    'cfg_scale' => 8.0
                ]),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'id' => 6,
                'user_id' => 6,
                'features_id' => 1,
                'title' => 'Ảnh test 6',
                'prompt' => 'cute 3D character, chibi style, pastel colors',
                'image_url' => json_encode(['https://picsum.photos/512/512?random=6']),
                'sum_like' => 28,
                'sum_comment' => 6,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'width' => 512,
                    'height' => 512,
                    'seed' => 147258369,
                    'steps' => 20,
                    'cfg_scale' => 7.5
                ]),
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ],
            [
                'id' => 7,
                'user_id' => 7,
                'features_id' => 1,
                'title' => 'Ảnh test 7',
                'prompt' => 'fantasy landscape, magical forest, glowing mushrooms',
                'image_url' => json_encode(['https://picsum.photos/1024/768?random=7']),
                'sum_like' => 56,
                'sum_comment' => 15,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'width' => 1024,
                    'height' => 768,
                    'seed' => 963852741,
                    'steps' => 30,
                    'cfg_scale' => 7.0
                ]),
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'id' => 8,
                'user_id' => 8,
                'features_id' => 1,
                'title' => 'Ảnh test 8',
                'prompt' => 'portrait, professional, business suit, confident pose',
                'image_url' => json_encode(['https://picsum.photos/512/768?random=8']),
                'sum_like' => 12,
                'sum_comment' => 2,
                'privacy_status' => 'private',
                'metadata' => json_encode([
                    'width' => 512,
                    'height' => 768,
                    'seed' => 852963741,
                    'steps' => 20,
                    'cfg_scale' => 7.5
                ]),
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'id' => 9,
                'user_id' => 9,
                'features_id' => 1,
                'title' => 'Ảnh test 9',
                'prompt' => 'cyberpunk city, neon lights, futuristic architecture',
                'image_url' => json_encode(['https://picsum.photos/1024/768?random=9']),
                'sum_like' => 67,
                'sum_comment' => 18,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'width' => 1024,
                    'height' => 768,
                    'seed' => 741852963,
                    'steps' => 30,
                    'cfg_scale' => 7.0
                ]),
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
            [
                'id' => 10,
                'user_id' => 10,
                'features_id' => 1,
                'title' => 'Ảnh test 10',
                'prompt' => 'watercolor style, flowers, spring, soft colors',
                'image_url' => json_encode(['https://picsum.photos/512/512?random=10']),
                'sum_like' => 39,
                'sum_comment' => 9,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'width' => 512,
                    'height' => 512,
                    'seed' => 369258147,
                    'steps' => 25,
                    'cfg_scale' => 8.0
                ]),
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],
        ];

        DB::table('images')->insert($images);
    }
} 