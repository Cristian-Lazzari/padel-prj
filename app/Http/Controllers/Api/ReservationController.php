<?php

namespace App\Http\Controllers\Api;

use App\Models\Player;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    
    public function get_reservation()
    {
        $data = $request->all();

        $new_player = '';
        if(!$data['new_player']){
            //creo il nuovo giocatore
            $new_player = new Player();

            $new_player->name = $data['name'];                            
            $new_player->surname = $data['surname'];
            $new_player->nickname = $data['nickname'];
            $new_player->phone = $data['phone'];
            $new_player->mail = $data['mail'];
  
            $new_player->save();
        }else{
            $new_player = Player::where('nickname', $data['new_player'])->first();
        }

        $field = $data['field'];
        $date = $data['date'];
        $time = $data['time'];

        $date_exists = Date::where('date', $date)->where('field', $field)->first();
        $date_slot = '';
        if($date_exists){
            //controllo se l'orario è già prenotato
            $date_times = json_decode($date_exists->field, true);
            if(in_array($time, $date_times[$field])){
                return response()->json([
                    'success' => true,
                    'message' => 'L\'orario selezionato non è disponibile, ricarica la pagina e riprova',
                    'data' => $data
                ]);
            }else{
                //aggiungo l'orario alla data
                $date_times[$field][] = $time;
                $date_exists->field = json_encode($date_times);
                $date_exists->save();
            }
            $date_slot = $date . ' ' . $time;
        }else{
            $new_date = new Date();
            $new_date->date = $date;
            $field_times = [
                1 => [],
                2 => [],
                3 => [],
            ];
            $field_times[$field][] = $time;
            $new_date->field = json_encode($field_times);
            $new_date->save();
            $date_slot = $date . ' ' . $time;
        }

        $match = new Reservation();
        $match->date_slot = $date_slot;
        $match->field = $field; // 1, 2, 3 
        $match->status = 1; // 1 confirmed, 2 cancelled, 3 noshow
        $match->dinner = json_encode($date['dinner']); //[ status, ospiti, orario] 
        $match->message = $data['message'] ?? null;
    
        $match->save();
        $team = [$new_player->id];
        if(isset($data['team'])){     
            foreach ($data['team'] as $p) {
                $player = Player::where('nickname', $p['nickname'])->first();
                array_push($team, $player->id);
            }
        }
        $contact = json_decode(Setting::where('name', 'Contatti')->first()->property);
        $advanced = json_decode(Setting::where('name', 'advanced')->first()->property);
        $bodymail = [
            'to' => 'admin',

            'title' =>  $new_player->name . ' ha appena prenotato il campo ' . $match->field . ' per il ' . $match->date_slot,
            'subtitle' => $date['dinner']['status'] ? 'Ha anche prenotato la cena per ' . $date['dinner']['ospiti'] . ' persone alle ore ' . $date['dinner']['orario'] : 'Non ha prenotato la cena',
            
            'name' => $new_player->name,
            'surname' => $new_player->surname,
            'email' => $new_player->email,

            'date_slot' => $match->date_slot,
            'team' => $match->players,

            'message' => $data['message'],

            'phone' => $new_player->phone,
            'admin_phone' => $contact['telefono'],

            'max_delay_default' => $advanced['max_delay_default'],
        
        ];
        $mailAdmin = new confermaOrdineAdmin($bodymail);
        Mail::to(config('configurazione.mail'))->send($mailAdmin);

        $bodymail['to'] = 'user';
        $bodymail['title'] = 'Ciao ' . $new_player->nickname . ', grazie per aver prenotato un campo tramite la nostra web-app';
        $bodymail['subtitle'] = 'Ti aspettiamo il ' . $match->date_slot . ' al campo ' . $match->field . $data['dinner']['status'] ? ' e ricorda che hai prenotato la cena per ' . $date['dinner']['ospiti'] . ' persone alle ore ' . $date['dinner']['orario'] : '';
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($data['email'])->send($mail);

        $product->players()->sync($team ?? []);  
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $data
        ]);



    }
}
