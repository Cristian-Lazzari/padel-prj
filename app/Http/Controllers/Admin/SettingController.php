<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\User;
use App\Models\Player;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{

    
    public function index(){
        $settings = Setting::all()->keyBy('name');
        $users = User::where('role', 'trainer')->get();
        $trainers = [];
        foreach ($users as $u) {
            $player = Player::find($u->playerId);
            $player->flag = $u->flag;
            $trainers[] = $player;
        }
        return view('admin.settings', compact('settings' , 'trainers'));
    }

    public function updateAll(Request $request)
    {
        $setting = Setting::all()->keyBy('name');
        $data = $request->all();

        $setting['Servizio di Prenotazione Online']->status = $data['status_service'];
        $setting['Servizio di Prenotazione Online']->save();

        $setting['Periodo di Ferie']->status = $data['ferie_status'];
        $propertyArray = [
            'from' => $data['from'],
            'to' => $data['to'],
        ];
        $setting['Periodo di Ferie']->property = json_encode($propertyArray);
        $setting['Periodo di Ferie']->save();

        
        if(!isset($setting['Impostazioni cena'])) {
            $setting['Impostazioni cena'] = new Setting();
            $setting['Impostazioni cena']->name = 'Impostazioni cena';
        }

        $setting['Impostazioni cena']->status = $data['dinner_status'];
        $propertyArray = [
            'user_mail' => $data['user_mail'],
        ];
        $setting['Impostazioni cena']->property = json_encode($propertyArray);
        $setting['Impostazioni cena']->save();


        $contatti = [
            'phone'  => $request->phone,
            'email'     => $request->email,
            'instagram' => $request->instagram,
            'facebook'  => $request->facebook,
            'youtube'   => $request->youtube,
            'tiktok'    => $request->tiktok,
            'whatsapp'  => $request->whatsapp,
        ];
        $setting['Contatti']->property = json_encode($contatti);
        $setting['Contatti']->save();      
        
        $day_off = json_decode($setting['advanced']->property, 1)['day_off'];
        $field_set = json_decode($setting['advanced']->property, 1)['field_set'];
        $trainer_set = json_decode($setting['advanced']->property, 1)['trainer_set']?? [];
                

        
        foreach ($data['field_set'] as $k => $f) {
            $field_set[$f['name_field']] = [
                'h_start'           => $f['h_start'],
                'n_slot'            => $f['n_slot'],
                'm_during'          => $f['m_during'],
                'm_during_client'   => $f['m_during_client'],        
                'type'              => $f['type'],        
                'closed_days'       => isset($f['closed_days']) ? $f['closed_days'] : [],        
            ];
        }
       
        if (auth()->user()->role == 'trainer') {
            $validated = $request->validate([
                'set_trainer.h_start' => ['required', 'date_format:H:i'],
                'set_trainer.h_end'   => ['required', 'date_format:H:i', 'after:set_trainer.h_start'],
            ], [
                'set_trainer.h_end.after' => 'L\'ora di fine deve essere successiva all\'ora di inizio.',
            ]);
        
            $trainer_set[auth()->user()->id] = [
                'field' => $data['set_trainer']['field'] ?? 0,
                'h_start' => $data['set_trainer']['h_start'],
                'h_end' => $data['set_trainer']['h_end'],
                'day_w' => $data['set_trainer']['day_w'] ?? [],
            ];
            
        }
        $setting['advanced']->property = json_encode([
            'day_off'           => $day_off,
            'max_delay_default' => $data['max_delay_default'],
            'delay_trainer'     => $data['delay_trainer'],
            'field_set'         => $field_set,  
            'trainer_set'       => $trainer_set,
        ]);

        $setting['advanced']->save();
        
        $m = 'Le impostazioni sono state ggiornate correttamente';

        return redirect()->back()->with('success', $m);   
    }

    public function cancelDates(Request $request){
        $data = $request->all();
        $s = Setting::where('name', 'advanced')->first();
        // /dd($data['day_off']);
        $adv = json_decode($s->property, 1);
        $adv['day_off'] = $data['day_off'] ?? [];

        $s->property = json_encode($adv);
        $s->update();
        
        return redirect()->route('admin.dashboard')->with('message', 'Le date sono state modificate correttamente');

    }
}
