<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;

class ImageTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Image::all()->each(function ($image) {
            $image->title = 'Hiện chưa có title';
            $image->save();
        });
    }
}
