<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(){
        $setting = Setting::all()->keyBy('name');
        return response()->json([
            'success' => true,
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
        if (!$reservation) {
            return response()->json(['error' => 'Nessuna prenotazione trovata'], 404);
        }
        if(in_array($reservation->status, [0])){
            $reservation->status = 0;
            
            return view('guests.delete_success');
        }



        return response()->json(['error' => 'Prenotazione gia annullata'], 400);
    }
}
