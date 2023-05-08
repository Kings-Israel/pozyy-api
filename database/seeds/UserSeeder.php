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
        $check = \App\User::where('username', '=', 'pozzy-admin')->first();
        if (!$check){
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
        } else {
            $check->update([
                'password' => Hash::make('123pozyy@deveint'),
            ]);
        }

        $check_pozzy = User::where('email', 'admin@pozzy.com')->first();
        if (!$check_pozzy) {
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
        } else {
            $check_pozzy->update([
                'password' => Hash::make('123pozyy@deveint'),
            ]);
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

        $check = \App\User::where('username', '=', 'parent-deveint')->first();
        if (!$check){
            $user = User::create([
                'fname' => 'Parent',
                'lname' => 'Deveint',
                'username' => 'parent-deveint',
                'email' => 'parent@deveint.com',
                'phone_number' => '254725730521',
                'password' => Hash::make('password'),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
            $user->assignRole('parent');
        }
    }
}
