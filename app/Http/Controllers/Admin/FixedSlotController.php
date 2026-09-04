<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FixedSlot;
use App\Models\FixedSlotException;
use App\Models\Player;
use App\Models\Setting;
use App\Services\FieldSchedule;
use App\Services\FixedSlotService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Gestione dei campi fissi dal back office.
 *
 * Ogni modifica rigenera le occorrenze future: le prenotazioni già
 * materializzate vengono cancellate e ricreate dal servizio.
 */
class FixedSlotController extends Controller
{
    /** Impostazioni dei campi lette una volta sola per richiesta. */
    private ?array $fields = null;

    public function __construct(private FixedSlotService $slots)
    {
    }

    private function rules(): array
    {
        return [
            'player_id' => 'required|exists:players,id',
            'field' => ['required', 'string', 'max:255', Rule::in(array_keys($this->fieldSet()))],
            'weekday' => 'required|integer|min:0|max:6',
            // Inizio e fine sono due punti della griglia del campo: il numero
            // di fasce lo ricava withDuration(), non lo scrive il gestore.
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'valid_from' => 'required|date',
            // Obbligatoria: le prenotazioni si creano tutte adesso, quindi
            // serve sapere fin dove arrivare. Il tetto evita che una data
            // sbagliata generi migliaia di righe.
            'valid_to' => 'required|date|after_or_equal:valid_from|before_or_equal:'
                .now()->addYears(2)->format('Y-m-d'),
            'status' => 'required|in:active,suspended,ended',
            'price' => 'nullable|numeric|min:0|max:99999',
            'note' => 'nullable|string|max:1000',
        ];
    }

    public function index()
    {
        $slots = FixedSlot::with('player:id,nickname,name,surname')
            ->withCount('exceptions')
            ->orderBy('status')
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->get();

        $field_set = $this->fieldSet();

        return view('admin.FixedSlots.index', compact('slots', 'field_set'));
    }

    public function create()
    {
        $slot = new FixedSlot([
            'weekday' => 1,
            'duration' => 3,
            'status' => 'active',
            'valid_from' => now()->format('Y-m-d'),
        ]);

        return view('admin.FixedSlots.create', array_merge(
            compact('slot'),
            $this->formData()
        ));
    }

    public function store(Request $request)
    {
        $data = $this->withDuration($request->validate($this->rules()));
        $data['created_by'] = auth()->id();

        $slot = FixedSlot::create($data);

        $result = $this->slots->refresh($slot);

        return to_route('admin.fixed-slots.show', $slot)
            ->with('message', 'Campo fisso creato: '.$result['created'].' prenotazioni inserite in calendario.')
            ->with('conflicts', $result['conflicts']);
    }

    public function show(FixedSlot $fixedSlot)
    {
        $fixedSlot->load(['player', 'exceptions', 'creator']);

        $field_set = $this->fieldSet();
        $minutes = $field_set[$fixedSlot->field]['m_during'] ?? 30;

        // Prossime occorrenze con lo stato della materializzazione.
        // Gli slot già generati si leggono con una query sola, non una per riga.
        $materialized = $fixedSlot->reservations()->pluck('date_slot')->flip();

        // Tutte le occorrenze che restano, fino alla fine della validità:
        // non c'è più un orizzonte, quindi l'elenco è completo.
        [$from, $to] = $this->slots->window($fixedSlot);

        $upcoming = collect($this->slots->occurrences($fixedSlot, $from, $to))
            ->map(fn ($date) => [
            'date' => $date,
            'materialized' => $materialized->has($date->format('Y-m-d').' '.$fixedSlot->start_time),
        ]);

        return view('admin.FixedSlots.show', compact('fixedSlot', 'field_set', 'minutes', 'upcoming'));
    }

    public function edit(FixedSlot $fixedSlot)
    {
        $slot = $fixedSlot;

        return view('admin.FixedSlots.edit', array_merge(
            compact('slot'),
            $this->formData()
        ));
    }

    public function update(Request $request, FixedSlot $fixedSlot)
    {
        $data = $this->withDuration($request->validate($this->rules()));

        $fixedSlot->update($data);

        $result = $this->slots->refresh($fixedSlot);

        return to_route('admin.fixed-slots.show', $fixedSlot)
            ->with('message', 'Campo fisso aggiornato: '.$result['created'].' prenotazioni ricreate in calendario.')
            ->with('conflicts', $result['conflicts']);
    }

    public function destroy(FixedSlot $fixedSlot)
    {
        // Le occorrenze future spariscono, quelle passate restano come storico
        // (fixed_slot_id va a null grazie a nullOnDelete).
        $this->slots->clearFuture($fixedSlot);
        $fixedSlot->delete();

        return to_route('admin.fixed-slots.index')
            ->with('message', 'Campo fisso eliminato');
    }

