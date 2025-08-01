<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ai_features_seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'id' => 1,
                'title' => 'Tạo ảnh bằng mô tả',
                'slug' => 'tao-anh-bang-mo-ta',
                'description' => 'Tự do sáng tạo các hình ảnh bằng trí tưởng tượng của bạn thông qua đoạn mô tả',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_1.png',
                'category' => 'Text to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 2,
                'title' => 'Tạo ảnh theo phong cách ảnh khác',
                'slug' => 'tao-anh-bang-mo-ta',
                'description' => 'Tạo ra các bức ảnh lấy phong cách từ các bức ảnh bạn cung cấp',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_2.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 3,
                'title' => 'Kết hợp phong cách 2 hình ảnh',
                'slug' => 'ket-hop-phong-cach-2-hinh-anh',
                'description' => 'Tạo ra các bức ảnh mà kết hợp phong cách từ 2 hình ảnh khác nhau do bạn cung cấp',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_3.png',
                'input_requirements' => 2,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 4,
                'title' => 'Chuyển đổi nét vẽ thành hình ảnh',
                'slug' => 'chuyen-doi-net-ve-thanh-hinh-anh',
                'description' => 'Chuyển đổi các nét vẽ thủ công thành hình ảnh màu sắc',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_4.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 5,
                'title' => 'Chuyển đổi phong cách sang Anime',
                'slug' => 'chuyen-doi-phong-cach-sang-anime',
                'description' => 'Chuyển đổi bất kì hình ảnh nào của bạn sang phong cách Anime',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_5.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 6,
                'title' => 'Tạo hình ảnh dễ thương',
                'slug' => 'tao-hinh-anh-de-thuong',
                'description' => 'Tạo hình ảnh dễ thương 3D từ dòng mô tả của bạn',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_6.png',
                'category' => 'Text to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 7,
                'title' => 'Tạo ảnh siêu thực siêu nhanh',
                'slug' => 'tao-hinh-anh-de-thuong',
                'description' => 'Tạo ảnh siêu thực và siêu nhanh trong 10s',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_7.png',
                'category' => 'Text to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 8,
                'title' => 'Tạo hình ảnh theo Pose',
                'slug' => 'tao-hinh-anh-theo-pose',
                'description' => 'Bạn muốn tạo ảnh theo Pose của riêng bạn? Thử ngay.',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_8.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 9,
                'title' => 'Tạo hình ảnh Ghibli từ khuôn mặt của bạn',
                'slug' => 'tao-hinh-anh-ghibli-tu-khuon-mat',
                'description' => 'Sáng tạo các hình ảnh Ghibli hoặc hình ảnh dịu dàng từ khuôn mặt của bạn',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_9.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 10,
                'title' => 'Tạo hình ảnh sticker từ khuôn mặt của bạn',
                'slug' => 'tao-hinh-sticker-tu-khuon-mat',
                'description' => 'Tạo ra các hình ảnh sticker dễ thương từ khuôn mặt của bạn',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_10.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 11,
                'title' => 'Làm nét hình ảnh',
                'slug' => 'lam-net-anh',
                'description' => 'Tăng độ phân giải, làm nét hình ảnh của bạn',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_11.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 12,
                'title' => 'Đổi màu sắc quần áo',
                'slug' => 'doi-mau-sac-quan-ao',
                'description' => 'Thay đổi màu sắc hình ảnh quần áo trong bất kì hình ảnh nào của bạn',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_12.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 13,
                'title' => 'Pha trộn hình ảnh nâng cao',
                'slug' => 'pha-tron-hinh-anh',
                'description' => 'Tải hình ảnh của bạn để pha trộn phong cách với nhau với phiên bản nâng cao',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_13.png',
                'input_requirements' => 2,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 14,
                'title' => 'Tạo sticker dễ thương',
                'slug' => 'pha-tron-hinh-anh',
                'description' => 'Tạo các sticker dễ thương từ mô tả của bạn',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_14.png',
                'category' => 'Text to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 15,
                'title' => 'Mở rộng hình ảnh',
                'slug' => 'mo-rong-anh',
                'description' => 'Tự động nội suy để tăng mở rộng hình ảnh của bạn',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_15.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 16,
                'title' => 'Tạo avatar 3D',
                'slug' => 'mo-rong-anh',
                'description' => 'Tạo ra các hình ảnh 3D từ khuôn mặt của bạn để làm ảnh đại diện',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_16.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 17,
                'title' => 'Thử quần áo phiên bản standard',
                'slug' => 'thu-quan-ao-phien-ban-standard',
                'description' => 'Tạo ra người mẫu ảo để thử các quần áo của bạn (Lưu ý ở phiên bản standard thì không thể lấy các họa tiết trên áo)',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_17.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 18,
                'title' => 'Tạo ảnh nội thất',
                'slug' => 'tao-anh-noi-that',
                'description' => 'Tự do sáng tạo hình ảnh nội thất, phòng ngủ, phòng khách và nhiều kiến trúc khác',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_18.png',
                'category' => 'Text to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 19,
                'title' => 'Tạo nền background sản phẩm',
                'slug' => 'tao-nen-background-san-pham',
                'description' => 'Bạn muốn thêm hình nền cho sản phẩm của bạn thêm phần chuyền nghiệp? Tải lên và thử ngay',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_19.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 20,
                'title' => 'Thử quần áo phiên bản Premium',
                'slug' => 'thu-quan-ao-phien-ban-premium',
                'description' => 'Sáng tạo hơn, chuyên nghiệp hơn phiên bản Standard. Có thể lấy họa tiết hình ảnh',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_20.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 21,
                'title' => 'Tạo người mẫu thử quần áo theo khuôn mặt của bạn',
                'slug' => 'tao-nguoi-mau-thu-ao',
                'description' => 'Tạo ra một người mẫu thử quần ảo với khuôn mặt do bạn chọn.',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_21.png',
                'input_requirements' => 2,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 22,
                'title' => 'Chuyển đổi ảnh từ anime sang hình ảnh thực tế',
                'slug' => 'chuyen-doi-anh-tu-anime-sang-hinh-anh-thuc-te',
                'description' => 'Chuyển đổi ảnh anime sang hình ảnh thực tế',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_22.png',
                'input_requirements' => 1,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ],
            [
                'id' => 23,
                'title' => 'Tạo ảnh ghép các vật thể từ 2 ảnh khác nhau',
                'slug' => 'tao-anh-ghep-các-vat-the-tu-2-anh-khac-nhau',
                'description' => 'Tạo ra các bức ảnh ghép các vật thể từ 2 ảnh khác nhau',
                'prompt_template' => '1girl, cute, smile...',
                'credit_cost' => 0,
                'thumbnail_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/ai_features/id_23.png',
                'input_requirements' => 2,
                'category' => 'Image to Image',
                'sum_img' => 0,
                'average_processing_time' => 300,
                'status_feature' => 'active'
            ]
        ];

        foreach ($features as $feature) {
            DB::table('ai_features')->insert($feature);
        }
    }
}
