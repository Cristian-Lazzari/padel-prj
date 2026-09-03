@extends('layouts.ui')

@section('title', 'Campo fisso - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.fixed-slots.index') }}">Campi fissi</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>#{{ $fixedSlot->player?->nickname }}</b>
</nav>

@if (session('message'))
    <div class="ui-flash" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'check-circle-fill', 'size' => 20])
        <span>{{ session('message') }}</span>
        <button type="button" class="ui-flash__close" data-ui-dismiss aria-label="Chiudi avviso">
            @include('admin.partials.ui-icon', ['name' => 'x-lg', 'size' => 14])
        </button>
    </div>
@endif

@if (session('conflicts') && count(session('conflicts')))
    <div class="ui-flash ui-flash--warn" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <div>
            <strong>{{ count(session('conflicts')) }} occorrenze non generate</strong>: il campo risultava già
            prenotato in quelle date. Vanno risolte a mano dal calendario.
            <ul>
                @foreach (session('conflicts') as $c)
                    <li>{{ $c['date_slot'] }} — prenotazione #{{ $c['reservation_id'] }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Campo fisso di #{{ $fixedSlot->player?->nickname }}</h1>
        <div class="ui-head__count">
            <span class="ui-status ui-status--{{ $fixedSlot->status === 'active' ? 'running' : ($fixedSlot->status === 'suspended' ? 'closed' : 'finished') }}">
                {{ $fixedSlot->statusLabel() }}
            </span>
            <span>{{ $fixedSlot->weekdayLabel() }} {{ $fixedSlot->start_time }} → {{ $fixedSlot->endTime($minutes) }}</span>
            <span>Campo <b>{{ $fixedSlot->field }}</b></span>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.fixed-slots.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna all'elenco</span>
        </a>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.fixed-slots.edit', $fixedSlot) }}">
            @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
            <span>Modifica</span>
        </a>
    </div>
</header>

<div class="ui-facts">
    <div class="ui-fact">
        <span>Giocatore</span>
        <strong>
            <a href="{{ route('admin.players.show', $fixedSlot->player_id) }}">
                {{ $fixedSlot->player?->name }} {{ $fixedSlot->player?->surname }}
            </a>
        </strong>
        <small>#{{ $fixedSlot->player?->nickname }}</small>
    </div>
    <div class="ui-fact">
        <span>Valido dal</span>
        <strong>{{ $fixedSlot->valid_from?->format('d/m/Y') }}</strong>
        <small>{{ $fixedSlot->valid_to ? 'fino al '.$fixedSlot->valid_to->format('d/m/Y') : 'a tempo indeterminato' }}</small>
    </div>
    <div class="ui-fact">
        <span>Quota</span>
        <strong>{{ $fixedSlot->price ? number_format((float) $fixedSlot->price, 2, ',', '.').' €' : '—' }}</strong>
        <small>Si salda in struttura</small>
    </div>
    <div class="ui-fact">
        <span>Creato da</span>
        <strong>{{ $fixedSlot->creator?->name ?: '—' }}</strong>
    </div>
</div>

@if ($fixedSlot->note)
    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Note</h2></div>
        <p>{{ $fixedSlot->note }}</p>
    </section>
@endif

