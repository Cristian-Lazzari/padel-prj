<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    

    public function cancel(Request $request){
        $data = $request->all();
        
        $match = Reservation::where('id', $data['id'])->with('players')->first();
        $booking_subject = Player::where('id', $match->booking_subject)->first();
        $match->status = 0;
        $match->update();

        $contact = json_decode(Setting::where('name', 'Contatti')->first()->property, 1);
        $advanced = json_decode(Setting::where('name', 'advanced')->first()->property, 1);

        $bodymail = [
            'to' => 'user',
            'res_id' => $match->id,
            'booking_subject_id' => $booking_subject->id,
            
            'title' => 'Ci dispiace informarti che la tua prenotazione del campo ' . $match->field . 'è stata cancellata.',
            'subtitle' => '',
            
            'name' => $booking_subject->name,
            'surname' => $booking_subject->surname,
            'mail' => $booking_subject->mail,

            'date_slot' => $match->date_slot,
            'status' => $match->status,
            'team' => $match->players,

            'message' => $data['message'] ?? null,

            'field' => $match->field,
            'phone' => $booking_subject->phone,
            'admin_phone' => $contact['phone'] ?? null,

            'max_delay_default' => $advanced['max_delay_default'],
        
        ];
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($bodymail['mail'])->send($mail);

        return redirect()->route('admin.reservations.index')->with('message', 'Prenotazione modificata con successo');

    }

    public function createFromD(Request $request){
        $data = $request->all();
        //dd($data);
        $adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        if($data['type_res'] == 'multipla'){
            $times = $data['times'];
            $grouped = [];

            foreach ($times as $t) {
                [$date, $time, $field] = explode('/', $t);
                $key = $date . '|' . $field;
                $grouped[$key][] = $time;
            }
            foreach ($grouped as $key => $arr_times) {
                sort($arr_times);
                [$date, $field] = explode('|', $key);
                // Recupero configurazione campo
                $type = '-';
                $slot = 0;
                foreach ($adv['field_set'] as $fKey => $value) {
                    if ($fKey == $field) {
                        $type = $value['type'];
                        $slot = $value['m_during'];
                        break;
                    }
                }
                // Controllo intervalli
                if (!$this->checkTimeIntervals($arr_times, $slot)) {
                    dump($times);
                    dump($grouped);
                    dump($arr_times);
                    dump($slot);
                    dd('errore..');
                    return redirect()
                        ->route('admin.dashboard')
                        ->with(
                            'error',
                            'Errore: gli orari devono essere consecutivi (' . $date . ')'
                        );
                }

                // Prima ora come riferimento
                $date_slot = $date . ' ' . $arr_times[0];

                $this->create_res(
                    $field,
                    $type,
                    $date_slot,
                    $arr_times,
                    $data
                );
            }
            $m = 'Prenotazioni multiple create con successo.';
        }else{
            $times = $data['times'];
            $arr_times = [];
            sort($times);
            $type = '-';
            $slot = 0;
            $field = explode('/', $times[0])[2];
            $time = explode('/', $times[0])[1];
            $date = explode('/', $times[0])[0];
            
            foreach ($adv['field_set'] as $key => $value) {
                if($key == $field){
                    $type = $value['type'];
                    $slot = $value['m_during'];
                    break;
                }
            }
            $field_set = $adv['field_set'];
            foreach ($times as $t) {
                $tm = explode('/', $t)[1];
                if($field !== explode('/', $t)[2]){
                    return redirect()->route('admin.dashboard')->with('error', 'Errore: gli orari devono essere dello stesso campo !');
                }
                $arr_times[] = $tm;
            }
            if(!$this->checkTimeIntervals($arr_times, $slot)){
                return redirect()->route('admin.dashboard')->with('error', 'Errore: gli orari devono essere consecutivi !');
            }
            $date_slot = $date . ' ' . $time;
    
            $this->create_res($field, $type, $date_slot, $arr_times, $data); 
            $m = 'Prenotazione creata con successo per il campo ' . $field . ' il ' . $date . ' dalle ore ' . $arr_times[0] . ' alle ore ' . Carbon::createFromFormat('H:i', end($arr_times))->addMinutes($slot)->format('H:i');
        }

        return redirect()->route('admin.dashboard')->with('message', $m);
    }
    private function checkTimeIntervals(array $times, int $slot): bool
    {
        $times = array_filter($times, fn($t) => !empty($t)); // Filtra eventuali valori vuoti o non validi
        
        if (count($times) < 2) { // Se c'è meno di 2 orari, non ha senso controllare
            return true;
        }
        sort($times); // Ordina gli orari (per sicurezza)
        for ($i = 0; $i < count($times) - 1; $i++) {
            try {
                $current = Carbon::createFromFormat('H:i', trim($times[$i]));
                $next = Carbon::createFromFormat('H:i', trim($times[$i + 1]));
            } catch (\Exception $e) {
                // Formato orario non valido
                return false;
            }
            // Differenza in minuti tra orari consecutivi
            $diff = $current->diffInMinutes($next, false);
            // Se non è esattamente uguale allo slot, fallisce
            if ($diff !== $slot) {
                return false;
            }
        }
        return true;
    }

    private function create_res($field, $type, $date_slot, $arr_times, $data){
        $match = new Reservation();
        $match->date_slot = $date_slot;
        $match->duration = count($arr_times);
        $match->type = $type; // 1, 2, 3 
        $match->field = $field; // 1, 2, 3 
        $match->status = 1; // 1 confirmed, 2 cancelled, 3 noshow
        $match->dinner = json_encode([
            'status' => false,
            'guests' => false,
            'time' => false,
        ]); //[ status, guests, time] 
        $match->message = $data['message'] ?? null;
        $match->lesson = $data['lesson'] == 1 ? 1 :null;
        $match->booking_subject =  auth()->user()->playerId;
        $match->save();
        if (array_key_exists('players',$data)) {
            $match->players()->sync($data['players']);
        } 
    }



    public function index()
    {
        $reservations = Reservation::orderBy('date_slot', 'desc')->get();
        $field_set = json_decode(Setting::where('name', 'advanced')->first()->property, 1)['field_set'];
        
        foreach ($reservations as $r) {
            $player = Player::find($r->booking_subject);
            $r->booking_subject_name = $player->name ?? '';
            $r->booking_subject_surname = $player->surname ?? '';
            $r->m_during = json_decode(Setting::where('name', 'advanced')->first()->property, 1)['field_set'][$r->field]['m_during'];
        }
        return view('admin.Reservations.index', compact('reservations', 'field_set'));
    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reservation = Reservation::where('id',$id)->with('players')->first();
        $m_during = json_decode(Setting::where('name', 'advanced')->first()->property, 1)['field_set'][$reservation->field]['m_during'];
        
        
        $player = Player::find($reservation->booking_subject);
        $reservation->booking_subject_name = $player->name ?? '';
        $reservation->booking_subject_surname = $player->surname ?? '';
        return view('admin.Reservations.show', compact('reservation' ,'m_during'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $reservation = Reservation::where('id',$id)->with('players')->first();
        $players = Player::where("role", "player")->get();
        $player = Player::find($reservation->booking_subject);
        $reservation->booking_subject_name = $player->name ?? '';
        $reservation->booking_subject_surname = $player->surname ?? '';
        
        return view('admin.Reservations.edit', compact('reservation','players'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $reservation = Reservation::find($id);
        $old_dinner = json_decode($reservation->dinner, 1);

        if($old_dinner['status']){
            $old_dinner['guests'] = $data['guests'];
            $old_dinner['time'] = $data['time'];
            $reservation->dinner = json_encode($old_dinner);
        }
        $reservation->status = $data['status'];
        $reservation->message = $data['message'] ?? null;
        $reservation->update();
        if (array_key_exists('players',$data)) {
            $reservation->players()->sync($data['players']);
        } else {
            $reservation->players()->sync([]);
        }
        return redirect()->route('admin.reservations.index')->with('message', 'Prenotazione modificata con successo');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
