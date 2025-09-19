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
            
            'title' => 'Ci dispiace informarti che la tua prenotazione del campo ' . $match->field,
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
        $times = $data['times'];
        $arr_times = [];
        sort($times);
        $field = explode('/', $times[0])[2];
        $time = explode('/', $times[0])[1];
        $date = explode('/', $times[0])[0];
        foreach ($times as $t) {
            $tm = explode('/', $t)[1];
            if($field !== explode('/', $t)[2]){
                return redirect()->route('admin.dashboard')->with('error', 'Errore: gli orari devono essere dello stesso campo !');
            }
            $arr_times[] = $tm;
        }
        if(!$this->checkHalfHourIntervals($arr_times)){
            return redirect()->route('admin.dashboard')->with('error', 'Errore: gli orari devono essere distanziati da mezzora !');
        }
        $date_slot = $date . ' ' . $time;

        $match = new Reservation();
        $match->date_slot = $date_slot;
        $match->duration = count($arr_times);
        $match->field = explode('_', $field)[1]; // 1, 2, 3 
        $match->status = 1; // 1 confirmed, 2 cancelled, 3 noshow
        $match->dinner = json_encode([
            'status' => false,
            'guests' => false,
            'time' => false,
        ]); //[ status, guests, time] 
        $match->message = $data['message'] ?? null;
        // /dd(auth()->user()->playerId);
        $match->booking_subject =  auth()->user()->playerId;
        $match->save();
        if (array_key_exists('players',$data)) {
            $match->players()->sync($data['players']);
        } else {
            $match->players()->sync([]);
        }

        return redirect()->route('admin.dashboard')->with('message', 'Prenotazione modificata con successo');
    }
    private function checkHalfHourIntervals(array $times): bool
    {
        // Ordino l'array per sicurezza
        sort($times);
        for ($i = 0; $i < count($times) - 1; $i++) {
            $current = Carbon::createFromFormat('H:i', $times[$i]);
            $next = Carbon::createFromFormat('H:i', $times[$i + 1]);
            if ($current->diffInMinutes($next) !== 30) {
                return false;
            }
        }
        return true;
    }


    public function index()
    {
        $reservations = Reservation::orderBy('date_slot', 'desc')->get();
        foreach ($reservations as $r) {
            $player = Player::find($r->booking_subject);
            $r->booking_subject_name = $player->name ?? '';
            $r->booking_subject_surname = $player->surname ?? '';
        }
        return view('admin.Reservations.index', compact('reservations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
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
        //
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
        
        $player = Player::find($reservation->booking_subject);
        $reservation->booking_subject_name = $player->name ?? '';
        $reservation->booking_subject_surnname = $player->surname ?? '';
        return view('admin.Reservations.show', compact('reservation'));
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
        $players = Player::all();
        $player = Player::find($reservation->booking_subject);
        $reservation->booking_subject_name = $player->name ?? '';
        $reservation->booking_subject_surnname = $player->surname ?? '';
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
