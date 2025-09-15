<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    
    public function get_reservation(Request $request)
    {
        $data = $request->all();

        $booking_subject = Player::where('id', $data['user_id'])->first();
        if (!$booking_subject) {
            return response()->json([
                'success' => false,
                'message' => 'Utente non trovato',
            ]);
        }

        $field = $data['field'];
        $date_slot = $data['date_slot'];
        $time = Carbon::parse($date_slot)->format('H:i');
        $date = Carbon::parse($date_slot)->format('Y-m-d');


        $reserved = $this->get_res_from_now();

        if (isset($reserved[$date])) {
            if (in_array($time, $reserved[$date][$field])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campo non disponibile, RIPROVARE',
                ]);
            }
        }



        $match = new Reservation();
        $match->date_slot = $date_slot;
        $match->field = $field; // 1, 2, 3 
        $match->status = 1; // 1 confirmed, 2 cancelled, 3 noshow
        $match->dinner = json_encode($data['dinner']); //[ status, guests, time] 
        $match->message = $data['message'] ?? null;
        $match->booking_subject = $booking_subject->id;
    
        $match->save();
        $team = [];
        if(isset($data['players']) && count($data['players']) > 0){     
            foreach ($data['players'] as $p) {
                $player = Player::where('nickname', $p)->first();
                if ($player) {
                    array_push($team, $player->id);
                }
            }
            $match->players()->sync($team ?? []);
        }
        $contact = json_decode(Setting::where('name', 'Contatti')->first()->property, 1);
        $advanced = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        $bodymail = [
            'to' => 'admin',
            'res_id' => $match->id,

            'title' =>  $booking_subject->name . ' ha appena prenotato il campo ' . $match->field . ' per il ' . $match->date_slot,
            'subtitle' => $data['dinner']['status'] ? 'Ha anche prenotato la cena per ' . $data['dinner']['guests'] . ' persone alle ore ' . $data['dinner']['time'] : 'Non ha prenotato la cena',
            
            'name' => $booking_subject->name,
            'surname' => $booking_subject->surname,
            'mail' => $booking_subject->mail,

            'date_slot' => $match->date_slot,
            'team' => $match->players,

            'message' => $data['message'] ?? null,

            'field' => $match->field,
            'phone' => $booking_subject->phone,
            'admin_phone' => $contact['phone'] ?? null,

            'max_delay_default' => $advanced['max_delay_default'],
        
        ];
        $mailAdmin = new confermaOrdineAdmin($bodymail);
        Mail::to($contact['email'])->send($mailAdmin);

        $bodymail['to'] = 'user';
        $bodymail['title'] = 'Ciao ' . $booking_subject->nickname . ', grazie per aver prenotato un campo tramite la nostra web-app';
        $bodymail['subtitle'] = 'Ti aspettiamo il ' . $match->date_slot . ' al campo ' . $match->field . ($data['dinner']['status'] ? ' e ricorda che hai prenotato la cena per ' . $data['dinner']['guests'] . ' persone alle ore ' . $data['dinner']['time'] : '');
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($bodymail['mail'])->send($mail);

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $data
        ]);



    }

    private function get_res_from_now(){
        $now_ = Carbon::now('Europe/Rome')->format('Y-m-d H:i:s');
        $rows = DB::table('reservations')
            ->select(
                'field',
                DB::raw("DATE(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i'))  AS day"),
                DB::raw("TIME(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i'))  AS t")
            )
            ->whereRaw("STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i') >= ?", [$now_])
            ->orderByRaw("DATE(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i')) ASC")
            ->orderByRaw("TIME(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i')) ASC")
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

        return $reserved;
    }
    public function get_date(){
   
        $now = Carbon::now('Europe/Rome');
        $reserved = $this->get_res_from_now();

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
