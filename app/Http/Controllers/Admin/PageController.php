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

        $players = Player::where("role", "player")->get();
        $reservations   = Reservation::all();
        $calendar = [];
        
        $oldestDate = Reservation::orderBy('date_slot', 'asc')->value('date_slot');
        
        $oldestCarbon = Carbon::parse($oldestDate);
        $year = $this->get_date($oldestCarbon);

        return view('admin.dashboard', compact('year','players'));

    }
    private function get_res($now){
        $rows = DB::table('reservations')
            ->select(
                'field',
                'duration',
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
            $reserved[$day][$field][substr($r->t, 0, 5)] = $r->duration;
        }
        ksort($reserved);
        //dd($reserved);

        return $reserved;
    }
    private function get_date($oldestCarbon){
   
        $now = $oldestCarbon;
        $reserved = $this->get_res($now);

        $days = [];
        
        $first_day = $now;

        $hour_arr = [];
        $hour_arr_1 = [];
        $hour_test = Carbon::createFromTime(9, 0);
        $hour_test_1 = Carbon::createFromTime(9, 0)->addMinutes(30);

        $slot_on_day = 11;
        $minutes_on_slot = 90;

        for ($t = 1 ; $t < $slot_on_day; $t++) {
            $hour_arr[] = $hour_test->copy()->format('H:i');
            $hour_arr_1[] = $hour_test_1->copy()->format('H:i');
            $hour_test->addMinutes($minutes_on_slot);
            $hour_test_1->addMinutes($minutes_on_slot);
        }

        $adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        
        $day_in_calendar = $oldestCarbon->diffInDays(Carbon::now()) + 90; // giorni da mostrare dalla prima a 90 giorni da oggi
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
            if(in_array($first_day->copy()->format('Y-m-d'), $adv['day_off'])){
                $day['status'] = false;
            }
            $end_1 = Carbon::createFromTime(23, 0); // 08:00
            $end_2 = Carbon::createFromTime(23, 0)->addMinutes(30); // 12:00
            
            $hour_1   = Carbon::createFromTime(9, 0);
            $hour_2   = Carbon::createFromTime(9, 0)->addMinutes(30);
            $hour_3   = Carbon::createFromTime(9, 0);
            do {
                $hour_f =  $hour_1->copy()->format('H:i');

                $hour_null = [
                    'time' => $hour_f,
                    's' => in_array($hour_f, $hour_arr) ? 1 : 0,
                    'status' => 0,
                    'id' => null,
                    'booking_subject' => null,
                ];
                //dd(!isset($reserved[$day['date']][1][$hour_f]));
                if(isset($reserved[$day['date']])) {
                    
                    
                    if(!isset($reserved[$day['date']][1][$hour_f])) {
                        $day['fields']['field_1'][] = $hour_null;
                    }else{
                        $res = Reservation::where('date_slot', $day['date'].' '.$hour_f)->where('field', 1)->first();
                        $day['fields']['field_1'][] = [
                            'time' => $hour_f,
                            'status' => 1,
                            's' => in_array($hour_f, $hour_arr) ? 1 : 0,
                            'id' => $res->id ?? '',
                            'd' => $reserved[$day['date']][1][$hour_f],
                            'booking_subject' => Player::where('id', $res->booking_subject)->value('nickname') ?? 'utente cancellato',
                            
                        ];
                        $day['reserved']++;
                        $hour_1->addMinutes(30 * ($reserved[$day['date']][1][$hour_f] - 1));
                        //dd('evvai');
                        
                    }
                }else{
                    $day['fields']['field_1'][] = $hour_null;
                }
                $hour_1->addMinutes(30);
                // dump($hour_1);
                // dump($end_1);
            } while ($hour_1->lessThan($end_1));
    
            do {
                $hour_f =  $hour_2->copy()->format('H:i');
                $hour_null = [
                    'time' => $hour_f,
                    'status' => 0,
                    'id' => null,
                    'booking_subject' => null,
                    's' => in_array($hour_f, $hour_arr_1) ? 1 : 0
                ];
                if(isset($reserved[$day['date']])) {
                    if(!isset($reserved[$day['date']][2][$hour_f])) {
                        $day['fields']['field_2'][] = $hour_null;
                    }else{
                        $res = Reservation::where('date_slot', $day['date'].' '.$hour_f)->where('field', 2)->first();
                        $day['fields']['field_2'][] = [
                            'time' => $hour_f,
                            'status' => 1,
                            'id' => $res->id ?? '',
                            'booking_subject' => Player::where('id', $res->booking_subject)->value('nickname') ?? 'utente cancellato',
                            'd' => $reserved[$day['date']][2][$hour_f],
                            's' => in_array($hour_f, $hour_arr_1) ? 1 : 0
                        ];
                        $day['reserved']++;
                        $hour_2->addMinutes(30 * ($reserved[$day['date']][2][$hour_f] - 1));
                    }
                }else{
                    $day['fields']['field_2'][] = $hour_null;
                }
                $hour_2->addMinutes(30);
            } while ($hour_2->lessThan($end_2));

            do {
                $hour_f =  $hour_3->copy()->format('H:i');
                $hour_null = [
                    'time' => $hour_f,
                    'status' => 0,
                    'id' => null,
                    'booking_subject' => null,
                    's' => in_array($hour_f, $hour_arr) ? 1 : 0
                ];
                if(isset($reserved[$day['date']])) {
                    if(!isset($reserved[$day['date']][3][$hour_f])) {
                        $day['fields']['field_3'][] = $hour_null;
                    }else{
                        $res = Reservation::where('date_slot', $day['date'].' '.$hour_f)->where('field', 3)->first();
                        $day['fields']['field_3'][] = [
                            'time' => $hour_f,
                            'status' => 1,
                            'id' => $res->id ?? '',
                            'd' => $reserved[$day['date']][3][$hour_f],
                            's' => in_array($hour_f, $hour_arr) ? 1 : 0,
                            'booking_subject' => Player::where('id', $res->booking_subject)->value('nickname') ?? 'utente cancellato',
                        ];
                        $day['reserved']++;
                        $hour_3->addMinutes(30 * ($reserved[$day['date']][3][$hour_f] - 1));
                    }
                }else{
                    $day['fields']['field_3'][] = $hour_null;
                }
                $hour_3->addMinutes(30);
            } while ($hour_3->lessThan($end_1));
            
            
            
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
        //dd('ciao');

        // se vuoi che sia un array con indici consecutivi
        // dd($result);
        return array_values($result);
    }


}
