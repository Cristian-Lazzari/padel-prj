<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function updateAree(Request $request){
        $setting = Setting::where('name', 'Comuni per il domicilio')->firstOrFail();
        $ar = $request->ar;
        if($ar == 'add'){ //se positivo aggiungie senno elimina
            $comune = $request->comune;
            $provincia = $request->provincia;
            $newarea = [
                'comune' => $comune,
                'provincia' => $provincia,
            ];
            $setting['property'] = json_decode($setting['property'], true);
            $isnew = true;
            foreach ($setting['property']  as $k) {
                if($k['comune'] == $comune){
                    $isnew = false;
                }
            }
            if ($isnew && $comune !== " ") {
                $newp = $setting['property'];

                array_push( $newp, $newarea);
                $setting['property'] = json_encode($newp);
                $setting->save();
            }
        }else{
            $comuni = $request->comuni;
            if($comuni !== null){
                $setting['property'] = json_decode($setting['property'], true);
                $upc = [];
                foreach ($setting['property'] as $co) {
                    if(!in_array($co['comune'], $comuni)){
                        array_push( $upc, $co);
                    }     
                }
                $setting['property'] = $upc;
                $setting['property'] = json_encode($upc);
                $setting->save();
            }
        }
        $m = 'Gli indirizzi sono stati aggiornati correttamente';

        return redirect()->back()->with('success', $m); 
    }
    
    public function numbers(Request $request){
        $numbers = $request->numbers;

     //   dd($numbers);
        $setting = Setting::where('name', 'wa')->firstOrFail();
        $old_p = json_decode($setting->property, true);
        $old_p['numbers'] = $numbers;
        //$array_filtrato = array_filter($old_p);
        // $array_filtrato = array_filter($old_p, function($value) {
        //     return $value !== null && $value !== ''; // Filtra valori null e stringhe vuote
        // });
        $array_filtrato = [];
        foreach ($numbers as $n) {
           if($n !== null && $n !== ' '){
            array_push($array_filtrato, $n);
           }
        }
        $old_p['numbers'] = $array_filtrato;
        $setting->property = json_encode($old_p);
        $setting->update();

        $m = 'I numeri sono stati aggiornati correttamente';
    
        return redirect()->back()->with('success', $m); 
    }
    public function updateAll(Request $request)
    {
        $setting = Setting::all();

    
        $tavoli = $request->tavoli_status;
        $asporto = $request->asporto_status;
        $ferie = $request->ferie_status;
        $ferie_from = $request->from;
        $ferie_to = $request->to;

        $delivery_cost = $request->delivery_cost;
        $min_price_a = $request->min_price_a;
        $min_price_d = $request->min_price_d;
        $pay_a = $request->asporto_pay;
        $pay_d = $request->domicilio_pay;
    
        $setting[0]->status = $tavoli;
        $setting[0]->save();

        
        
        $setting[1]->status = $asporto;
        if (config('configurazione.pack') > 2) {
            $prop_apsorto = [
                'pay' => intval($pay_a),
                'min_price' => $min_price_a * 100,
            ];
            $setting[1]->property = json_encode($prop_apsorto);
        }
        $setting[1]->save();
        
        // Aggiornare il terzo setting
        $setting[2]->status = $ferie;
        
        $propertyArray = [
            'from' => $ferie_from,
            'to' => $ferie_to,
        ];
        // Aggiorna il terzo setting
        $setting[2]->status = $ferie;
        $setting[2]->property = json_encode($propertyArray);
        $setting[2]->save();

        $giorni_attivita = [
            'lunedì'    =>  $request->lunedì,
            'martedì'   =>  $request->martedì,
            'mercoledì' =>  $request->mercoledì,
            'giovedì'   =>  $request->giovedì,
            'venerdì'   =>  $request->venerdì,
            'sabato'    =>  $request->sabato,
            'domenica'  =>  $request->domenica,
        ];
        $setting[3]->property = json_encode($giorni_attivita);
        $setting[3]->save();
        //dd( $request['foto_maps']);

        $oldPosition = json_decode($setting[4]['property'], 1);

        if(isset($oldPosition['foto_maps'])){
            $posizione = [
                'foto_maps' =>  $oldPosition['foto_maps'],
                'link_maps' =>  $request->link_maps,
                'indirizzo' =>  $request->indirizzo,
            ]; 
            
        
            if (isset($request->foto_maps)) {
                $imagePath = $request->file('foto_maps')->store('public/uploads');
                $posizione['foto_maps'] = $imagePath;
            }
        }else{
            $posizione = [
                'foto_maps' =>  "",
                'link_maps' =>  $request->link_maps,
                'indirizzo' =>  $request->indirizzo,
            ];
            if (isset($request->foto_maps)) {
                $imagePath = $request->file('foto_maps')->store('public/uploads');
                $posizione['foto_maps'] = $imagePath;
            }
        }
        $setting[4]->property = json_encode($posizione);
        $setting[4]->save();

        $contatti = [
            'telefono'  => $request->telefono,
            'email'     => $request->email,
        ];
        $setting[5]->property = json_encode($contatti);
        $setting[5]->save();      
        
        if (config('configurazione.pack') == 3 || config('configurazione.pack') == 4) {
            $setting[7]->property = json_encode($setting[7]->property);
            $setting[6]->status = $request->domicilio_status;
            $prop_domicilio = [
                'pay' => intval($pay_d),
                'min_price' => $min_price_d * 100,
                'delivery_cost' => $delivery_cost * 100,
            ];
            $setting[6]->property = json_encode($prop_domicilio);
            $setting[6]->save();
        }
        
        $m = 'Le impostazioni sono state ggiornate correttamente';

        return redirect()->back()->with('success', $m);   
    }
}
