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
}
