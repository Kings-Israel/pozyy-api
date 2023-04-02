<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class); //ensure order to create roles first
        $this->call(UserSeeder::class);
        $this->call(TestCategorySeeder::class);
        $this->call(GamesSeeder::class);
        $this->call(MobileMediaSeeder::class);
    }
}
