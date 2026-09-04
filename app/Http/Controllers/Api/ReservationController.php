<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use App\Services\OpenMatchService;
use App\Services\FixedSlotService;
use App\Services\FieldSchedule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    
    public function get_reservation(Request $request)
    {
        try {
            $data = $request->all();

            $booking_subject = Player::where('id', $data['user_id'])->first();
            if (! $booking_subject) {
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

            $field_set = Setting::fieldSet();
            $campo = $field_set[$field] ?? [];
            $during = (int) ($campo['m_during'] ?? 30);
            $slots = Reservation::clientSlots($during);

            $inizio = Carbon::parse($date_slot);
            $fine = $inizio->copy()->addMinutes(Reservation::CLIENT_MINUTES);

            // L'ora e mezza deve stare dentro l'orario di quel giorno
            if ($campo) {
                $finestra = FieldSchedule::window($campo, Carbon::parse($date));

                if (! $finestra) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Il campo è chiuso in questa giornata.',
                    ]);
                }

                [$apertura, $chiusura] = $finestra;

                if ($inizio->lt($apertura) || $fine->gt($chiusura)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Orario fuori dagli orari di apertura del campo.',
                    ]);
                }
            }

            $reservations = Reservation::where('date_slot', 'LIKE', '%'.$date.'%')
                ->where('field', $field)
                ->where('status', '!=', 0)
                ->select('date_slot', 'duration')
                ->get();

            // Sovrapposizione fra intervalli, non più solo "l'inizio cade dentro
            // un'altra prenotazione": con la partenza libera una richiesta alle
            // 18:00 può scavalcare una prenotazione che comincia alle 18:30.
            foreach ($reservations as $r) {
                $altroInizio = Carbon::parse($r->date_slot);
                $altroFine = $altroInizio->copy()->addMinutes($during * (int) $r->duration);

                if ($inizio->lt($altroFine) && $altroInizio->lt($fine)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Campo non disponibile, ricarica la pagina per aggiornare le disponibilità!',
                    ]);
                }
            }

            // Rete di sicurezza sui campi fissi: se un'occorrenza non è stata
            // generata (per esempio perché quel giorno il campo era occupato),
            // l'accordo vale comunque e lo slot resta riservato.
            $fixed = app(FixedSlotService::class)->conflictingSlot($field, $date_slot, $slots);

            if ($fixed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campo non disponibile: questo orario è assegnato come campo fisso.',
                ]);
            }

            $match = new Reservation;
            $match->date_slot = $date_slot;
            $match->field = $field; // 1, 2, 3
            $match->status = 1; // 1 confirmed, 2 cancelled, 3 noshow
            $match->duration = $slots; // sempre un'ora e mezza, nelle fasce del campo
            $match->type = $data['type']; // padel, basket , calcio ...
            $match->dinner = json_encode($data['dinner']); // [ status, guests, time]
            $match->message = $data['message'] ?? null;
            $match->booking_subject = $booking_subject->id;

            $match->save();
            $team = [];
            if (isset($data['players']) && count($data['players']) > 0) {
                foreach ($data['players'] as $p) {
                    $player = Player::where('nickname', $p)->first();
                    if ($player) {
                        array_push($team, $player->id);
                    }
                }
                // syncWithPivotValues: stessa sincronizzazione di prima,
                // in più valorizza i campi di iscrizione del pivot.
                $match->players()->syncWithPivotValues($team, [
                    'join_status' => 'accepted',
                    'joined_at' => now(),
                ]);
            }

            // Opzionale: la prenotazione nasce già fra le partite aperte.
            $this->publishAsOpenMatch($match, $data);
            $contact = Setting::props('Contatti');
            $bodymail = [
                'to' => 'admin',
                'res_id' => $match->id,

                'title' => $booking_subject->name.' ha appena prenotato il campo '.$match->field,
                'subtitle' => $data['dinner']['status'] ? 'Ha anche prenotato la cena per '.$data['dinner']['guests'].' persone alle ore '.$data['dinner']['time'] : 'Non ha prenotato la cena',

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
            try {
                $mailAdmin = new confermaOrdineAdmin($bodymail);
                Mail::to($contact['email'])->send($mailAdmin);

                $bodymail['to'] = 'user';
                $bodymail['title'] = 'Ciao '.$booking_subject->nickname.', grazie per aver prenotato un campo tramite la nostra web-app';
                $bodymail['subtitle'] = 'Ti aspettiamo il '.$match->date_slot.' al campo '.$match->field.($data['dinner']['status'] ? ' e ricorda che hai prenotato la cena per '.$data['dinner']['guests'].' persone alle ore '.$data['dinner']['time'] : '');
                $mail = new confermaOrdineAdmin($bodymail);
                Mail::to($bodymail['mail'])->send($mail);
            } catch (\Throwable $e) {
                Log::warning('Reservation confirmation email failed', [
                    'reservation_id' => $match->id,
                    'exception' => $e,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'ok',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Reservation booking failed', [
                'user_id' => $request->input('user_id'),
                'date_slot' => $request->input('date_slot'),
                'field' => $request->input('field'),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Non siamo riusciti a completare la prenotazione online. Attualmente non è possibile prenotare: chiama la struttura per prenotare.',
            ], 500);
        }
    }

    private function get_res($now, $field_set, $type){
        
        // date_slot è un varchar 'Y-m-d H:i': si taglia e si confronta come
        // testo, che dà lo stesso ordine delle date e gira anche su SQLite.
        $rows = DB::table('reservations')
            ->select(
                'type',
                'field',
                'duration',
                'status',
                DB::raw('substr(date_slot, 1, 10) AS day'),
                DB::raw('substr(date_slot, 12, 5) AS t')
            )
            ->where('date_slot', '>=', $now->subMinutes(180)->format('Y-m-d H:i'))
            ->where('status', '!=', 0) // 👈 controllo aggiunto
            ->where('type',  $type) // 👈 controllo aggiunto
            ->orderBy('date_slot')
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

        $adv = Setting::props('advanced');
        $field_set = Setting::fieldSet();
        $trainer_set = $adv['trainer_set'] ?? [];
        $delay_trainer = $adv['delay_trainer'] ?? 0;
        
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
        $adv = Setting::props('advanced');
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
                    if($f['type'] == $data['type'] && FieldSchedule::isOpen($f, (int) $day['dayOfWeek'])){
                        $day['fields'][$k] = $this->orariDisponibili(
                            $f,
                            $k,
                            $first_day->copy(),
                            $reserved[$day['date']][$k] ?? [],
                            $trainer_set,
                            $delay_trainer
                        );
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

    /**
     * Orari di partenza che il cliente può scegliere su un campo, in un giorno.
     *
     * Non esistono più le fasce prenotabili: si parte da qualsiasi punto della
     * griglia del campo (di norma ogni 30 minuti) purché l'ora e mezza sia
     * libera per intero e finisca entro la chiusura.
     */
    private function orariDisponibili(array $f, string $campo, Carbon $giorno, array $prenotato, $trainer_set, $delay_trainer): array
    {
        $passo = (int) $f['m_during'];
        $celle = Reservation::clientSlots($passo);

        // Apertura e chiusura sono quelle di quel giorno della settimana.
        $finestra = FieldSchedule::window($f, $giorno);

        if (! $finestra) {
            return [];
        }

        [$apertura, $chiusura] = $finestra;

        $occupato = $this->celleOccupate($prenotato, $passo);

        $orari = [];
        $cursore = $apertura->copy();

        while ($cursore->copy()->addMinutes(Reservation::CLIENT_MINUTES)->lessThanOrEqualTo($chiusura)) {
            $libero = true;

            // Tutte le celle coperte dall'ora e mezza devono essere libere e
            // fuori dall'orario di un istruttore: prima, con la partenza
            // vincolata ai 90 minuti, il caso non si poteva presentare.
            for ($i = 0; $i < $celle; $i++) {
                $cella = $cursore->copy()->addMinutes($passo * $i)->format('H:i');

                if (isset($occupato[$cella]) || $this->slotDiIstruttore($cella, $campo, $giorno, $trainer_set, $delay_trainer)) {
                    $libero = false;
                    break;
                }
            }

            if ($libero) {
                $orari[] = $cursore->format('H:i');
            }

            $cursore->addMinutes($passo);
        }

        return $orari;
    }

    /**
     * Espande le prenotazioni del giorno (che portano solo l'orario di inizio
     * e la durata) in tutte le celle che occupano.
     */
    private function celleOccupate(array $prenotato, int $passo): array
    {
        $occupato = [];

        foreach ($prenotato as $ora => $durata) {
            $inizio = Carbon::createFromTimeString($ora);

            for ($i = 0; $i < max(1, (int) $durata); $i++) {
                $occupato[$inizio->copy()->addMinutes($passo * $i)->format('H:i')] = true;
            }
        }

        return $occupato;
    }

    /** True se quella cella è riservata all'orario di un istruttore. */
    private function slotDiIstruttore(string $ora, string $campo, Carbon $giorno, $trainer_set, $delay_trainer): bool
    {
        if (! $trainer_set) {
            return false;
        }

        foreach ($trainer_set as $t) {
            if ($campo != ($t['field'] ?? null) || ! in_array($giorno->format('N'), $t['day_w'] ?? [])) {
                continue;
            }

            if (! $this->isTimeInRange($ora, $t['h_start'], $t['h_end'])) {
                continue;
            }

            // L'istruttore ha la precedenza solo fino a N ore prima: dopo,
            // lo slot torna prenotabile da chiunque.
            [$h, $m] = array_map('intval', explode(':', $ora));
            $quando = $giorno->copy()->setTime($h, $m);

            if (Carbon::now()->diffInMinutes($quando, false) > $delay_trainer * 60) {
                return true;
            }
        }

        return false;
    }

    private function isTimeInRange(string $time, string $hStart, string $hEnd): bool
    {
        $t  = strtotime($time);
        $s  = strtotime($hStart);
        $e  = strtotime($hEnd);

        return $t >= $s && $t < $e; // h_end escluso
    }

    /**
     * Pubblica la prenotazione appena creata fra le partite aperte,
     * se il frontend ha inviato il blocco "open". In assenza di quel
     * blocco il comportamento della prenotazione resta invariato.
     */
    private function publishAsOpenMatch(Reservation $match, array $data): void
    {
        $open = $data['open'] ?? null;

        if (! is_array($open) || empty($open['enabled'])) {
            return;
        }

        $validator = validator($open, OpenMatchService::rules());

        if ($validator->fails()) {
            // Una configurazione incompleta non deve far fallire la
            // prenotazione: viene salvata come prenotazione normale.
            Log::warning('Partita aperta non pubblicata: parametri non validi', [
                'reservation_id' => $match->id,
                'errors' => $validator->errors()->all(),
            ]);

            return;
        }

        try {
            app(OpenMatchService::class)->publish($match, [
                'slots_total' => $open['slots_total'],
                'category' => $open['category'] ?? null,
                'level_min' => $open['level_min'] ?? null,
                'level_max' => $open['level_max'] ?? null,
                'note' => $open['note'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Pubblicazione partita aperta fallita', [
                'reservation_id' => $match->id,
                'exception' => $e,
            ]);
        }
    }
}
