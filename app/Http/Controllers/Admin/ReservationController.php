<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Player;
use App\Models\Setting;
use App\Models\TournamentMatch;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    

    public function cancel(Request $request){
        $data = $request->all();
        
        $match = Reservation::where('id', $data['id'])->with('players')->first();
        $booking_subject = Player::where('id', $match->booking_subject)->first();
        $match->status = 0;
        $match->update();

        $contact = Setting::props('Contatti');
        $advanced = Setting::props('advanced');

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
        $adv = Setting::props('advanced');
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
                foreach (Setting::fieldSet() as $fKey => $value) {
                    if ($fKey == $field) {
                        $type = $value['type'];
                        $slot = $value['m_during'];
                        break;
                    }
                }
                // Controllo intervalli
                if (!$this->checkTimeIntervals($arr_times, $slot)) {
                    // dump($times);
                    // dump($grouped);
                    // dump($arr_times);
                    // dump($slot);
                    // dd('errore..');
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
            
            foreach (Setting::fieldSet() as $key => $value) {
                if($key == $field){
                    $type = $value['type'];
                    $slot = $value['m_during'];
                    break;
                }
            }
            $field_set = Setting::fieldSet();
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
        $match->lesson = $data['lesson'];
        $match->booking_subject =  auth()->user()->playerId;
        $match->save();
        if (array_key_exists('players',$data)) {
            $match->players()->sync($data['players']);
        } 
    }



    /** Quante righe per pagina si possono chiedere. La prima è quella di partenza. */
    public const PER_PAGE = [25, 50, 100, 200];

    public function index(Request $request)
    {
        // Le impostazioni e i giocatori vengono letti una volta sola:
        // prima erano interrogati dentro al ciclo, una query per riga.
        $field_set  = Setting::fieldSet();
        $dinner_off = Setting::flag('Impostazioni cena');

        $per_page = (int) $request->input('per_page');
        if (! in_array($per_page, self::PER_PAGE, true)) {
            $per_page = self::PER_PAGE[0];
        }

        $q      = trim((string) $request->input('q', ''));
        $status = in_array($request->input('status'), ['confirmed', 'cancelled'], true)
            ? $request->input('status')
            : 'all';
        $open = $request->boolean('open');
        $sort = in_array($request->input('sort'), ['slot_asc', 'created_desc', 'created_asc'], true)
            ? $request->input('sort')
            : 'slot_desc';

        // La ricerca vale anche per i numeri sui filtri: "Confermate 12" deve
        // dire dodici fra quelle trovate, non dodici in tutto l'archivio.
        $trovate = Reservation::query()->when($q !== '', function ($query) use ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('field', 'like', '%'.$q.'%')
                  ->orWhereHas('owner', function ($o) use ($q) {
                      $o->where('name', 'like', '%'.$q.'%')
                        ->orWhere('surname', 'like', '%'.$q.'%')
                        ->orWhere('nickname', 'like', '%'.$q.'%');
                  });
            });
        });

        // Un solo giro in banca dati per i tre numeri dei filtri.
        $conteggi = (clone $trovate)->selectRaw(
            'count(*) as tutte,'
            .' sum(case when status = 0 then 1 else 0 end) as annullate,'
            .' sum(case when is_open = 1 then 1 else 0 end) as aperte'
        )->first();

        $counts = [
            'tutte'      => (int) ($conteggi->tutte ?? 0),
            'annullate'  => (int) ($conteggi->annullate ?? 0),
            'aperte'     => (int) ($conteggi->aperte ?? 0),
        ];
        $counts['confermate'] = $counts['tutte'] - $counts['annullate'];

        $query = (clone $trovate)
            // I giocatori servivano solo per contarli: due withCount al posto
            // di caricare tutte le righe della tabella pivot.
            ->withCount(['players', 'acceptedPlayers'])
            ->with('owner:id,name,surname');

        if ($status === 'confirmed') {
            $query->where('status', '!=', 0);
        } elseif ($status === 'cancelled') {
            $query->where('status', 0);
        }

        if ($open) {
            $query->where('is_open', true);
        }

        // date_slot è una stringa 'Y-m-d H:i': ordinarla come testo dà lo stesso
        // ordine delle date e non impedisce l'uso dell'indice.
        match ($sort) {
            'slot_asc'     => $query->orderBy('date_slot', 'asc'),
            'created_desc' => $query->orderBy('created_at', 'desc'),
            'created_asc'  => $query->orderBy('created_at', 'asc'),
            default        => $query->orderBy('date_slot', 'desc'),
        };

        $reservations = $query->paginate($per_page)->withQueryString();

        // La durata in minuti la calcola la vista dal field_set: qui basta
        // il nome dell'intestatario, che arriva dalla relazione già caricata.
        foreach ($reservations as $r) {
            $r->booking_subject_name = $r->owner->name ?? '';
            $r->booking_subject_surname = $r->owner->surname ?? '';
        }

        return view('admin.Reservations.index', [
            'reservations'   => $reservations,
            'field_set'      => $field_set,
            'dinner_off'     => $dinner_off,
            'counts'         => $counts,
            'per_page'       => $per_page,
            'per_page_opts'  => self::PER_PAGE,
            'q'              => $q,
            'status'         => $status,
            'open'           => $open,
            'sort'           => $sort,
        ]);
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
        // Il campo potrebbe non essere più in configurazione: 30' è il valore base
        $m_during = Setting::fieldSet()[$reservation->field]['m_during'] ?? 30;
        
        
        $player = Player::find($reservation->booking_subject);
        $reservation->booking_subject_name = $player->name ?? '';
        $reservation->booking_subject_surname = $player->surname ?? '';
        $dinner_off = Setting::flag('Impostazioni cena');

        // Elenco per la select "aggiungi partecipante": esclude chi c'è già.
        $joined_ids = $reservation->players->pluck('id');
        $available_players = Player::whereNotIn('id', $joined_ids)
            ->orderBy('nickname')
            ->get(['id', 'nickname', 'name', 'surname', 'level']);

        // Se questa prenotazione è lo slot di un incontro di torneo, il dettaglio
        // deve dirlo: "partita di torneo" è un tipo, il torneo è un'altra cosa.
        $tournament_match = TournamentMatch::with('tournament')
            ->where('reservation_id', $reservation->id)
            ->first();

        return view('admin.Reservations.show', compact(
            'reservation', 'm_during', 'dinner_off', 'available_players', 'tournament_match'
        ));
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
        $dinner_off = Setting::flag('Impostazioni cena');
        return view('admin.Reservations.edit', compact('reservation','players', 'dinner_off'));
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
        $reservation->lesson = $data['lesson'];
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

    // ==========================================================
    // Gestione degli iscritti a una partita aperta
    // ==========================================================

    /**
     * Aggiunge un partecipante dalla scheda della prenotazione.
     * Il gestore può superare i posti disponibili: la validazione sui
     * posti riguarda solo le iscrizioni fatte dai clienti.
     */
    public function addParticipant(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $request->validate(['player_id' => 'required|exists:players,id']);

        $playerId = (int) $request->input('player_id');

        $existing = DB::table('player_reservation')
            ->where('reservation_id', $reservation->id)
            ->where('player_id', $playerId)
            ->first();

        if ($existing) {
            DB::table('player_reservation')
                ->where('reservation_id', $reservation->id)
                ->where('player_id', $playerId)
                ->update(['join_status' => 'accepted', 'joined_at' => now()]);
        } else {
            DB::table('player_reservation')->insert([
                'reservation_id' => $reservation->id,
                'player_id' => $playerId,
                'join_status' => 'accepted',
                'joined_at' => now(),
                'is_owner' => $playerId === (int) $reservation->booking_subject,
            ]);
        }

        return back()->with('message', 'Partecipante aggiunto alla prenotazione');
    }

    /**
     * Rimuove un partecipante. La riga viene eliminata: il gestore
     * sta correggendo l'elenco, non registrando una disdetta.
     */
    public function removeParticipant($id, $playerId)
    {
        $reservation = Reservation::findOrFail($id);

        DB::table('player_reservation')
            ->where('reservation_id', $reservation->id)
            ->where('player_id', $playerId)
            ->delete();

        return back()->with('message', 'Partecipante rimosso dalla prenotazione');
    }

    /** Toglie la prenotazione dall'elenco delle partite aperte. */
    public function closeOpen($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->is_open = false;
        $reservation->save();

        return back()->with('message', 'La partita non è più fra quelle aperte');
    }
}
