<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chạy theo thứ tự để đảm bảo foreign key constraints
        $this->call([
            ai_features_seed::class,      // Bảng gốc, không phụ thuộc vào bảng nào
            users_seed::class,            // Bảng users
            admin_users_seed::class,      // Bảng admin_users
            images_seed::class,           // Phụ thuộc vào users và ai_features
            comments_seed::class,         // Phụ thuộc vào users và images
            interactions_seed::class,     // Phụ thuộc vào users và images
            image_jobs_seed::class,       // Phụ thuộc vào users và ai_features
            reports_seed::class,          // Phụ thuộc vào admin_users và images
        ]);
    }
} 