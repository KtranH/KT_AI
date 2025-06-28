<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Interaction;

class InteractionLikeImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $interaction = new Interaction();
        $interaction->user_id = 1;
        $interaction->image_id = 1;
        $interaction->type_interaction = 'like';
        $interaction->status_interaction = 'active';
        $interaction->save();

        $interaction = new Interaction();
        $interaction->user_id = 2;
        $interaction->image_id = 1;
        $interaction->type_interaction = 'like';
        $interaction->status_interaction = 'active';
        $interaction->save();

        $interaction = new Interaction();
        $interaction->user_id = 5;
        $interaction->image_id = 1;
        $interaction->type_interaction = 'like';
        $interaction->status_interaction = 'active';
        $interaction->save();
    }
}
