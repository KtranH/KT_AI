<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserActivitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::where('id', '2')->update(
            [
                'activities' =>  json_encode([
                    ['action' => 'test thử', 'timestamp' => now()->toDateTimeString()],
                    ['action' => 'thêm mới', 'timestamp' => now()->subMinutes(5)->toDateTimeString()],
                    ['action' => 'sửa', 'timestamp' => now()->subMinutes(10)->toDateTimeString()],
                    ['action' => 'xóa', 'timestamp' => now()->subMinutes(15)->toDateTimeString()],
                ])
            ]
        );
    }
}
