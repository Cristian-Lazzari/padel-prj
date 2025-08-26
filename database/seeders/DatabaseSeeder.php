<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Seeders\UsersTableSeeder;

use Database\Seeders\SettingsTableSeeder;



class DatabaseSeeder extends Seeder
{
    public function run()
    {
     

        $this->call([
            UsersTableSeeder::class,
            SettingsTableSeeder::class,

        ]);
    }
}
