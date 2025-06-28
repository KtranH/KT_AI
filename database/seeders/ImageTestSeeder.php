<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Seeder;
use App\Models\Image;

class ImageTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $image = [
            [
                'id' => 1,
                'user_id' => 2,
                'features_id' => 1,
                'prompt' => '1girl, 2girl, 3girl',
                'image_url' => json_encode([
                    'https://cdn-media.sforum.vn/storage/app/media/anh-dep-102.jpg',
                    'https://cdn-media.sforum.vn/storage/app/media/anh-dep-103.jpg',
                    'https://cdn-media.sforum.vn/storage/app/media/anh-dep-104.jpg',
                ]),
                'sum_like' => 0,
                'sum_comment' => 0,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'tags' => ['girl', 'beauty', 'portrait'],
                ]),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'features_id' => 1,
                'prompt' => 'Thử nghiệm lần 2',
                'image_url' => json_encode([
                    'https://cdn-media.sforum.vn/storage/app/media/anh-dep-104.jpg'
                ]),
                'sum_like' => 0,
                'sum_comment' => 0,
                'privacy_status' => 'public',
                'metadata' => json_encode([
                    'tags' => ['girl', 'beauty', 'portrait'],
                ]),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ],
        ];
        foreach ($image as $img) {
            Image::updateOrCreate(['id' => $img['id']], $img);
        }
    }
}
