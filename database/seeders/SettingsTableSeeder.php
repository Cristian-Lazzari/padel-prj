<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\FieldHour;
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
                    'name' => 'Impostazioni cena',
                    'status' => 0,
                    'property' => [
                        'user_mail' => '',
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
                        'delay_trainer' => 7,
                        'max_delay_default' => 24,
                        'day_off' => [],
                        'field_set'=> [
                            'Campo 1' => [
                                'h_start' => '08:00',
                                "n_slot"=>"10",
                                "m_during"=>"30",
                                "m_during_client"=>"90",
                                "type"=>"padel",
                                "closed_days"=>[]
                            ],
                            'Campo 2' => [
                                'h_start' => '08:00',
                                "n_slot"=>"10",
                                "m_during"=>"30",
                                "m_during_client"=>"90",
                                "type"=>"padel",
                                "closed_days"=>[]
                            ],
                            'Campo 3' => [
                                'h_start' => '08:00',
                                "n_slot"=>"10",
                                "m_during"=>"30",
                                "m_during_client"=>"90",
                                "type"=>"padel",
                                "closed_days"=>[]
                            ],
                        ],
                        'trainer_set'=> [],
                    ]
                ],
                
            ];
      

        foreach ($settings as $s) {
            $string = json_encode($s['property'], true);  
            $s['property'] = $string;
            // Creazione della voce di impostazione
            Setting::create($s);
        }

        $this->campi();
    }

    /**
     * I campi nelle loro tabelle: è da lì che il gestionale legge gli orari,
     * il `field_set` qui sopra resta come copia di sicurezza.
     */
    private function campi(): void
    {
        foreach (['Campo 1', 'Campo 2', 'Campo 3'] as $sort => $nome) {
            $field = Field::create([
                'name' => $nome,
                'type' => 'padel',
                'm_during' => 30,
                'm_during_client' => 90,
                'sort' => $sort,
            ]);

            foreach (array_keys(FieldHour::WEEKDAYS) as $weekday) {
                FieldHour::create([
                    'field_id' => $field->id,
                    'weekday' => $weekday,
                    'closed' => false,
                    'h_start' => '08:00',
                    'h_end' => '23:00',
                ]);
            }
        }
    }
}
