<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class reports_seed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reports = [
            [
                'id' => 1,
                'admin_id' => 2,
                'image_id' => 8,
                'status_report' => 'waiting',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'id' => 2,
                'admin_id' => 3,
                'image_id' => 8,
                'status_report' => 'waiting',
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
            [
                'id' => 3,
                'admin_id' => 1,
                'image_id' => 8,
                'status_report' => 'accept',
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],
        ];

        DB::table('reports')->insert($reports);
    }
} 