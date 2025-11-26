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
        $oldestDate = Reservation::orderBy('date_slot', 'asc')->value('date_slot');

        
        $oldestCarbon = Carbon::parse($oldestDate);
        $year = $this->get_date($oldestCarbon);

        return view('admin.dashboard', compact('year','players'));

    }
    private function get_res($now, $field_set){
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
            $field = $r->field;
            if (!isset($reserved[$day])) {
                foreach ($field_set as $k => $f) {
                    $reserved[$day][$k] = [];
                }
            }
            $reserved[$day][$field][substr($r->t, 0, 5)] = $r->duration;
        }
        ksort($reserved);

        return $reserved;
    }
    private function get_date($oldestCarbon){
   
        $now = $oldestCarbon;
        $adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        $field_set = $adv['field_set'];
        $trainer_set = $adv['trainer_set'];
       
        $reserved = $this->get_res($now, $field_set);

        $days = [];
        
        $first_day = $now;
 
        $hour_arr = [];
        $hour_arr_1 = [];
        $hour_test = Carbon::createFromTime(9, 0);
        $hour_test_1 = Carbon::createFromTime(9, 0)->addMinutes(30);
        
        $day_in_calendar = $oldestCarbon->diffInDays(Carbon::now()) + 90; // giorni da mostrare dalla prima a 90 giorni da oggi

        for ($i = 0 ; $i < $day_in_calendar; $i++) { 
            $day = [
                'date' => $first_day->format('Y-m-d'),
                'year' => $first_day->year,
                'day' => $first_day->format('j'), // 1 - 31
                'month' => $first_day->month, // 1 - 12
                'day_w' => $first_day->format('N'), // 1 = lunedì, 7 = domenica
                'fields' => array_map(fn() => [], $field_set),
                'status' => true, // libero, pieno, parziale 
                'reserved' => 0,
            ];
            if(in_array($first_day->copy()->format('Y-m-d'), $adv['day_off'])){
                $day['status'] = false;
            }

            foreach ($field_set as $k => $f) {

                if(!in_array($day['day_w'], $f['closed_days'])){
                    $start_time = Carbon::createFromTimeString($f['h_start']);
                    $hour_test = Carbon::createFromTimeString($f['h_start']);
                    $end_time   = $start_time->copy()->addMinutes(($f['m_during_client'] * $f['n_slot']));
                    // dump($start_time);
                    $hour_array_control = [];
                    for ($t = 0 ; $t < $f['n_slot']; $t++) {
                        $hour_array_control[] = $hour_test->copy()->format('H:i');
                        $hour_test->addMinutes($f['m_during_client']);
                    }
                    do {
                        $hour_f =  $start_time->copy()->format('H:i');
                        $status = 0;
                        $trainer_id = [];
                        foreach ($trainer_set as $key => $value) {
                            if(in_array($first_day->format('N'), $value['day_w']) && $k == $value['field'] ){
                                if($this->isTimeInRange($hour_f, $value['h_start'], $value['h_end'])){
                                    $status = 1;
                                    $trainer_id[] = $key;
                                }
                            }
                        }

                        $hour_null = [
                            'time' => $hour_f,
                            'status' => $status,
                            'trainer_id' => $trainer_id,
                            'id' => null,
                            'booking_subject' => null,
                            's' => in_array($hour_f, $hour_array_control) ? 1 : 0
                        ];
                        if(isset($reserved[$day['date']])) {
                            if(!isset($reserved[$day['date']][$k][$hour_f])) {
                                $day['fields'][$k]['times'][] = $hour_null;
                            }else{
                                $res = Reservation::where('date_slot', $day['date'].' '.$hour_f)->where('field', $k)->first();
                                $day['fields'][$k]['times'][] = [
                                    'time' => $hour_f,
                                    'status' => 2,
                                    'id' => $res->id ?? '',
                                    'booking_subject' => Player::where('id', $res->booking_subject?? 0)->value('nickname') ?? 'utente cancellato',
                                    'd' => $reserved[$day['date']][$k][$hour_f],
                                    's' => in_array($hour_f, $hour_array_control) ? 1 : 0
                                ];
                                $day['fields'][$k]['match'] = ($day['fields'][$k]['match'] ?? 0) + 1;
                                $day['reserved']++;
                                $start_time->addMinutes($f['m_during'] * ($reserved[$day['date']][$k][$hour_f] - 1));
                            }
                        }else{
                            $day['fields'][$k]['times'][] = $hour_null;
                        }
                        $start_time->addMinutes($f['m_during']);
                    } while ($start_time->lessThan($end_time));
                }
           
            }
            
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
         //dd($result);
        return array_values($result);
    }

    private function isTimeInRange(string $time, string $hStart, string $hEnd): bool
    {
        $t  = strtotime($time);
        $s  = strtotime($hStart);
        $e  = strtotime($hEnd);

        return $t >= $s && $t < $e; // h_end escluso
    }


}
