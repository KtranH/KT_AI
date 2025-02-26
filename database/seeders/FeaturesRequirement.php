<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeaturesRequirement extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $features = [
            [
                'id' => 1,
                'input_requirements' => null
            ],
            [
                'id' => 2,
                'input_requirements' => 1
            ],
            [
                'id' => 3,
                'input_requirements' => 2
            ],
            [
                'id' => 4,
                'input_requirements' => 1
            ],
            [
                'id' => 5,
                'input_requirements' => 1
            ],
            [
                'id' => 6,
                'input_requirements' => null
            ],
            [
                'id' => 7,
                'input_requirements' => null
            ],
            [
                'id' => 8,
                'input_requirements' => 1
            ],
            [
                'id' => 9,
                'input_requirements' => 1
            ],
            [
                'id' => 10,
                'input_requirements' => 1
            ],
            [
                'id' => 11,
                'input_requirements' => 1
            ],
            [
                'id' => 12,
                'input_requirements' => 1
            ],
            [
                'id' => 13,
                'input_requirements' => 2
            ],
            [
                'id' => 14,
                'input_requirements' => null
            ],
            [
                'id' => 15,
                'input_requirements' => 1
            ],
            [
                'id' => 16,
                'input_requirements' => 1
            ],
            [
                'id' => 17,
                'input_requirements' => 1
            ],
            [
                'id' => 18,
                'input_requirements' => null
            ],
            [
                'id' => 19,
                'input_requirements' => 1
            ],
            [
                'id' => 20,
                'input_requirements' => 1
            ],
            [
                'id' => 21,
                'input_requirements' => 2
            ]
        ];
        foreach ($features as $feature) {
            DB::table('ai_features')->where('id', $feature['id'])->update($feature);
        }
    }
}
