<?php

use Illuminate\Database\Seeder;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Hash;
use \App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $check = \App\User::where('username', '=', 'taji-admin')->first();
        if (!$check){
            // $admin = DB::table('users')->insert([
            $admin = User::create([
                'fname' => 'Pozzy',
                'lname' => 'Admin',
                'username' => 'pozzy-admin',
                'email' => 'admin@deveint.com',
                'phone_number' => '254725730055',
                'password' => Hash::make('secretpassword'),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            $admin->assignRole('admin');

            $pozzy = User::create([
                'fname' => 'Pozzy',
                'lname' => 'Admin',
                'username' => 'pozzy-admin',
                'email' => 'admin@pozzy.com',
                'phone_number' => '254728408711',
                'password' => Hash::make('secretpassword'),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            $pozzy->assignRole('admin');
        }

        $check = \App\User::where('username', '=', 'user-deveint')->first();
        if (!$check){
            $user = User::create([
                'fname' => 'User',
                'lname' => 'Deveint',
                'username' => 'user-deveint',
                'email' => 'user@deveint.com',
                'phone_number' => '254725730021',
                'password' => Hash::make('123456'),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
            $user->assignRole('user');
        }

        $check = \App\User::where('username', '=', 'data-deveint')->first();
        if (!$check){
            $data_entry = User::create([
                'fname' => 'Data',
                'lname' => 'Entry',
                'username' => 'data-deveint',
                'email' => 'data@deveint.com',
                'phone_number' => '254725730009',
                'password' => Hash::make('123456'),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
        $data_entry->assignRole('data_entry');
    }
}
