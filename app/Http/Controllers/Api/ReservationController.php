<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function get_date(){
        $now_ = Carbon::now('Europe/Rome')->format('Y-m-d H:i:s');
        $now = Carbon::now('Europe/Rome');
        $rows = DB::table('reservations')
            ->select(
                'field',
                DB::raw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'))  AS day"),
                DB::raw("TIME(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'))  AS t")
            )
            ->whereRaw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i') >= ?", [$now_])
            ->orderByRaw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
            ->orderByRaw("TIME(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
            ->get();

        $reserved = [];

        foreach ($rows as $r) {
            $day = $r->day;
            $field = (int) $r->field;

            if (!isset($reserved[$day])) {
                $reserved[$day] = [
                    1 => [],
                    2 => [],
                    3 => [],
                ];
            }

            $reserved[$day][$field][] = substr($r->t, 0, 5);
        }
        ksort($reserved);

        $days = [];
        
        $limite = $now->setTime(20, 0); 
        $first_day = $now;
        if($now->greaterThan($limite)){
            $first_day = Carbon::tomorrow();
        }
        for ($i = 0 ; $i < 7; $i++) { 
            $day = [
                'date' => $first_day->format('Y-m-d'),
                'day' => $first_day->format('j'), // 1 - 31
                'dayOfWeek' => $first_day->format('N'), // 1 = lunedì, 7 = domenica
                'fields' => [
                    'field_1' => [],
                    'field_2' => [],
                    'field_3' => [],
                ],
                'status' => true // libero, pieno, parziale
            ];
            
            
            $hour   = $first_day->setTime(9, 0)->format('H:i');
            $hour_1 = $first_day->setTime(9, 0)->addMinutes(30)->format('H:i');
            // dump('---');
            // dump($first_day->format('Y-m-d H:i'));
            for ($t = 1 ; $t < 11; $t++) {
                if(isset($reserved[$day['date']])) {
                    if(!in_array($hour, $reserved[$day['date']][1])) {
                        $day['fields']['field_1'][] = $hour;
                    }
                    if(!in_array($hour, $reserved[$day['date']][3])) {
                        $day['fields']['field_3'][] = $hour;
                    }
                    if(!in_array($hour_1, $reserved[$day['date']][2])) {
                        $day['fields']['field_2'][] = $hour_1;
                    }
                }else{
                    $day['fields']['field_1'][] = $hour;
                    $day['fields']['field_2'][] = $hour_1;
                    $day['fields']['field_3'][] = $hour;    
                }
                
                $hour   = $first_day->copy()->setTime(9, 0)->addMinutes(30 * ($t * 3))->format('H:i');
                $hour_1 = $first_day->copy()->setTime(9, 0)->addMinutes(30 * (($t * 3) + 1))->format('H:i');
                
            }
            // dump($first_day->format('Y-m-d H:i'));
            // dump('---');
            $days[] = $day;
            
            $first_day->addDay();
        }
        // dd($days);
        return response()->json([
            'success' => true,
            'data' => $days
        ]);
    }

}