    /** Cambio rapido di stato: sospendi, riattiva, chiudi. */
    public function status(Request $request, FixedSlot $fixedSlot)
    {
        $request->validate(['status' => 'required|in:active,suspended,ended']);

        $fixedSlot->status = $request->input('status');

        if ($fixedSlot->status === 'ended' && ! $fixedSlot->valid_to) {
            $fixedSlot->valid_to = now()->format('Y-m-d');
        }

        $fixedSlot->save();

        $result = $this->slots->refresh($fixedSlot);

        $message = [
            'active' => 'Campo fisso riattivato: '.$result['created'].' prenotazioni ricreate in calendario.',
            'suspended' => 'Campo fisso sospeso: le occorrenze future sono state liberate.',
            'ended' => 'Campo fisso chiuso: le occorrenze future sono state liberate.',
        ][$fixedSlot->status];

        return back()->with('message', $message);
    }

    // ==========================================================
    // Eccezioni
    // ==========================================================

    public function exceptionStore(Request $request, FixedSlot $fixedSlot)
    {
        $request->validate([
            'date' => 'required|date',
            'reason' => 'required|in:'.implode(',', FixedSlotException::REASONS),
            'note' => 'nullable|string|max:255',
        ]);

        $exception = FixedSlotException::updateOrCreate(
            ['fixed_slot_id' => $fixedSlot->id, 'date' => $request->input('date')],
            ['reason' => $request->input('reason'), 'note' => $request->input('note')]
        );

        // L'occorrenza di quel giorno va liberata subito.
        $this->slots->clearDate($fixedSlot, $exception->date);

        return back()->with('message', 'Eccezione aggiunta: il campo resta libero il '
            .$exception->date->format('d/m/Y'));
    }

    public function exceptionDestroy(FixedSlot $fixedSlot, FixedSlotException $exception)
    {
        abort_unless($exception->fixed_slot_id === $fixedSlot->id, 404);

        $exception->delete();

        $this->slots->refresh($fixedSlot);

        return back()->with('message', 'Eccezione rimossa: la ricorrenza è stata ripristinata');
    }

    // ==========================================================
    // Helper
    // ==========================================================

    private function fieldSet(): array
    {
        if ($this->fields !== null) {
            return $this->fields;
        }

        return $this->fields = Setting::fieldSet();
    }

    /**
     * La griglia oraria di un campo in un giorno della settimana:
     * dall'apertura alla chiusura di quel giorno, un punto ogni m_during.
     * Sono le opzioni delle due select del modulo e sono anche il limite
     * vero della durata — dopo l'ultimo punto il campo è chiuso.
     *
     * @param  int  $weekday  come lo salva FixedSlot: 0 = domenica
     * @return string[] orari 'H:i', apertura e chiusura comprese
     */
    private function gridPoints(string $field, int $weekday): array
    {
        $set = $this->fieldSet()[$field] ?? null;

        if (! $set) {
            return [];
        }

        return FieldSchedule::points($set, self::isoWeekday($weekday));
    }

    /**
     * Le griglie di ogni campo per ogni giorno, come le legge il javascript
     * del modulo: cambiando campo o giorno le due select si rifanno da qui.
     */
    private function grids(): array
    {
        $grids = [];

        foreach ($this->fieldSet() as $key => $set) {
            $days = [];

            foreach (array_keys(FixedSlot::WEEKDAYS) as $weekday) {
                $days[$weekday] = $this->gridPoints($key, $weekday);
            }

            $grids[$key] = [
                'step' => (int) ($set['m_during'] ?? 30),
                'days' => $days,
            ];
        }

        return $grids;
    }

    /** Da 0 = domenica (FixedSlot) a 7 = domenica (orari dei campi). */
    private static function isoWeekday(int $weekday): int
    {
        return $weekday === 0 ? 7 : $weekday;
    }

    /**
     * Traduce ora di inizio e ora di fine nel numero di fasce salvato in
     * `duration`. Rifiuta tutto ciò che sta fuori dalla griglia del campo:
     * la fine non può superare la chiusura né precedere l'inizio.
     */
    private function withDuration(array $data): array
    {
        $weekday = (int) $data['weekday'];
        $points = $this->gridPoints($data['field'], $weekday);

        if (count($points) < 2) {
            throw ValidationException::withMessages([
                'weekday' => 'Il campo '.$data['field'].' è chiuso di '
                    .mb_strtolower(FixedSlot::WEEKDAYS[$weekday] ?? 'quel giorno')
                    .': scegli un altro giorno o cambia gli orari in impostazioni.',
            ]);
        }

        $start = array_search($data['start_time'], $points, true);
        $end = array_search($data['end_time'], $points, true);

        if ($start === false || $start === count($points) - 1) {
            throw ValidationException::withMessages([
                'start_time' => 'Ora di inizio fuori dagli orari del campo: scegline una dall\'elenco.',
            ]);
        }

        if ($end === false) {
            throw ValidationException::withMessages([
                'end_time' => 'Ora di fine fuori dagli orari del campo: l\'ultima possibile è le '.end($points).'.',
            ]);
        }

        if ($end <= $start) {
            throw ValidationException::withMessages([
                'end_time' => 'L\'ora di fine deve venire dopo quella di inizio.',
            ]);
        }

        $data['duration'] = $end - $start;
        unset($data['end_time']);

        return $data;
    }

    private function formData(): array
    {
        return [
            'players' => Player::orderBy('nickname')->get(['id', 'nickname', 'name', 'surname']),
            'field_set' => $this->fieldSet(),
            'grids' => $this->grids(),
            'weekdays' => FixedSlot::WEEKDAYS,
        ];
    }
}
