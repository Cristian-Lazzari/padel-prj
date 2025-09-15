<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReservationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields   = [1, 2, 3];
        $statuses = [1, 2, 3];
        $status   = [true, false];

        for ($i = 0; $i < 5; $i++) { // più prenotazioni
            // giorno casuale entro i prossimi 30 giorni
            $date = now()->addDays(rand(0, 30));

            // ora casuale tra 10:00 e 23:30
            $hour   = rand(10, 23);
            $minute = rand(0, 1) ? '00' : '30'; // minuti sempre 00 o 30

            $dateSlot = $date->setTime($hour, (int) $minute);

            DB::table('reservations')->insert([
                'date_slot' => $dateSlot->format('Y-m-d H:i'),
                'field'     => $fields[array_rand($fields)],
                'status'    => $statuses[array_rand($statuses)],
                'message'   => rand(0, 1) ? Str::random(20) : null,
                'dinner'    => json_encode([
                    'status' => array_rand($status),
                    'guests' => rand(1, 8),
                    'time' => $dateSlot->format('H:i')
                ]),
                'booking_subject' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
