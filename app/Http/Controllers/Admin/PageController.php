<?php



namespace App\Http\Controllers\Admin;


use Carbon\Carbon;

use App\Models\User;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{

    public function admin() { //calendar


        $reservations   = Reservation::all();
        $calendar = [];
        
        $oldestDate = Reservation::orderBy('date_slot', 'asc')->value('date_slot');
        
        $oldestCarbon = Carbon::parse($oldestDate);
        $year = $this->get_date($oldestCarbon);

        return view('admin.dashboard', compact('year'));

    }
    private function get_res($now){
        $rows = DB::table('reservations')
            ->select(
                'field',
                DB::raw("DATE(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i'))  AS day"),
                DB::raw("TIME(STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i'))  AS t")
            )
            ->whereRaw("STR_TO_DATE(date_slot, '%Y-%m-%d %H:%i') >= ?", [$now])
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
    private function get_date($oldestCarbon){
   
        $now = $oldestCarbon;
        $reserved = $this->get_res($now);

        $days = [];
        
        $limite = $now->setTime(20, 0); 
        $first_day = $now;
        if($now->greaterThan($limite)){
            $first_day = Carbon::tomorrow();
        }

        $day_in_calendar = 180; // giorni da mostrare

        for ($i = 0 ; $i < $day_in_calendar; $i++) { 
            $day = [
                'date' => $first_day->format('Y-m-d'),
                'year' => $first_day->year,
                'day' => $first_day->format('j'), // 1 - 31
                'month' => $first_day->month, // 1 - 12
                'day_w' => $first_day->format('N'), // 1 = lunedì, 7 = domenica
                'fields' => [
                    'field_1' => [],
                    'field_2' => [],
                    'field_3' => [],
                ],
                'status' => true, // libero, pieno, parziale 
                'reserved' => 0,
            ];
            
            
            $hour   = $first_day->setTime(9, 0)->format('H:i');
            $hour_1 = $first_day->setTime(9, 0)->addMinutes(30)->format('H:i');
         
            for ($t = 1 ; $t < 11; $t++) {
                if(isset($reserved[$day['date']])) {
                    if(!in_array($hour, $reserved[$day['date']][1])) {
                        $day['fields']['field_1'][] = [
                            'time' => $hour,
                            'status' => 0,
                            'id' => null,
                            'booking_subject' => null
                        ];
                    }else{
                        $res = Reservation::where('date_slot', $day['date'].' '.$hour)->where('field', 1)->first();
                        $day['fields']['field_1'][] = [
                            'time' => $hour,
                            'status' => 1,
                            'id' => $res->id,
                            'booking_subject' => Player::where('id', $res->booking_subject)->value('nickname')
                        ];
                        $day['reserved']++;
                    }
                    if(!in_array($hour, $reserved[$day['date']][3])) {
                        $day['fields']['field_3'][] = [
                            'time' => $hour,
                            'status' => 0,
                            'id' => null,
                            'booking_subject' => null
                        ];
                    }else{
                        $res = Reservation::where('date_slot', $day['date'].' '.$hour)->where('field', 3)->first();
                        $day['fields']['field_3'][] = [
                            'time' => $hour,
                            'status' => 1,
                            'id' => $res->id,
                            'booking_subject' => Player::where('id', $res->booking_subject)->value('nickname')
                        ];
                        $day['reserved']++;
                    }
                    if(!in_array($hour_1, $reserved[$day['date']][2])) {
                        $day['fields']['field_2'][] = [
                            'time' => $hour_1,
                            'status' => 0,
                            'id' => null,
                            'booking_subject' => null
                        ];
                    }else{
                        $res = Reservation::where('date_slot', $day['date'].' '.$hour_1)->where('field', 2)->first();
                        $day['fields']['field_2'][] = [
                            'time' => $hour_1,
                            'status' => 1,
                            'id' => $res->id,
                            'booking_subject' => Player::where('id', $res->booking_subject)->value('nickname')
                        ];
                        $day['reserved']++;
                    }
                }else{
                    $day['fields']['field_1'][] = [
                        'time' => $hour,
                        'status' => 0,
                        'id' => null,
                        'booking_subject' => null
                    ];
                    $day['fields']['field_2'][] = [
                        'time' => $hour_1,
                        'status' => 0,
                        'id' => null,
                        'booking_subject' => null
                    ];
                    $day['fields']['field_3'][] = [
                        'time' => $hour,
                        'status' => 0,
                        'id' => null,
                        'booking_subject' => null
                    ];    
                }
                
                $hour   = $first_day->copy()->setTime(9, 0)->addMinutes(30 * ($t))->format('H:i');
                $hour_1 = $first_day->copy()->setTime(9, 0)->addMinutes(30 * (($t) + 1))->format('H:i');
                // $hour   = $first_day->copy()->setTime(9, 0)->addMinutes(30 * ($t * 3))->format('H:i');
                // $hour_1 = $first_day->copy()->setTime(9, 0)->addMinutes(30 * (($t * 3) + 1))->format('H:i');
                
            }
            // dump($first_day->format('Y-m-d H:i'));
            // dump('---');
            $days[] = $day;
            
            $first_day->addDay();
        }
            $result = [];

        foreach ($days as $day) {
            $monthNumber = $day['month'];
            $year = $day['year'];

            // se il mese non esiste ancora, inizializzalo
            if (!isset($result[$monthNumber])) {
                $result[$monthNumber] = [
                    'year' => $year,
                    'month' => $monthNumber,
                    'days' => []
                ];
            }

            // aggiungi il giorno dentro il mese corrispondente
            $result[$monthNumber]['days'][] = $day;
        }

        // se vuoi che sia un array con indici consecutivi
        return array_values($result);
    }


}

