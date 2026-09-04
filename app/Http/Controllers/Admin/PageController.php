<?php



namespace App\Http\Controllers\Admin;


use Carbon\Carbon;

use App\Models\User;
use App\Models\Player;
use App\Models\Setting;
use App\Services\FieldSchedule;
use App\Models\Reservation;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    /**
     * Quanti mesi in avanti si può navigare oltre quello corrente. Prima il
     * calendario costruiva in un colpo solo tutti i giorni dalla prima
     * prenotazione a +180 giorni: centinaia di giornate, ognuna con le sue
     * fasce e con una query per ogni fascia occupata. Ora si costruisce un
     * mese alla volta e gli altri arrivano in ajax.
     */
    private const MONTHS_AHEAD = 6;

    public function admin(Request $request) { //calendar

        $players = Player::where("role", "player")
            ->orderBy('nickname')
            ->get(['id', 'nickname', 'name', 'surname', 'level']);

        [$minKey, $maxKey] = $this->calendarBounds();

        // Mese da aprire: quello chiesto se valido, altrimenti quello corrente
        // riportato dentro ai limiti navigabili.
        $cursor = $this->resolveMonth($request->input('year'), $request->input('month'), $minKey, $maxKey);

        $m = $this->buildMonth($cursor->year, $cursor->month);

        // I tornei veri e propri: occupano giorni interi e campi, e nel calendario
        // stanno sopra le fasce orarie, non dentro (le partite di torneo restano
        // prenotazioni con lesson = 2).
        $tournaments = Tournament::where('status', '!=', 'cancelled')
            ->withCount(['confirmedRegistrations', 'waitlistRegistrations'])
            ->orderBy('starts_at')
            ->get();

        $months           = $this->monthOptions($minKey, $maxKey);
        $dinner_off       = Setting::flag('Impostazioni cena');
        $day_off          = array_values(Setting::props('advanced')['day_off'] ?? []);
        $tourneiPerGiorno = $this->tournamentsByDay($tournaments);

        return view('admin.dashboard', compact(
            'm', 'months', 'players', 'tournaments', 'tourneiPerGiorno', 'dinner_off', 'day_off'
        ) + $this->navFlags($cursor, $minKey, $maxKey));
    }

    /**
     * Un solo mese, chiesto dalle frecce o dal selettore: risponde con i due
     * frammenti di calendario già disegnati, così la pagina resta ferma e si
     * ricarica soltanto la griglia.
     */
    public function month(Request $request)
    {
        [$minKey, $maxKey] = $this->calendarBounds();

        $cursor = $this->resolveMonth($request->input('year'), $request->input('month'), $minKey, $maxKey);

        $m = $this->buildMonth($cursor->year, $cursor->month);

        $tourneiPerGiorno = $this->tournamentsByDay(
            Tournament::where('status', '!=', 'cancelled')
                ->withCount(['confirmedRegistrations', 'waitlistRegistrations'])
                ->orderBy('starts_at')
                ->get()
        );

        return response()->json([
            'year'   => $cursor->year,
            'month'  => $cursor->month,
            'label'  => self::MESI[$cursor->month].' '.$cursor->year,
            'grid'   => view('admin.partials.cal-month', [
                'm' => $m,
                'tourneiPerGiorno' => $tourneiPerGiorno,
                'dinner_off' => Setting::flag('Impostazioni cena'),
            ])->render(),
            'offGrid' => view('admin.partials.cal-month-off', ['m' => $m])->render(),
        ] + $this->navFlags($cursor, $minKey, $maxKey));
    }

    public const MESI = [
        1 => 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno',
        'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre',
    ];

    /**
     * Mappa "giorno => tornei di quel giorno": la usano sia la banda sotto al
     * calendario sia il segno sul riquadro del giorno.
     */
    public function tournamentsByDay($tournaments): array
    {
        $perGiorno = [];

        foreach ($tournaments as $t) {
            $dati = [
                'nome'      => $t->name,
                'url'       => route('admin.tournaments.show', $t),
                'stato'     => $t->statusLabel(),
                'statoKey'  => $t->status,
                'campi'     => $t->fieldsLabel(),
                'ora'       => $t->starts_at?->format('H:i'),
                'formula'   => $t->formatLabel(),
                'iscritte'  => (int) $t->confirmed_registrations_count,
                'posti'     => (int) $t->teams_max,
                'attesa'    => (int) $t->waitlist_registrations_count,
                'aperto'    => $t->registration_open,
                'chiusura'  => $t->registration_closes_at?->format('d/m H:i'),
            ];

            foreach ($t->occupiedDays() as $giorno) {
                $perGiorno[$giorno][] = $dati;
            }
        }

        return $perGiorno;
    }

    /**
     * Primo e ultimo mese navigabili, come chiavi 'Y-m'. Si parte dalla prima
     * prenotazione in archivio (se c'è) e si arriva a qualche mese avanti.
     */
    private function calendarBounds(): array
    {
        $oldest = Reservation::min('date_slot');

        $min = $oldest
            ? Carbon::parse(substr($oldest, 0, 10))->startOfMonth()
            : Carbon::now()->startOfMonth();

        $max = Carbon::now()->startOfMonth()->addMonths(self::MONTHS_AHEAD);

        if ($min->greaterThan($max)) {
            $min = Carbon::now()->startOfMonth();
        }

        return [$min->format('Y-m'), $max->format('Y-m')];
    }

    /** Il mese chiesto, ripulito e tenuto dentro ai limiti. */
    private function resolveMonth($year, $month, string $minKey, string $maxKey): Carbon
    {
        $year  = (int) $year;
        $month = (int) $month;

        $cursor = ($year >= 2000 && $year <= 2100 && $month >= 1 && $month <= 12)
            ? Carbon::create($year, $month, 1)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $key = $cursor->format('Y-m');

        if ($key < $minKey) {
            return Carbon::createFromFormat('Y-m-d', $minKey.'-01')->startOfMonth();
        }

        if ($key > $maxKey) {
            return Carbon::createFromFormat('Y-m-d', $maxKey.'-01')->startOfMonth();
        }

        return $cursor;
    }

    /** Stato delle frecce e coordinate del mese prima e dopo. */
    private function navFlags(Carbon $cursor, string $minKey, string $maxKey): array
    {
        $prev = $cursor->copy()->subMonth();
        $next = $cursor->copy()->addMonth();

        return [
            'has_prev'  => $prev->format('Y-m') >= $minKey,
            'has_next'  => $next->format('Y-m') <= $maxKey,
            'prev_year' => $prev->year,  'prev_month' => $prev->month,
            'next_year' => $next->year,  'next_month' => $next->month,
        ];
    }

    /** Le voci del selettore: un'etichetta per ogni mese navigabile. */
    private function monthOptions(string $minKey, string $maxKey): array
    {
        $cursor = Carbon::createFromFormat('Y-m-d', $minKey.'-01')->startOfMonth();
        $end    = Carbon::createFromFormat('Y-m-d', $maxKey.'-01')->startOfMonth();

        $out = [];

        while ($cursor->lessThanOrEqualTo($end)) {
            $out[] = [
                'year'  => $cursor->year,
                'month' => $cursor->month,
                'key'   => $cursor->format('Y-m'),
                'label' => self::MESI[$cursor->month].' '.$cursor->year,
            ];
            $cursor->addMonth();
        }

        return $out;
    }

    /**
     * Le prenotazioni di un intervallo di giorni, indicizzate per giorno,
     * campo e ora: una sola query al posto della vecchia coppia
     * "elenco + una SELECT per ogni fascia occupata".
     */
    private function reservationsInRange(string $from, string $to): array
    {
        // date_slot è una stringa 'Y-m-d H:i': il confronto lessicografico dà
        // lo stesso ordine di quello sulle date e, a differenza di STR_TO_DATE,
        // può usare l'indice.
        $rows = DB::table('reservations as r')
            ->leftJoin('players as p', 'p.id', '=', 'r.booking_subject')
            ->select(
                'r.id', 'r.field', 'r.duration', 'r.date_slot', 'r.lesson',
                'r.dinner', 'r.fixed_slot_id', 'r.booking_subject',
                'p.nickname as owner_nickname'
            )
            ->where('r.status', '!=', 0)
            ->where('r.date_slot', '>=', $from)
            ->where('r.date_slot', '<', $to)
            ->orderBy('r.date_slot')
            ->get();

        $map = [];

        foreach ($rows as $r) {
            $day  = substr($r->date_slot, 0, 10);
            $time = substr($r->date_slot, 11, 5);

            // Se due prenotazioni si accavallano sullo stesso campo e ora vince
            // la prima, come faceva il vecchio ->first().
            if (isset($map[$day][$r->field][$time])) {
                continue;
            }

            $dinner = $r->dinner ? json_decode($r->dinner, true) : null;

            $map[$day][$r->field][$time] = [
                'id'       => $r->id,
                'duration' => (int) $r->duration,
                'lesson'   => (int) $r->lesson,
                'nickname' => $r->owner_nickname,
                'owner'    => $r->booking_subject,
                'fixed'    => (bool) ($r->fixed_slot_id ?? false),
                'dinner'   => (int) ($dinner['status'] ?? 0) === 1,
            ];
        }

        return $map;
    }

    /**
     * Il mese come lo consuma la vista: stessa forma di prima
     * (['year','month','days'=>[...]]), ma costruita solo per i suoi giorni.
     */
    private function buildMonth(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $adv         = Setting::props('advanced');
        $field_set   = Setting::fieldSet();
        $trainer_set = $adv['trainer_set'] ?? [];
        $day_off     = $adv['day_off'] ?? [];

        $reserved = $this->reservationsInRange(
            $start->format('Y-m-d').' 00:00',
            $start->copy()->addMonth()->format('Y-m-d').' 00:00'
        );

        // I maestri delle fasce: prima si leggeva un utente per ogni fascia
        // con lezione, qui si legge una volta la bandiera di ciascuno.
        $flags = User::query()
            ->whereNotNull('flag')
            ->pluck('flag', 'id')
            ->all();
        $flagsByPlayer = User::query()
            ->whereNotNull('playerId')
            ->pluck('flag', 'playerId')
            ->all();

        $days       = [];
        $first_day  = $start->copy();

        while ($first_day->lessThanOrEqualTo($end)) {
            $day = [
                'date' => $first_day->format('Y-m-d'),
                'year' => $first_day->year,
                'day' => $first_day->format('j'), // 1 - 31
                'month' => $first_day->month, // 1 - 12
                'day_w' => $first_day->format('N'), // 1 = lunedì, 7 = domenica
                'fields' => array_map(fn() => [], $field_set),
                'status' => true, // libero, pieno, parziale
                'reserved_match' => 0,
                'reserved_lesson' => 0,
                'reserved_tournament' => 0,
                'reserved_dinner' => 0,
            ];

            if (in_array($day['date'], $day_off)) {
                $day['status'] = false;
            }

            $delGiorno = $reserved[$day['date']] ?? [];

            foreach ($field_set as $k => $f) {

                // Ogni giorno ha il suo orario: se manca, quel giorno è chiuso.
                $orario = FieldSchedule::hours($f, (int) $day['day_w']);

                if (! $orario) {
                    continue;
                }

                $start_time = Carbon::createFromTimeString($orario['h_start']);
                $end_time   = Carbon::createFromTimeString($orario['h_end']);

                if ($end_time->lessThanOrEqualTo($start_time)) {
                    $end_time->addDay(); // chiusura dopo la mezzanotte
                }

                // Le fasce "intere" restano ancorate all'apertura del giorno.
                $hour_test = $start_time->copy();
                $hour_array_control = [];

                while ($hour_test->lessThan($end_time)) {
                    $hour_array_control[] = $hour_test->format('H:i');
                    $hour_test->addMinutes($f['m_during_client']);
                }

                do {
                    $hour_f = $start_time->copy()->format('H:i');
                    $res    = $delGiorno[$k][$hour_f] ?? null;

                    if ($res === null) {
                        $status = 0;
                        $trainer_id = [];

                        foreach ($trainer_set as $key => $value) {
                            if (in_array($day['day_w'], $value['day_w']) && $k == $value['field']) {
                                if ($this->isTimeInRange($hour_f, $value['h_start'], $value['h_end'])) {
                                    $status = 1;
                                    $trainer_id[] = $key;
                                }
                            }
                        }

                        // Le fasce libere sono la stragrande maggioranza e viaggiano
                        // tutte nella pagina: le chiavi che valgono zero o niente
                        // si lasciano fuori, il javascript sa già cosa metterci.
                        $hour_null = ['time' => $hour_f];

                        if ($status == 1) {
                            $hour_null['status'] = 1;
                            $hour_null['trainer_id'] = $trainer_id;

                            if (isset($flags[$trainer_id[0]])) {
                                $hour_null['flag'] = $flags[$trainer_id[0]];
                            }
                        }

                        if (in_array($hour_f, $hour_array_control)) {
                            $hour_null['s'] = 1;
                        }

                        $day['fields'][$k]['times'][] = $hour_null;
                    } else {
                        $time_in = [
                            'time' => $hour_f,
                            'status' => 2,
                            'id' => $res['id'],
                            'lesson' => $res['lesson'],
                            'booking_subject' => $res['nickname'] ?? 'utente cancellato',
                            'd' => $res['duration'],
                        ];

                        if (in_array($hour_f, $hour_array_control)) {
                            $time_in['s'] = 1;
                        }

                        // Distingue in calendario le occorrenze dei campi fissi
                        if ($res['fixed']) {
                            $time_in['fixed'] = true;
                        }

                        if ($res['lesson'] == 1) {
                            if (isset($flagsByPlayer[$res['owner']])) {
                                $time_in['flag'] = $flagsByPlayer[$res['owner']];
                            }
                            $day['reserved_lesson']++;
                        } elseif ($res['lesson'] == 2) {
                            $day['reserved_tournament']++;
                        } else {
                            $day['reserved_match']++;
                        }

                        if ($res['dinner']) {
                            $day['reserved_dinner']++;
                        }

                        $day['fields'][$k]['times'][] = $time_in;
                        $day['fields'][$k]['match'] = ($day['fields'][$k]['match'] ?? 0) + 1;
                        $start_time->addMinutes($f['m_during'] * ($res['duration'] - 1));
                    }

                    $start_time->addMinutes($f['m_during']);
                } while ($start_time->lessThan($end_time));
            }

            $days[] = $day;
            $first_day->addDay();
        }

        return [
            'year'  => $year,
            'month' => $month,
            'days'  => $days,
        ];
    }

    private function isTimeInRange(string $time, string $hStart, string $hEnd): bool
    {
        $t  = strtotime($time);
        $s  = strtotime($hStart);
        $e  = strtotime($hEnd);

        return $t >= $s && $t < $e; // h_end escluso
    }


}
