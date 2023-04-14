<?php

use App\GameNight;
use App\GameNightCategory;
use Illuminate\Database\Seeder;

class GameNightCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GameNightCategory::create([
            'name' => 'Game Day',
        ]);

        GameNightCategory::create([
            'name' => 'Family Challenge',
        ]);

        $game_nights = GameNight::all();

        $game_nights->each(function($game_night) {
            $index = mt_rand(1, 2);
            $game_night->update([
                'category_id' => $index,
            ]);
        });
    }
}
