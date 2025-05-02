<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Image;

class DeleteAllCommentSeeder extends Seeder
{
    public function run()
    {
        Comment::truncate();
        foreach (Image::all() as $image) {
            $image->sum_comment = 0;
            $image->save();
        }
    }
}
