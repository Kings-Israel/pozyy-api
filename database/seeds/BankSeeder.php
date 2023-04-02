<?php

use App\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $banks = [
            'Cooperative Bank',
            'Kenya Commercial Bank(KCB)',
            'Equity Bank',
            'National Bank',
            'Kingdom Bank',
            'Sidian Bank',
            'Standard Chartered Bank',
            'Barclays Bank',
            'Family Bank',
            'I&M Bank',
            'Diamond Trust Bank',
            'Jamii Bora Bank',
            'Prime Bank',
        ];

        foreach ($banks as $bank) Bank::create(['name' => $bank]);
    }
}
