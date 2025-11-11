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
                    'name' => 'Contatti',
                    'property' => [
                        'phone' => '3271622244',
                        'email' => '',
                        'whatsapp' => '',
                        'youtube' => '',
                        'instagram' => '',
                        'tiktok' => '',
                    ]
                ],
                [
                    'name' => 'advanced',
                    'property' => [
                        'max_delay_default' => 24,
                        'day_off' => [],
                        'field_set'=> [],
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
