<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingsTableSeeder extends Seeder
{
   
    public function run()
    {
        
        
     
            $settings = [
                [
                    'name' => 'Call reserving',  
                    'status' => 1,
                    'property' => [
                        'empty' => 0
                    ],
                ],
                [
                    'name' => 'Periodo di Ferie',  
                    'status' => 0,
                    'property' => [
                        'from' => '',
                        'to' => '',
                    ]
                ],
                [
                    'name' => 'Orari di attività',
                    'property' => []
                ],
                [
                    'name' => 'Contatti',
                    'property' => []
                ],
                [
                    'name' => 'wa',
                    'property' => [
                        'last_response_wa_1' => '',
                        'last_response_wa_2' => '',
                        'numbers' => ['393271622244'],

                    ]
                ],
            ];
      

        foreach ($settings as $s) {
            $string = json_encode($s['property'], true);  
            $s['property'] = $string;
            dump( $s['name']);
            // Creazione della voce di impostazione
            Setting::create($s);
        }
    }
}
