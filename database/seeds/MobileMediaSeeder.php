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
            MobileSections::SCHOOL_TV()->label, MobileSections::CLUBS()->label, MobileSections::INDOOR()->label, MobileSections::OUTDOOR()->label
        ];

        collect($sections)->each(function($section) {
            MobileMedia::create(['section' => $section]);
        });
    }
}
