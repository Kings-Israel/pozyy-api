<?php

use App\Enum\MobileSections;
use App\MobileMedia;
use Illuminate\Database\Seeder;

class MobileMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sections = [
            MobileSections::GUIDE()->label, MobileSections::GAME_NIGHT()->label, MobileSections::SCHOOL()->label, MobileSections::SHOP()->label
        ];

        collect($sections)->each(function($section) {
            MobileMedia::create(['section' => $section]);
        });
    }
}
