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
        $match->duration = 3; 
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

            'title' =>  $booking_subject->name . ' ha appena prenotato il campo ' . $match->field,
            'subtitle' => $data['dinner']['status'] ? 'Ha anche prenotato la cena per ' . $data['dinner']['guests'] . ' persone alle ore ' . $data['dinner']['time'] : 'Non ha prenotato la cena',
            
            'name' => $booking_subject->name,
            'surname' => $booking_subject->surname,
            'mail' => $booking_subject->mail,

            'date_slot' => $match->date_slot,
            'team' => $match->players,
            'status' => $match->status,

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
                'duration',
                DB::raw("DATE(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i'))  AS day"),
                DB::raw("TIME(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i'))  AS t")
            )
            ->whereRaw("STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i') >= ?", [$now_])
            ->where('status', '!=', 0) // 👈 controllo aggiunto
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
        //array di controllo
        $hour_arr = [];
        $hour_arr_1 = [];
        $hour_test = Carbon::createFromTime(9, 0);
        $hour_test_1 = Carbon::createFromTime(9, 0)->addMinutes(30);
        for ($t = 1 ; $t < 11; $t++) {
            $hour_arr[] = $hour_test->copy()->format('H:i');
            $hour_arr_1[] = $hour_test_1->copy()->format('H:i');
            $hour_test->addMinutes(90);
            $hour_test_1->addMinutes(90);
        }
        //---
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
            
                       
            $end_1 = Carbon::createFromTime(23, 0); // 08:00
            $end_2 = Carbon::createFromTime(23, 0)->addMinutes(30); // 12:00
            $hour_1   = Carbon::createFromTime(9, 0);
            $hour_2   = Carbon::createFromTime(9, 0)->addMinutes(30);
            $hour_3   = Carbon::createFromTime(9, 0);
            
            do {
                $hour_f =  $hour_1->copy()->format('H:i'); //in_array($hour_f, $hour_arr_1) ? 1 : 0

                if(isset($reserved[$day['date']])) {
                    if(!isset($reserved[$day['date']][1][$hour_f]) &&
                    !isset($reserved[$day['date']][1][$hour_1->copy()->addMinutes(30)->format('H:i')]) &&
                    !isset($reserved[$day['date']][1][$hour_1->copy()->addMinutes(60)->format('H:i')]) &&
                    !isset($reserved[$day['date']][1][$hour_1->copy()->subMinutes(30)->format('H:i')]) &&
                    !isset($reserved[$day['date']][1][$hour_1->copy()->subMinutes(60)->format('H:i')])
                    ) {
                    //if(!isset($reserved[$day['date']][1][$hour_f])) {
                        $day['fields']['field_1'][] = $hour_f;
                    }
                }else{
                    $day['fields']['field_1'][] = $hour_f;
                }
                $hour_1->addMinutes(90);
            } while ($hour_1->lessThan($end_1));
            do {
                $hour_f =  $hour_2->copy()->format('H:i'); //in_array($hour_f, $hour_arr_1) ? 1 : 0

                if(isset($reserved[$day['date']])) {
                    if(!isset($reserved[$day['date']][1][$hour_f]) &&
                    !isset($reserved[$day['date']][1][$hour_2->copy()->addMinutes(30)->format('H:i')]) &&
                    !isset($reserved[$day['date']][1][$hour_2->copy()->addMinutes(60)->format('H:i')]) &&
                    !isset($reserved[$day['date']][1][$hour_2->copy()->subMinutes(30)->format('H:i')]) &&
                    !isset($reserved[$day['date']][1][$hour_2->copy()->subMinutes(60)->format('H:i')])
                    ) {
                    //if(!isset($reserved[$day['date']][1][$hour_f])) {
                        $day['fields']['field_2'][] = $hour_f;
                    }
                }else{
                    $day['fields']['field_2'][] = $hour_f;
                }
                $hour_2->addMinutes(90);
            } while ($hour_2->lessThan($end_2));
            do {
                $hour_f =  $hour_3->copy()->format('H:i'); //in_array($hour_f, $hour_arr_1) ? 1 : 0

                if(isset($reserved[$day['date']])) {
                    if(!isset($reserved[$day['date']][1][$hour_f]) &&
                    !isset($reserved[$day['date']][1][$hour_3->copy()->addMinutes(30)->format('H:i')]) &&
                    !isset($reserved[$day['date']][1][$hour_3->copy()->addMinutes(60)->format('H:i')]) &&
                    !isset($reserved[$day['date']][1][$hour_3->copy()->subMinutes(30)->format('H:i')]) &&
                    !isset($reserved[$day['date']][1][$hour_3->copy()->subMinutes(60)->format('H:i')])
                    ) {
                    //if(!isset($reserved[$day['date']][1][$hour_f])) {
                        $day['fields']['field_3'][] = $hour_f;
                    }
                }else{
                    $day['fields']['field_3'][] = $hour_f;
                }
                $hour_3->addMinutes(30);
            } while ($hour_3->lessThan($end_1));

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
