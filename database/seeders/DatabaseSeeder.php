<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\PlayersTableSeeder;
use Database\Seeders\SettingsTableSeeder;
use Database\Seeders\ReservationsTableSeeder;



class DatabaseSeeder extends Seeder
{
    public function run()
    {
     

        $this->call([
            UsersTableSeeder::class,
            SettingsTableSeeder::class,
            ReservationsTableSeeder::class,
            PlayersTableSeeder::class,

        ]);
    }
}
