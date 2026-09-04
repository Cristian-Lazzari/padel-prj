<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\Field;
use App\Models\FieldHour;
use App\Models\User;
use App\Models\Player;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{

    
    public function index(){
        $settings = Setting::all()->keyBy('name');
        $users = User::where('role', 'trainer')->get();
        $trainers = [];
        foreach ($users as $u) {
            $player = Player::find($u->playerId);

            // Un'utenza che punta a un giocatore cancellato non deve far
            // esplodere l'intera pagina delle impostazioni.
            if (! $player) {
                continue;
            }

            $player->flag = $u->flag;
            // L'elenco mostra il giocatore, ma il pulsante di rimozione
            // agisce sull'utenza: servono qui id e indirizzo dell'accesso.
            $player->user_id = $u->id;
            $player->user_email = $u->email;
            $trainers[] = $player;
        }
        return view('admin.settings', compact('settings' , 'trainers'));
    }

    /**
     * Toglie a un istruttore l'accesso al gestionale.
     *
     * Sparisce l'utenza, non la persona: la scheda giocatore resta in
     * archivio con le sue lezioni e le sue prenotazioni. Va via anche la
     * fascia di disponibilità del maestro, che è indicizzata sull'id
     * dell'utenza e senza di questa resterebbe orfana in calendario.
     */
    public function trainerDestroy(User $user)
    {
        abort_unless($user->role === 'trainer', 404);
        abort_if($user->id === auth()->id(), 403, 'Non puoi rimuovere il tuo stesso accesso');

        $advanced = Setting::where('name', 'advanced')->first();

        if ($advanced) {
            $property = json_decode($advanced->property, true) ?: [];

            if (isset($property['trainer_set'][$user->id])) {
                unset($property['trainer_set'][$user->id]);
                $advanced->property = json_encode($property);
                $advanced->save();
            }
        }

        $player = Player::find($user->playerId);

        $user->tokens()->delete();
        $user->delete();

        $nome = trim($user->name.' '.$user->surname) ?: $user->email;

        return back()->with('message', 'Accesso di '.$nome.' rimosso.'
            .($player ? ' La scheda giocatore #'.$player->nickname.' resta in archivio con le sue lezioni.' : ''));
    }

    public function updateAll(Request $request)
    {
        // I campi si validano prima di toccare qualsiasi cosa: se l'orario di
        // un giorno è sbagliato la pagina torna indietro senza aver salvato
        // metà impostazioni.
        $fields = $this->validateFields($request);

        $setting = Setting::all()->keyBy('name');
        $data = $request->all();

        $setting['Servizio di Prenotazione Online']->status = $data['status_service'];
        $setting['Servizio di Prenotazione Online']->save();

        $setting['Periodo di Ferie']->status = $data['ferie_status'];
        $propertyArray = [
            'from' => $data['from'],
            'to' => $data['to'],
        ];
        $setting['Periodo di Ferie']->property = json_encode($propertyArray);
        $setting['Periodo di Ferie']->save();

        
        if(!isset($setting['Impostazioni cena'])) {
            $setting['Impostazioni cena'] = new Setting();
            $setting['Impostazioni cena']->name = 'Impostazioni cena';
        }

        $setting['Impostazioni cena']->status = $data['dinner_status'];
        $propertyArray = [
            'user_mail' => $data['user_mail'],
        ];
        $setting['Impostazioni cena']->property = json_encode($propertyArray);
        $setting['Impostazioni cena']->save();


        $contatti = [
            'phone'  => $request->phone,
            'email'     => $request->email,
            'instagram' => $request->instagram,
            'facebook'  => $request->facebook,
            'youtube'   => $request->youtube,
            'tiktok'    => $request->tiktok,
            'whatsapp'  => $request->whatsapp,
        ];
        $setting['Contatti']->property = json_encode($contatti);
        $setting['Contatti']->save();      
        
        $day_off = json_decode($setting['advanced']->property, 1)['day_off'];
        $field_set = json_decode($setting['advanced']->property, 1)['field_set'];
        $trainer_set = json_decode($setting['advanced']->property, 1)['trainer_set']?? [];
                

        
        // I campi vivono nelle tabelle `fields` e `field_hours`. Il `field_set`
        // dentro `advanced` resta com'è, congelato: serve solo da rete di
        // sicurezza se un domani le tabelle sparissero.
        $this->saveFields($fields);
       
        if (auth()->user()->role == 'trainer') {
            $validated = $request->validate([
                'set_trainer.h_start' => ['required', 'date_format:H:i'],
                'set_trainer.h_end'   => ['required', 'date_format:H:i', 'after:set_trainer.h_start'],
            ], [
                'set_trainer.h_end.after' => 'L\'ora di fine deve essere successiva all\'ora di inizio.',
            ]);
        
            $trainer_set[auth()->user()->id] = [
                'field' => $data['set_trainer']['field'] ?? 0,
                'h_start' => $data['set_trainer']['h_start'],
                'h_end' => $data['set_trainer']['h_end'],
                'day_w' => $data['set_trainer']['day_w'] ?? [],
            ];
            
        }
        $setting['advanced']->property = json_encode([
            'day_off'           => $day_off,
            'max_delay_default' => $data['max_delay_default'],
            'delay_trainer'     => $data['delay_trainer'],
            'field_set'         => $field_set,  
            'trainer_set'       => $trainer_set,
        ]);

        $setting['advanced']->save();
        
        $m = 'Le impostazioni sono state ggiornate correttamente';

        return redirect()->back()->with('success', $m);   
    }

    public function cancelDates(Request $request){
        $data = $request->all();
        $s = Setting::where('name', 'advanced')->first();
        // /dd($data['day_off']);
        $adv = json_decode($s->property, 1);
        $adv['day_off'] = $data['day_off'] ?? [];

        $s->property = json_encode($adv);
        $s->update();
        
        return redirect()->route('admin.dashboard')->with('message', 'Le date sono state modificate correttamente');

    }

    // ==========================================================
    // Campi
    // ==========================================================

    /**
     * Controlla il blocco "Campi" del modulo e lo restituisce ripulito.
     *
     * Ogni campo porta i suoi sette giorni: un giorno o è chiuso, o ha
     * apertura e chiusura che stanno in piedi da sole — la fine dopo
     * l'inizio, e con dentro almeno una fascia della durata minima.
     */
    private function validateFields(Request $request): array
    {
        $request->validate([
            'field_set' => 'array',
            'field_set.*.name_field' => 'required|string|max:60',
            'field_set.*.type' => 'required|string|max:40',
            'field_set.*.m_during' => 'required|integer|min:5|max:240',
            'field_set.*.m_during_client' => 'required|integer|min:5|max:600',
            'field_set.*.hours' => 'array',
            'field_set.*.hours.*.h_start' => 'nullable|date_format:H:i',
            'field_set.*.hours.*.h_end' => 'nullable|date_format:H:i',
        ], [], $this->fieldAttributes($request));

        $puliti = [];
        $errori = [];

        foreach ((array) $request->input('field_set', []) as $k => $f) {
            $nome = trim((string) ($f['name_field'] ?? ''));

            if ($nome === '') {
                continue; // pannello lasciato in bianco: non si crea un campo senza nome
            }

            $during = (int) $f['m_during'];
            $ore = [];

            foreach (array_keys(FieldHour::WEEKDAYS) as $weekday) {
                $riga = $f['hours'][$weekday] ?? [];
                $chiuso = (bool) ($riga['closed'] ?? false);
                $inizio = $chiuso ? null : substr((string) ($riga['h_start'] ?? ''), 0, 5);
                $fine = $chiuso ? null : substr((string) ($riga['h_end'] ?? ''), 0, 5);
                $giorno = FieldHour::WEEKDAYS[$weekday];

                if (! $chiuso && ($inizio === '' || $fine === '')) {
                    $errori["field_set.$k.hours.$weekday.h_start"] =
                        $nome.', '.mb_strtolower($giorno).': manca apertura o chiusura. Se il campo non apre, segnalo chiuso.';
                    continue;
                }

                if (! $chiuso && $fine <= $inizio) {
                    $errori["field_set.$k.hours.$weekday.h_end"] =
                        $nome.', '.mb_strtolower($giorno).': la chiusura deve venire dopo l\'apertura.';
                    continue;
                }

                if (! $chiuso && $this->minutesBetween($inizio, $fine) < $during) {
                    $errori["field_set.$k.hours.$weekday.h_end"] =
                        $nome.', '.mb_strtolower($giorno).': l\'apertura è più corta della durata minima ('.$during.' minuti).';
                    continue;
                }

                $ore[$weekday] = [
                    'closed' => $chiuso,
                    'h_start' => $chiuso ? null : $inizio,
                    'h_end' => $chiuso ? null : $fine,
                ];
            }

            $puliti[] = [
                'name' => $nome,
                'type' => trim((string) $f['type']),
                'm_during' => $during,
                'm_during_client' => (int) $f['m_during_client'],
                'hours' => $ore,
            ];
        }

        if ($errori) {
            throw ValidationException::withMessages($errori);
        }

        return $puliti;
    }

    /** Scrive i campi e i loro orari, tutto o niente. */
    private function saveFields(array $fields): void
    {
        if (! $fields) {
            return;
        }

        DB::transaction(function () use ($fields) {
            $sort = 0;

            foreach ($fields as $row) {
                $field = Field::updateOrCreate(
                    ['name' => $row['name']],
                    [
                        'type' => $row['type'],
                        'm_during' => $row['m_during'],
                        'm_during_client' => $row['m_during_client'],
                        'sort' => $sort++,
                    ]
                );

                foreach ($row['hours'] as $weekday => $ore) {
                    FieldHour::updateOrCreate(
                        ['field_id' => $field->id, 'weekday' => $weekday],
                        $ore
                    );
                }
            }
        });

        Setting::forgetFieldSet();
    }

    /** Etichette leggibili nei messaggi di errore del blocco campi. */
    private function fieldAttributes(Request $request): array
    {
        $attributi = [];

        foreach ((array) $request->input('field_set', []) as $k => $f) {
            $nome = trim((string) ($f['name_field'] ?? '')) ?: 'campo senza nome';

            $attributi["field_set.$k.name_field"] = 'nome del campo';
            $attributi["field_set.$k.type"] = "sport di $nome";
            $attributi["field_set.$k.m_during"] = "durata minima di $nome";
            $attributi["field_set.$k.m_during_client"] = "durata fascia di $nome";

            foreach (FieldHour::WEEKDAYS as $weekday => $giorno) {
                $attributi["field_set.$k.hours.$weekday.h_start"] = "$nome, apertura di ".mb_strtolower($giorno);
                $attributi["field_set.$k.hours.$weekday.h_end"] = "$nome, chiusura di ".mb_strtolower($giorno);
            }
        }

        return $attributi;
    }

    private function minutesBetween(string $from, string $to): int
    {
        [$h1, $m1] = array_map('intval', explode(':', $from));
        [$h2, $m2] = array_map('intval', explode(':', $to));

        return ($h2 * 60 + $m2) - ($h1 * 60 + $m1);
    }
}
