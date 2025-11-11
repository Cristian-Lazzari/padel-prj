<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index(){
        $setting = Setting::all()->keyBy('name');
        $field_arr=[];
        $field_set = json_decode(Setting::where('name', 'advanced')->first()->property, 1)['field_set'];
        foreach ($field_set as $k => $f) {
            $field_arr[] = $f['type'];
        }
        return response()->json([
            'success' => true,
            'type_of_field' => array_unique($field_arr),
            'service' => $setting['Servizio di Prenotazione Online']->status,
            'social' => $setting['Contatti']->property
        ]);
    }

    public function client_default(Request $request) {
        $id = $request->query('id');
        //return $messageId;

        if (!$id) {
            return response()->json(['error' => 'id mancante'], 400);
        }
        $reservation = Reservation::where('id', $id)->first();
         // parse della data
        $date = Carbon::createFromFormat('Y/m/d H:i', $reservation->date_slot);

        // se la data è già passata → false
        if ($date->isPast()) {
            return false;
        }
        $hours = json_decode(Setting::where('name', 'advanced')->property, 1)['max_delay_default'];
        // controlla se manca almeno 1 giorno
        $is_ok=  $date->gte(now()->addHours($hours));
        if (!$reservation) {
            return response()->json(['error' => 'Nessuna prenotazione trovata'], 404);
        }
        if(in_array($reservation->status, [0]) && $is_ok){
            $reservation->status = 0;
            
            return view('guests.delete_success');
        }



        return response()->json(['error' => 'Prenotazione gia annullata'], 400);
    }
}