<div class="ui-split">
    <div class="ui-split__main">

        {{-- ============ Eccezioni ============ --}}
        <section class="ui-section">
            <div class="ui-section__head">
                <h2>Eccezioni</h2>
                <div class="ui-section__meta"><span class="ui-pill">{{ $fixedSlot->exceptions->count() }} date saltate</span></div>
            </div>

            @if ($fixedSlot->exceptions->count())
                <div class="ui-list" role="table" aria-label="Date saltate"
                     style="--ui-cols: minmax(0, 1.4fr) 150px minmax(0, 1fr) 110px;">
                    <div class="ui-list__head" role="row">
                        <span role="columnheader">Data</span>
                        <span role="columnheader">Motivo</span>
                        <span role="columnheader">Nota</span>
                        <span role="columnheader" class="ui-vh">Azioni</span>
                    </div>
                    @foreach ($fixedSlot->exceptions as $e)
                        <article class="ui-row" role="row">
                            <div class="ui-name" role="cell">
                                <span class="ui-name__title">{{ $e->date->locale('it')->translatedFormat('D j M Y') }}</span>
                            </div>
                            <div class="ui-cell" data-label="Motivo" role="cell">
                                <strong><span class="ui-pill {{ $e->reason === 'recupero' ? 'ui-pill--accent' : ($e->reason === 'sospensione' ? 'ui-pill--warn' : '') }}">{{ $e->reasonLabel() }}</span></strong>
                            </div>
                            <div class="ui-cell" data-label="Nota" role="cell">
                                <strong style="font-weight:500;">{{ $e->note ?: '—' }}</strong>
                            </div>
                            <div class="ui-actions" role="cell">
                                <form action="{{ route('admin.fixed-slots.exceptions.destroy', ['fixedSlot' => $fixedSlot, 'exception' => $e]) }}"
                                      method="post" onsubmit="return confirm('Rimuovere questa eccezione? La ricorrenza tornerà attiva.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ui-action ui-action--danger"
                                            aria-label="Rimuovi l'eccezione del {{ $e->date->format('d/m/Y') }}">Rimuovi</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="ui-hint">Nessuna eccezione: la ricorrenza vale per tutte le date.</p>
            @endif

            <section class="ui-panel">
                <div class="ui-panel__head"><h2>Salta una data</h2></div>
                <form action="{{ route('admin.fixed-slots.exceptions.store', $fixedSlot) }}" method="post">
                    @csrf
                    <div class="ui-fields ui-fields--2">
                        <div class="ui-field">
                            <label for="date">Data <b>*</b></label>
                            <input type="date" name="date" id="date" required>
                        </div>
                        <div class="ui-field">
                            <label for="reason">Motivo</label>
                            <select name="reason" id="reason">
                                <option value="sospensione">Sospensione</option>
                                <option value="festivo">Festivo</option>
                                <option value="recupero">Recupero</option>
                            </select>
                        </div>
                        <div class="ui-field">
                            <label for="exception_note">Nota</label>
                            <input type="text" name="note" id="exception_note" placeholder="Facoltativa">
                        </div>
                    </div>
                    <div>
                        <button class="ui-btn" type="submit">
                            @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                            <span>Salta questa data</span>
                        </button>
                    </div>
                </form>
            </section>
        </section>

        {{-- ============ Prossime occorrenze ============ --}}
        <section class="ui-section">
            <div class="ui-section__head">
                <h2>Date in calendario</h2>
                <div class="ui-section__meta"><span class="ui-pill ui-pill--accent">{{ $upcoming->count() }} da qui alla fine</span></div>
            </div>

            @if ($upcoming->count())
                <details class="ui-data" open>
                    <summary>
                        <span>Tutte le date del campo fisso</span>
                        @include('admin.partials.ui-icon', ['name' => 'chevron-down', 'size' => 16])
                    </summary>
                    <div class="ui-data__scroll">
                        <table>
                            <thead>
                                <tr><th>Data</th><th>Orario</th><th>Prenotazione</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($upcoming as $u)
                                    <tr>
                                        <td>{{ $u['date']->locale('it')->translatedFormat('D j M Y') }}</td>
                                        <td>{{ $fixedSlot->start_time }} – {{ $fixedSlot->endTime($minutes) }}</td>
                                        {{-- Se manca, quel giorno il campo era già occupato da qualcun altro --}}
                                        <td>{{ $u['materialized'] ? 'In calendario' : 'Non creata: campo occupato' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
                <p class="ui-hint">
                    Sono tutte le date che restano, fino al
                    {{ $fixedSlot->valid_to?->format('d/m/Y') ?: 'termine della validità' }}:
                    le prenotazioni sono già in calendario, create quando hai salvato il campo fisso.
                </p>
            @else
                <p class="ui-hint">Nessuna data in programma: la validità è finita, oppure ogni ricorrenza ha un'eccezione.</p>
            @endif
        </section>
    </div>

    <aside class="ui-split__side">
        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Stato</h2></div>
            <div style="display:grid; gap:10px;">
                @foreach (['active' => ['Riattiva', ''], 'suspended' => ['Sospendi', ''], 'ended' => ['Chiudi definitivamente', 'ui-btn--danger']] as $status => $meta)
                    @continue($fixedSlot->status === $status)
                    <form action="{{ route('admin.fixed-slots.status', $fixedSlot) }}" method="post">
                        @csrf
                        <input type="hidden" name="status" value="{{ $status }}">
                        <button class="ui-btn {{ $meta[1] }}" style="width:100%;" type="submit">{{ $meta[0] }}</button>
                    </form>
                @endforeach
            </div>
            <p class="ui-hint">Sospendendo o chiudendo, le occorrenze future già generate vengono eliminate e il campo torna prenotabile.</p>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Zona pericolosa</h2></div>
            <p class="ui-hint">Le occorrenze future vengono liberate. Quelle passate restano in archivio.</p>
            <button class="ui-btn ui-btn--danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteFixedSlot">
                @include('admin.partials.ui-icon', ['name' => 'trash3-fill', 'size' => 16])
                <span>Elimina campo fisso</span>
            </button>
        </section>
    </aside>
</div>

<div class="modal fade ui-modal" id="deleteFixedSlot" tabindex="-1" aria-labelledby="deleteFixedSlotLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <h2 id="deleteFixedSlotLabel" style="font-size:19px;font-weight:700;margin-bottom:10px;">Eliminare questo campo fisso?</h2>
                <p class="ui-hint">Le occorrenze future verranno liberate. Quelle già passate restano in archivio.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="ui-btn" data-bs-dismiss="modal">Lascia com'è</button>
                <form action="{{ route('admin.fixed-slots.destroy', $fixedSlot) }}" method="post">
                    @method('DELETE')
                    @csrf
                    <button class="ui-btn ui-btn--danger" type="submit">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
    b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
});
</script>
@endsection
