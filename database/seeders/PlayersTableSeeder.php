<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlayersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'role'      => 'gio',
                'nickname'  => 'Gio',
                'name'      => 'Giordano',
                'surname'   => 'Giorgi',
                'phone'     => '3385314356',
                'mail'      => 'info@future-plus.it',
                'sex'       => 'm'
            ],
            [
                'role'      => 'trainer',
                'nickname'  => 'Dany',
                'name'      => 'Daniel',
                'surname'   => 'Martín',
                'phone'     => '3394773897',
                'mail'      => 'info@future-plus.it',
                'sex'       => 'm'
            ],
            [
                'role'      => 'player',
                'nickname'  => 'Cris',
                'name'      => 'Cristian',
                'surname'   => 'Lazzari',
                'phone'     => '3271622244',
                'mail'      => 'info@future-plus.it',
                'sex'       => 'm'
            ],
            
            

        ];

        foreach ($users as $u) {
            Player::create($u);
        }

    }
}
