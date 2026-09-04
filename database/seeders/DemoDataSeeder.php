<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\FieldHour;
use App\Models\FixedSlot;
use App\Models\FixedSlotException;
use App\Models\Listing;
use App\Models\Player;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Models\User;
use App\Services\FixedSlotService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Dati finti per provare il gestionale senza costruirli a mano.
 *
 *   php artisan db:seed --class=DemoDataSeeder
 *
 * Copre in particolare le cose nuove: tornei con i campi impegnati, la regola
 * che vieta due tornei sugli stessi campi negli stessi giorni, la banda del
 * torneo sul calendario con le iscrizioni, e la differenza fra un torneo vero
 * e una prenotazione segnata come "partita di torneo".
 *
 * È rifacibile: all'inizio svuota solo le tabelle che riempie lui.
 */
class DemoDataSeeder extends Seeder
{
    /** Chiavi dei campi: le stesse che finiscono in field_set nelle impostazioni. */
    private const CAMPI = ['Campo 1', 'Campo 2', 'Campo 3', 'Campo 4'];

    public function run()
    {
        $this->command->warn('Questo seeder svuota e ricrea i dati di prova.');

        $this->svuota();
        $this->impostazioni();

        $giocatori = $this->giocatori();
        $utenti    = $this->utenti($giocatori);

        $prenotazioni = $this->prenotazioni($giocatori);
        $this->campiFissi($giocatori, $utenti['admin']);
        $tornei = $this->tornei($giocatori, $prenotazioni);
        $this->bacheca($giocatori);
        $this->modelliMail();

        $this->riepilogo($tornei);
    }

    // ==========================================================

    private function svuota(): void
    {
        // Ordine: prima i figli, poi i padri, altrimenti le chiavi esterne protestano.
        DB::table('player_reservation')->delete();
        DB::table('tournament_matches')->delete();
        DB::table('tournament_registrations')->delete();
        DB::table('tournaments')->delete();
        DB::table('listing_images')->delete();
        DB::table('listings')->delete();
        DB::table('fixed_slot_exceptions')->delete();
        DB::table('reservations')->delete();
        DB::table('fixed_slots')->delete();
        DB::table('players')->delete();
        DB::table('users')->delete();
        DB::table('models')->delete();
        DB::table('field_hours')->delete();
        DB::table('fields')->delete();
        DB::table('settings')->delete();
    }

    private function impostazioni(): void
    {
        // n_slot conta le fasce da m_during_client: 10 x 90' = apertura 08:00,
        // chiusura 23:00. La griglia di partenza resta invece di 30 minuti.
        $campoBase = [
            'h_start' => '08:00',
            'n_slot' => '10',
            'm_during' => '30',
            'm_during_client' => '90',
            'type' => 'padel',
            'closed_days' => [],
        ];

        $fieldSet = [];
        foreach (self::CAMPI as $campo) {
            $fieldSet[$campo] = $campoBase;
        }
        // Un campo chiuso la domenica, per vedere "campo non disponibile"
        $fieldSet['Campo 4']['closed_days'] = [7];

        $settings = [
            ['name' => 'Servizio di Prenotazione Online', 'status' => 2, 'property' => []],
            ['name' => 'Periodo di Ferie', 'status' => 0, 'property' => ['from' => '', 'to' => '']],
            ['name' => 'Impostazioni cena', 'status' => 2, 'property' => ['user_mail' => 'cucina@kresceria.it']],
            ['name' => 'Contatti', 'property' => [
                'phone' => '3271622244',
                'email' => 'info@freesport-padel.it',
                'whatsapp' => '+393271622244',
                'youtube' => '',
                'instagram' => 'https://instagram.com/freesportchiaravalle',
                'tiktok' => '',
                'facebook' => '',
            ]],
            ['name' => 'advanced', 'property' => [
                'delay_trainer' => 7,
                'max_delay_default' => 24,
                'day_off' => [now()->addDays(9)->format('Y-m-d')], // un giorno bloccato, per provare "Blocca giorni"
                'field_set' => $fieldSet,
                'trainer_set' => [],
            ]],
        ];

        foreach ($settings as $s) {
            Setting::create([
                'name' => $s['name'],
                'status' => $s['status'] ?? 1,
                'property' => json_encode($s['property']),
            ]);
        }

        $this->campi();
    }

