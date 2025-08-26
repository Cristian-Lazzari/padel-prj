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
                    'name' => 'Servizio di Prenotazione Online',  
                    'status' => 0,
                    'property' => []
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
