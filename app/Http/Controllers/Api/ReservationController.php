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
        
        $now = Carbon::now('Europe/Rome');
        
        $adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        $field_set = $adv['field_set'];
        
        $reserved = $this->get_res($now->addMinutes(30), $field_set, $data['type']);
        
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
        $match->type = $data['type']; //padel, basket , calcio ...
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
            'booking_subject_id' => $booking_subject->id,

            'field' => $match->field,
            'phone' => $booking_subject->phone,
            'admin_phone' => $contact['phone'] ?? null,
            'max_delay_default' => $adv['max_delay_default'],

        
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

    private function get_res($now, $field_set, $type){
        
        $rows = DB::table('reservations')
            ->select(
                'type',
                'field',
                'duration',
                'status',
                DB::raw("DATE(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i'))  AS day"),
                DB::raw("TIME(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i'))  AS t")
            )
            ->whereRaw("STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i') >= ?", [$now->subMinutes(180)])
            ->where('status', '!=', 0) // 👈 controllo aggiunto
            ->where('type',  $type) // 👈 controllo aggiunto
            ->orderByRaw("DATE(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i')) ASC")
            ->orderByRaw("TIME(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i')) ASC")
            ->get();

        $reserved = [];

        foreach ($rows as $r) {
            if($r->status == 1 || $r->status == '1'){
                $day = $r->day;
                $field = $r->field;

                if (!isset($reserved[$day])) {
                    foreach ($field_set as $k => $f) {
                        $reserved[$day][$k] = [];
                    }
                }
                $reserved[$day][$field][substr($r->t, 0, 5)] = $r->duration;
            }
        }
        ksort($reserved);


        return $reserved;
    }
    public function get_date(Request $request){
        $data = $request->all();
        $now = Carbon::now('Europe/Rome');
        $dalay_from_res = 30;

        $adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        $field_set = $adv['field_set'];
        $trainer_set = $adv['trainer_set'];
        $delay_trainer = $adv['delay_trainer'];
        
        $reserved = $this->get_res($now->addMinutes($dalay_from_res), $field_set, $data['type']);
        //return $reserved;

        $days = [];
        
        $limite = $now->setTime(20, 0); // massimo orario per prenotare in giornata 20:00
        $first_day = $now;
        if($now->greaterThan($limite)){
            $first_day = Carbon::tomorrow();
        }
        
        $field_arr = [];
        foreach ($field_set as $k => $f) {
            if($f['type'] == $data['type']){
                $field_arr[$k] = [];
            }
        }
        
        $day_in_calendar = 7;
        $adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        $ddd = [];
        for ($i = 0 ; $i < $day_in_calendar; $i++) { 
            $day = [
                'date' => $first_day->format('Y-m-d'),
                'day' => $first_day->format('j'), // 1 - 31
                'dayOfWeek' => $first_day->format('N'), // 1 = lunedì, 7 = domenica
                'fields' => $field_arr,
                'status' => true // libero, pieno, parziale
            ];
            if(!in_array($first_day->copy()->format('Y-m-d'), $adv['day_off'])){       
                foreach ($field_set as $k => $f) {
                    if($f['type'] == $data['type'] && !in_array($day['dayOfWeek'], $f['closed_days'])){
                        $start_time = Carbon::createFromTimeString($f['h_start']);
                        $end_time   = $start_time->copy()->addMinutes(($f['m_during_client'] * $f['n_slot']));
                        
                        $hour_test = Carbon::createFromTimeString($f['h_start']);
                        $hour_array_control = [];
                        for ($t = 0 ; $t < $f['n_slot']; $t++) {
                            $hour_array_control[] = $hour_test->copy()->format('H:i');
                            $hour_test->addMinutes($f['m_during_client']);
                        }
                        do {
                            $status = 0;
                            $hour_f = $start_time->copy()->format('H:i');
                            

                            if ($trainer_set !== null) {
                                foreach ($trainer_set as $key => $value) {
                                    if(in_array($first_day->format('N'), $value['day_w']) && $k == $value['field'] ){
                                        if($this->isTimeInRange($hour_f, $value['h_start'], $value['h_end'])){
                                            $anno  = $first_day->year;
                                            $mese  = $first_day->month;
                                            $giorno = $first_day->day;

                                            // 2. Estraggo ora e minuti dall'orario corretto
                                            $ora   = $start_time->hour;
                                            $minuti = $start_time->minute;

                                            // 3. Creo la data corretta
                                            $dataCorretta = Carbon::create($anno, $mese, $giorno, $ora, $minuti);
                                            if($dataCorretta->diffInHours(Carbon::now(), false) > $delay_trainer){
                                                $status = 1;
                                            }
                                        }
                                    }
                                }
                            }

                            if($status == 0){

                               
                                //da fixare dimanica per ogni durata minima di ogni campo
                                if(isset($reserved[$day['date']])) {
                                    if(!isset($reserved[$day['date']][$k][$hour_f])) {
                                        if(in_array($hour_f, $hour_array_control)){
                                            if( !isset($reserved[$day['date']][$k][$start_time->copy()->addMinutes($f['m_during'])->format('H:i')]) &&
                                                !isset($reserved[$day['date']][$k][$start_time->copy()->addMinutes($f['m_during'] * 2)->format('H:i')]))
                                            {
                                                
                                                $day['fields'][$k][] = $hour_f;
                                            }
                                        }
                                    }else{

                                        $start_time->addMinutes($f['m_during'] * ($reserved[$day['date']][$k][$hour_f]- 1) );
                                    }
                                }else {
                                    if( in_array($hour_f, $hour_array_control)){
                                        if( !isset($reserved[$day['date']][$k][$start_time->copy()->addMinutes($f['m_during'])->format('H:i')]) &&
                                            !isset($reserved[$day['date']][$k][$start_time->copy()->addMinutes($f['m_during'] * 2)->format('H:i')]))
                                        {
                                            $day['fields'][$k][] = $hour_f;
                                        }
                                    }
                                }
                            }
                            $start_time->addMinutes($f['m_during']);
                        } while ($start_time->lessThan($end_time));
                    }
               
                }
                $days[] = $day;    
            }

            
            $first_day->addDay();
        }

        // dd($days);
        return response()->json([
            'success' => true,
            'data' => $days,
            'dd' => $ddd,
            'reserved' => $reserved
        ]);
    }

    private function isTimeInRange(string $time, string $hStart, string $hEnd): bool
    {
        $t  = strtotime($time);
        $s  = strtotime($hStart);
        $e  = strtotime($hEnd);

        return $t >= $s && $t < $e; // h_end escluso
    }

}
