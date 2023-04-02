<?php

use Illuminate\Database\Seeder;
use \App\Models\TestCategory;

class TestCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TestCategory::create([
            'name' => 'exam',
        ]);

        TestCategory::create([
            'name' => 'cat',
        ]);

        TestCategory::create([
            'name' => 'homework',
        ]);
    }
}
