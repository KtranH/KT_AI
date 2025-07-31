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
                'prompt' => '1girl, cute, smile, beautiful, detailed, high quality',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample1.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'features_id' => 2,
                'prompt' => 'anime style, 1girl, blue hair, school uniform, detailed',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample2.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'features_id' => 3,
                'prompt' => 'landscape, sunset, mountains, beautiful nature, realistic',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample3.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'features_id' => 4,
                'prompt' => 'sketch to image, line art, colorful, detailed',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample4.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => 5,
                'user_id' => 5,
                'features_id' => 5,
                'prompt' => 'anime conversion, 1girl, pink hair, detailed face',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample5.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'id' => 6,
                'user_id' => 6,
                'features_id' => 6,
                'prompt' => 'cute 3D character, chibi style, pastel colors',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample6.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ],
            [
                'id' => 7,
                'user_id' => 7,
                'features_id' => 1,
                'prompt' => 'fantasy landscape, magical forest, glowing mushrooms',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample7.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'id' => 8,
                'user_id' => 8,
                'features_id' => 2,
                'prompt' => 'portrait, professional, business suit, confident pose',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample8.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'id' => 9,
                'user_id' => 9,
                'features_id' => 3,
                'prompt' => 'cyberpunk city, neon lights, futuristic architecture',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample9.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
            [
                'id' => 10,
                'user_id' => 10,
                'features_id' => 4,
                'prompt' => 'watercolor style, flowers, spring, soft colors',
                'image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/images/sample10.jpg',
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
                'status_image' => 'completed',
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],
        ];

        DB::table('images')->insert($images);
    }
} 