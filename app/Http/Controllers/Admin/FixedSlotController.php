<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FixedSlot;
use App\Models\FixedSlotException;
use App\Models\Player;
use App\Models\Setting;
use App\Services\FixedSlotService;
use Illuminate\Http\Request;

/**
 * Gestione dei campi fissi dal back office.
 *
 * Ogni modifica rigenera le occorrenze future: le prenotazioni già
 * materializzate vengono cancellate e ricreate dal servizio.
 */
class FixedSlotController extends Controller
{
    public function __construct(private FixedSlotService $slots)
    {
    }

    private function rules(): array
    {
        return [
            'player_id' => 'required|exists:players,id',
            'field' => 'required|string|max:255',
            'weekday' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:1|max:12',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
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
        $data = $request->validate($this->rules());
        $data['created_by'] = auth()->id();

        $slot = FixedSlot::create($data);

        $result = $this->slots->refresh($slot);

        return to_route('admin.fixed-slots.show', $slot)
            ->with('message', 'Campo fisso creato: '.$result['created'].' occorrenze generate.')
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

        $upcoming = collect($this->slots->occurrences(
            $fixedSlot,
            now()->startOfDay(),
            now()->startOfDay()->addWeeks(FixedSlotService::HORIZON_WEEKS)
        ))->map(fn ($date) => [
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
        $data = $request->validate($this->rules());

        $fixedSlot->update($data);

        $result = $this->slots->refresh($fixedSlot);

        return to_route('admin.fixed-slots.show', $fixedSlot)
            ->with('message', 'Campo fisso aggiornato: '.$result['created'].' occorrenze rigenerate.')
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
            'active' => 'Campo fisso riattivato: '.$result['created'].' occorrenze rigenerate.',
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
        $setting = Setting::where('name', 'advanced')->first();

        return $setting ? (json_decode($setting->property, true)['field_set'] ?? []) : [];
    }

    private function formData(): array
    {
        return [
            'players' => Player::orderBy('nickname')->get(['id', 'nickname', 'name', 'surname']),
            'field_set' => $this->fieldSet(),
            'weekdays' => FixedSlot::WEEKDAYS,
        ];
    }
}