    /**
     * I campi con i loro orari giorno per giorno: da qui li legge tutto il
     * gestionale. Il `field_set` nelle impostazioni resta solo come rete di
     * sicurezza per le installazioni non ancora migrate.
     */
    private function campi(): void
    {
        $sort = 0;

        foreach (self::CAMPI as $nome) {
            $field = Field::create([
                'name' => $nome,
                'type' => 'padel',
                'm_during' => 30,
                'm_during_client' => 90,
                'sort' => $sort++,
            ]);

            foreach (array_keys(FieldHour::WEEKDAYS) as $weekday) {
                // Campo 4 chiuso la domenica e Campo 3 con il fine settimana
                // corto: servono a vedere in pagina gli orari per giorno.
                $chiuso = $nome === 'Campo 4' && $weekday === 7;
                $corto = $nome === 'Campo 3' && in_array($weekday, [6, 7], true);

                FieldHour::create([
                    'field_id' => $field->id,
                    'weekday' => $weekday,
                    'closed' => $chiuso,
                    'h_start' => $chiuso ? null : ($corto ? '09:00' : '08:00'),
                    'h_end' => $chiuso ? null : ($corto ? '20:00' : '23:00'),
                ]);
            }
        }
    }

    private function utenti(array $giocatori): array
    {
        $admin = User::create([
            'role' => 'admin',
            'name' => 'Giordano',
            'surname' => 'Giorgi',
            'email' => 'admin@demo.it',
            'phone' => '3385314356',
            'flag' => '#23B792',
            'playerId' => $giocatori['Gio']->id,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $trainer = User::create([
            'role' => 'trainer',
            'name' => 'Daniel',
            'surname' => 'Martín',
            'email' => 'istruttore@demo.it',
            'phone' => '3394773897',
            'flag' => '#4DA3FF',
            'playerId' => $giocatori['Dany']->id,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        return ['admin' => $admin, 'trainer' => $trainer];
    }

    /** Dodici giocatori con storie diverse: certificati, verifiche mail, livelli. */
    private function giocatori(): array
    {
        $base = [
            ['Gio', 'Giordano', 'Giorgi', 'm', 5, 'admin'],
            ['Dany', 'Daniel', 'Martín', 'm', 5, 'trainer'],
            ['Cris', 'Cristian', 'Lazzari', 'm', 4, 'player'],
            ['Ele', 'Elena', 'Rossi', 'f', 4, 'player'],
            ['Marti', 'Martina', 'Bianchi', 'f', 3, 'player'],
            ['Luca', 'Luca', 'Verdi', 'm', 3, 'player'],
            ['Sara', 'Sara', 'Neri', 'f', 2, 'player'],
            ['Ale', 'Alessandro', 'Conti', 'm', 2, 'player'],
            ['Fede', 'Federica', 'Moretti', 'f', 1, 'player'],
            ['Ricky', 'Riccardo', 'Gallo', 'm', 3, 'player'],
            ['Vale', 'Valentina', 'Costa', 'f', 4, 'player'],
            ['Teo', 'Matteo', 'Ferrari', 'm', 1, 'player'],
        ];

        $citta = ['Chiaravalle', 'Falconara', 'Ancona', 'Jesi', 'Senigallia'];
        $giocatori = [];

        foreach ($base as $i => [$nick, $nome, $cognome, $sesso, $livello, $ruolo]) {
            $giocatori[$nick] = Player::create([
                'role' => $ruolo,
                'nickname' => $nick,
                'name' => $nome,
                'surname' => $cognome,
                'sex' => $sesso,
                'level' => $livello,
                'phone' => '33'.str_pad((string) (10000000 + $i * 111111), 8, '0', STR_PAD_LEFT),
                'mail' => strtolower($nick).'@demo.it',
                'city' => $citta[$i % count($citta)],
                'birth_date' => now()->subYears(20 + $i)->format('Y-m-d'),
                'hand' => $i % 3 === 0 ? 'sx' : 'dx',
                'preferred_position' => ['dritto', 'rovescio', 'indifferente'][$i % 3],
                'bio' => $i % 4 === 0 ? 'Gioca da tre anni, preferisce il doppio serale.' : null,
                'note' => $i % 5 === 0 ? 'Paga sempre in contanti.' : null,
                // Certificati in tutti gli stati: valido, in scadenza, scaduto, mancante
                'certificate_expires_at' => match ($i % 4) {
                    0 => now()->addMonths(8)->format('Y-m-d'),
                    1 => now()->addDays(12)->format('Y-m-d'),
                    2 => now()->subDays(20)->format('Y-m-d'),
                    default => null,
                },
                // Due giocatori con la mail non ancora verificata
                'mail_verified_at' => in_array($i, [6, 9], true) ? null : now()->subMonths(2),
            ]);
        }

        return $giocatori;
    }

    /**
     * Prenotazioni sparse fra ieri e tre settimane avanti: partite, lezioni,
     * partite di torneo, una annullata, due partite aperte, cene.
     */
    private function prenotazioni(array $giocatori): array
    {
        $creata = [];

        $righe = [
            // [giorni da oggi, ora, campo, durata slot, tipo (lesson), stato, cena, chiave]
            [0,  '09:30', 'Campo 1', 3, 0, 1, true,  'oggi_mattina'],
            [0,  '18:00', 'Campo 2', 3, 1, 1, false, 'oggi_lezione'],
            [0,  '20:00', 'Campo 3', 3, 0, 1, true,  'oggi_sera'],
            [1,  '19:00', 'Campo 1', 3, 2, 1, false, 'torneo_gara_1'],
            [1,  '20:30', 'Campo 2', 3, 2, 1, false, 'torneo_gara_2'],
            [2,  '18:00', 'Campo 3', 2, 0, 1, false, 'aperta_1'],
            [3,  '19:30', 'Campo 1', 3, 0, 0, false, 'annullata'],
            [4,  '17:00', 'Campo 4', 3, 1, 1, false, 'lezione_futura'],
            [5,  '21:00', 'Campo 2', 3, 0, 1, true,  'aperta_2'],
            [-1, '19:00', 'Campo 1', 3, 0, 1, false, 'ieri'],
            [-32, '19:00', 'Campo 2', 3, 0, 1, false, 'mese_scorso'],
            [-6, '20:00', 'Campo 3', 3, 2, 1, false, 'torneo_passato_1'],
            [-6, '21:30', 'Campo 4', 3, 2, 1, false, 'torneo_passato_2'],
        ];

        $intestatari = array_values($giocatori);

        foreach ($righe as $i => [$giorni, $ora, $campo, $durata, $tipo, $stato, $cena, $chiave]) {
            $slot = now()->addDays($giorni);
            [$h, $m] = explode(':', $ora);
            $slot->setTime((int) $h, (int) $m);

            $intestatario = $intestatari[$i % count($intestatari)];
            $aperta = in_array($chiave, ['aperta_1', 'aperta_2'], true);

            $id = DB::table('reservations')->insertGetId([
                'date_slot' => $slot->format('Y-m-d H:i'),
                'field' => $campo,
                'status' => $stato,
                'type' => 'Padel',
                'lesson' => $tipo,
                'message' => $i % 3 === 0 ? 'Portano loro le palline.' : null,
                'dinner' => json_encode([
                    'status' => $cena,
                    'guests' => $cena ? 4 : 0,
                    'time' => $cena ? '21:30' : '',
                ]),
                'duration' => $durata,
                'booking_subject' => $intestatario->id,
                'is_open' => $aperta,
                'open_category' => $aperta ? 'match' : null,
                'slots_total' => $aperta ? 4 : null,
                'level_min' => $aperta ? 2 : null,
                'level_max' => $aperta ? 4 : null,
                'open_note' => $aperta ? 'Cerchiamo due giocatori di pari livello.' : null,
                'open_closes_at' => $aperta ? $slot->copy()->subHours(3) : null,
                'created_at' => now()->subDays(max(0, 7 - $i)),
                'updated_at' => now(),
            ]);

            $creata[$chiave] = $id;

            // Partecipanti: l'intestatario è l'organizzatore, più qualche iscritto
            $partecipanti = collect($intestatari)->shuffle()->take($aperta ? 2 : 3)->push($intestatario)->unique('id');

            foreach ($partecipanti as $p) {
                DB::table('player_reservation')->insertOrIgnore([
                    'player_id' => $p->id,
                    'reservation_id' => $id,
                    'join_status' => 'accepted',
                    'joined_at' => now()->subDays(2),
                    'is_owner' => $p->id === $intestatario->id,
                ]);
            }

            // Una richiesta ancora da accettare, per vedere lo stato "in attesa"
            if ($chiave === 'aperta_1') {
                DB::table('player_reservation')->insertOrIgnore([
                    'player_id' => $giocatori['Teo']->id,
                    'reservation_id' => $id,
                    'join_status' => 'pending',
                    'joined_at' => now(),
                    'is_owner' => false,
                ]);
            }
        }

        return $creata;
    }

    private function campiFissi(array $giocatori, User $admin): void
    {
        $slot = FixedSlot::create([
            'player_id' => $giocatori['Cris']->id,
            'field' => 'Campo 2',
            'weekday' => 3,
            'start_time' => '19:00',
            'duration' => 3,
            'valid_from' => now()->subMonth()->format('Y-m-d'),
            'valid_to' => now()->addMonths(6)->format('Y-m-d'),
            'status' => 'active',
            'price' => 30,
            'note' => 'Accordo annuale, paga a fine mese.',
            'created_by' => $admin->id,
        ]);

        FixedSlotException::create([
            'fixed_slot_id' => $slot->id,
            'date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'festivo',
            'note' => 'Chiuso per festività',
        ]);

        $sospeso = FixedSlot::create([
            'player_id' => $giocatori['Ele']->id,
            'field' => 'Campo 3',
            'weekday' => 5,
            'start_time' => '18:30',
            'duration' => 2,
            'valid_from' => now()->subMonths(3)->format('Y-m-d'),
            'valid_to' => now()->addMonths(2)->format('Y-m-d'),
            'status' => 'suspended',
            'price' => 25,
            'created_by' => $admin->id,
        ]);

        // Come dal back office: le prenotazioni del campo fisso nascono qui,
        // tutte insieme. Quello sospeso non ne genera nessuna.
        $servizio = app(FixedSlotService::class);
        $servizio->refresh($slot->fresh('exceptions'));
        $servizio->refresh($sospeso);
    }

    /**
     * Quattro tornei che raccontano tutti i casi:
     * - uno aperto che copre oggi sui campi 1 e 2 (banda sul calendario);
     * - uno in corso negli stessi giorni ma sui campi 3 e 4: convive, perché
     *   i campi non si sovrappongono;
     * - uno concluso con incontri e risultati, per la classifica;
     * - una bozza senza campi assegnati.
     */
    private function tornei(array $giocatori, array $prenotazioni): array
    {
        // --- 1. Aperto, copre oggi e domani, campi 1 e 2 ---
        $aperto = Tournament::create([
            'name' => 'Americano di fine estate',
            'slug' => Tournament::makeSlug('Americano di fine estate'),
            'description' => 'Formula americana, si gioca tutti contro tutti.',
            'regulation' => 'Partite a tempo, 20 minuti. Cambio compagno a ogni turno.',
            'type' => 'Padel',
            'format' => 'americano',
            'teams_max' => 12,
            'is_pair' => true,
            'price' => 25,
            'level_min' => 2,
            'level_max' => 5,
            'starts_at' => now()->setTime(19, 0),
            'ends_at' => now()->addDay()->setTime(23, 0),
            'registration_opens_at' => now()->subWeek(),
            'registration_closes_at' => now()->addHours(6),
            'status' => 'open',
            'location' => 'Free Sport Chiaravalle',
            'note' => 'Premi offerti dallo sponsor.',
            'fields' => ['Campo 1', 'Campo 2'],
        ]);

        // Iscrizioni in stati diversi, pagate e non. Le coppie non riusano lo
        // stesso giocatore: c'è un indice unico (torneo, giocatore).
        $coppie = [
            [['Gio', 'Dany'],   'confirmed', 'I Falchi'],
            [['Cris', 'Ele'],   'confirmed', null],
            [['Marti', 'Luca'], 'confirmed', null],
            [['Sara', 'Ale'],   'confirmed', null],
            [['Fede', 'Ricky'], 'pending',   null],
            [['Vale', 'Teo'],   'waitlist',  null],
        ];

        foreach ($coppie as $i => [[$uno, $due], $stato, $squadra]) {
            TournamentRegistration::create([
                'tournament_id' => $aperto->id,
                'player_id' => $giocatori[$uno]->id,
                'partner_player_id' => $giocatori[$due]->id,
                'team_name' => $squadra,
                'status' => $stato,
                'paid' => $stato === 'confirmed' && $i % 2 === 0,
                'created_at' => now()->subDays(7 - $i),
                'updated_at' => now(),
            ]);
        }

        // Due incontri agganciati alle prenotazioni "partita di torneo" di domani:
        // è il collegamento che distingue la partita dal torneo.
        $squadre = $aperto->registrations()->where('status', 'confirmed')->get();
        foreach ([['torneo_gara_1', 0, 1], ['torneo_gara_2', 2, 3]] as $i => [$chiave, $a, $b]) {
            if (! isset($squadre[$a], $squadre[$b], $prenotazioni[$chiave])) {
                continue;
            }

            TournamentMatch::create([
                'tournament_id' => $aperto->id,
                'round' => 'Turno 1',
                'group_name' => 'A',
                'position' => $i + 1,
                'reservation_id' => $prenotazioni[$chiave],
                'team_a_id' => $squadre[$a]->id,
                'team_b_id' => $squadre[$b]->id,
                'played_at' => now()->addDay()->setTime(19 + $i, 0),
                'status' => 'scheduled',
            ]);
        }

        // --- 2. In corso negli stessi giorni, ma su campi diversi ---
        $parallelo = Tournament::create([
            'name' => 'Torneo femminile a gironi',
            'slug' => Tournament::makeSlug('Torneo femminile a gironi'),
            'description' => 'Gironi da tre squadre, poi semifinali.',
            'type' => 'Padel',
            'format' => 'gironi',
            'teams_max' => 6,
            'is_pair' => true,
            'price' => 20,
            'starts_at' => now()->setTime(18, 30),
            'ends_at' => now()->addDay()->setTime(22, 0),
            'status' => 'running',
            'location' => 'Free Sport Chiaravalle',
            'fields' => ['Campo 3', 'Campo 4'],
        ]);

        $femminile = [
            [['Ele', 'Marti'], 'confirmed'],
            [['Sara', 'Fede'], 'confirmed'],
            [['Vale', 'Cris'], 'rejected'],
        ];

        foreach ($femminile as $i => [[$uno, $due], $stato]) {
            TournamentRegistration::create([
                'tournament_id' => $parallelo->id,
                'player_id' => $giocatori[$uno]->id,
                'partner_player_id' => $giocatori[$due]->id,
                'status' => $stato,
                'paid' => $stato === 'confirmed',
                'note' => $stato === 'rejected' ? 'Iscrizione fuori livello.' : null,
                'created_at' => now()->subDays(5 - $i),
                'updated_at' => now(),
            ]);
        }

        // --- 3. Concluso, con risultati: serve alla classifica ---
        $concluso = Tournament::create([
            'name' => 'Coppa di primavera',
            'slug' => Tournament::makeSlug('Coppa di primavera'),
            'type' => 'Padel',
            'format' => 'gironi',
            'teams_max' => 4,
            'is_pair' => true,
            'price' => 15,
            'starts_at' => now()->subDays(6)->setTime(20, 0),
            'ends_at' => now()->subDays(6)->setTime(23, 30),
            'status' => 'finished',
            'location' => 'Free Sport Chiaravalle',
            'fields' => ['Campo 3', 'Campo 4'],
        ]);

        $squadreConcluso = [];
        foreach ([['Cris', 'Luca'], ['Ale', 'Ricky'], ['Teo', 'Gio'], ['Dany', 'Vale']] as $i => [$uno, $due]) {
            $squadreConcluso[] = TournamentRegistration::create([
                'tournament_id' => $concluso->id,
                'player_id' => $giocatori[$uno]->id,
                'partner_player_id' => $giocatori[$due]->id,
                'status' => 'confirmed',
                'paid' => true,
                'created_at' => now()->subDays(12 - $i),
                'updated_at' => now(),
            ]);
        }

        $partite = [
            [0, 1, '6-4 6-3', 'torneo_passato_1'],
            [2, 3, '6-2 4-6 7-5', 'torneo_passato_2'],
            [0, 2, '6-1 6-4', null],
            [1, 3, '3-6 6-4 6-2', null],
        ];

        foreach ($partite as $i => [$a, $b, $risultato, $chiave]) {
            $sets = TournamentMatch::parseScore($risultato);

            TournamentMatch::create([
                'tournament_id' => $concluso->id,
                'round' => 'Girone unico',
                'group_name' => 'A',
                'position' => $i + 1,
                'reservation_id' => $chiave ? ($prenotazioni[$chiave] ?? null) : null,
                'team_a_id' => $squadreConcluso[$a]->id,
                'team_b_id' => $squadreConcluso[$b]->id,
                'score' => $sets,
                'winner' => TournamentMatch::winnerFromScore($sets),
                'played_at' => now()->subDays(6)->setTime(20 + $i % 3, 0),
                'status' => 'played',
            ]);
        }

        // --- 4. Bozza senza campi: non blocca nessuno e non si vede sul sito ---
        $bozza = Tournament::create([
            'name' => 'Torneo di Natale (bozza)',
            'slug' => Tournament::makeSlug('Torneo di Natale (bozza)'),
            'type' => 'Padel',
            'format' => 'eliminazione',
            'teams_max' => 16,
            'is_pair' => true,
            'starts_at' => now()->addMonths(3)->setTime(15, 0),
            'status' => 'draft',
            'fields' => [],
        ]);

        return compact('aperto', 'parallelo', 'concluso', 'bozza');
    }

    private function bacheca(array $giocatori): void
    {
        $annunci = [
            ['Racchetta Head Delta Pro', 'racchette', 'usato', 120, 'pending', null],
            ['Borsone Adidas 3 racchette', 'accessori', 'come nuovo', 45, 'published', null],
            ['Scarpe Asics Gel Padel 43', 'scarpe', 'usato', 35, 'sold', null],
            ['Palline nuove, 6 tubi', 'accessori', 'nuovo', 18, 'rejected', 'Foto non chiare, ricaricale.'],
            ['Overgrip stock 20 pezzi', 'accessori', 'nuovo', 12, 'expired', null],
        ];

        $venditori = array_values($giocatori);

        foreach ($annunci as $i => [$titolo, $categoria, $condizione, $prezzo, $stato, $motivo]) {
            Listing::create([
                'player_id' => $venditori[$i % count($venditori)]->id,
                'title' => $titolo,
                'description' => "Vendo per inutilizzo.\nRitiro in circolo o spedizione a carico dell'acquirente.",
                'category' => $categoria,
                'condition' => $condizione,
                'price' => $prezzo,
                'contact_phone' => '3331234567',
                'contact_mail' => 'venditore@demo.it',
                'show_phone' => true,
                'show_mail' => false,
                'status' => $stato,
                'reject_reason' => $motivo,
                'expires_at' => $stato === 'published' ? now()->addDays(30) : ($stato === 'expired' ? now()->subDays(3) : null),
            ]);
        }

        // Le visualizzazioni non sono fillable: si aggiornano a parte
        DB::table('listings')->update(['views' => DB::raw('FLOOR(RAND() * 80)')]);
    }

    private function modelliMail(): void
    {
        DB::table('models')->insert([
            [
                'name' => 'Promo autunno',
                'object' => 'Torna in campo con noi',
                'heading' => 'Le serate si accorciano, i match no',
                'body' => 'Ciao!\nDa lunedì riparte il campionato sociale./*/Prenota il tuo campo dal sito: i posti vanno via in fretta.',
                'ending' => 'Ci vediamo in campo.',
                'sender' => 'Lo staff di Free Sport Chiaravalle',
                'img_1' => null,
                'img_2' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Apertura iscrizioni tornei',
                'object' => 'Iscrizioni aperte',
                'heading' => 'Il torneo di fine estate ti aspetta',
                'body' => 'Iscrizioni aperte fino a esaurimento posti.\nCoppie di livello 2-5./*/Quota 25 euro a coppia, si salda in struttura.',
                'ending' => 'A presto!',
                'sender' => 'Lo staff',
                'img_1' => null,
                'img_2' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function riepilogo(array $tornei): void
    {
        $this->command->info('');
        $this->command->info('Dati di prova pronti.');
        $this->command->line('  Accesso:   admin@demo.it / password        (amministratore)');
        $this->command->line('             istruttore@demo.it / password   (istruttore)');
        $this->command->info('');
        $this->command->line('  Calendario: oggi e domani trovi la banda di "'.$tornei['aperto']->name.'" (campi 1 e 2)');
        $this->command->line('              e in parallelo "'.$tornei['parallelo']->name.'" (campi 3 e 4).');
        $this->command->line('  Conflitti:  prova a creare un torneo su Campo 1 nelle stesse date: viene bloccato.');
        $this->command->line('  Domani:     due fasce marcate "partita di torneo", agganciate agli incontri.');
        $this->command->line('  Classifica: aperta nella scheda di "'.$tornei['concluso']->name.'".');
        $this->command->info('');
    }
}
