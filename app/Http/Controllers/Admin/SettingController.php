<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{

    
    public function index(){
        $settings = Setting::all()->keyBy('name');
        return view('admin.settings', compact('settings'));
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

        $giorni_attivita = [
            'lunedì'    => [ 'from' => $data['lunedì_from'], 'to' => $data['lunedì_to']],
            'martedì'   => [ 'from' => $data['martedì_from'], 'to' => $data['martedì_to']],
            'mercoledì' => [ 'from' => $data['mercoledì_from'], 'to' => $data['mercoledì_to']],
            'giovedì'   => [ 'from' => $data['giovedì_from'], 'to' => $data['giovedì_to']],
            'venerdì'   => [ 'from' => $data['venerdì_from'], 'to' => $data['venerdì_to']],
            'sabato'    => [ 'from' => $data['sabato_from'], 'to' => $data['sabato_to']],
            'domenica'  => [ 'from' => $data['domenica_from'], 'to' => $data['domenica_to']],
        ];
        $setting['Orari di attività']->property = json_encode($giorni_attivita);
        $setting['Orari di attività']->save();

        $setting['advanced']->property = json_encode([
            'max_delay_default' => $data['max_delay_defalt'],
        ]);
        $setting['advanced']->save();
        



        
        $m = 'Le impostazioni sono state ggiornate correttamente';

        return redirect()->back()->with('success', $m);   
    }
}
