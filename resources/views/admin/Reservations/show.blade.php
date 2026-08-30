@extends('layouts.ui')

@section('title', 'Prenotazione - F+')

@section('contents')

@php
    $datetime = Carbon\Carbon::parse($reservation->date_slot)->locale('it');
    $data     = $datetime->translatedFormat('l j F');
    $ora      = $datetime->format('H:i');
    $ora_fine = $datetime->copy()->addMinutes($m_during * $reservation->duration)->format('H:i');

    $dinner = json_decode($reservation->dinner, true);
    $tipo   = [0 => 'Partita', 1 => 'Lezione', 2 => 'Torneo'][$reservation->lesson ?? 0] ?? 'Partita';
    $annullata = $reservation->status == 0;
    $intestatario = trim($reservation->booking_subject_name.' '.$reservation->booking_subject_surname);
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.reservations.index') }}">Prenotazioni</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>{{ $intestatario ?: 'Dettaglio' }}</b>
</nav>

@if (session('message'))
    <div class="ui-flash" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'check-circle-fill', 'size' => 20])
        <span>{{ session('message') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>{{ $tipo }} di {{ $intestatario ?: 'ospite' }}</h1>
        <div class="ui-head__count">
            <span>{{ ucfirst($data) }}</span>
            <span>{{ $ora }} → {{ $ora_fine }}</span>
            <span>Campo <b>{{ $reservation->field }}</b></span>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.reservations.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna all'elenco</span>
        </a>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.reservations.edit', $reservation) }}">
            @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
            <span>Modifica</span>
        </a>
    </div>
</header>

<div class="ui-split">
    <div class="ui-split__main">

        {{-- Partita aperta: sta in cima perché è l'unica parte che richiede una decisione --}}
        @if ($reservation->is_open)
            @php $cat_labels = ['match' => 'Partita', 'lesson' => 'Lezione', 'tournament' => 'Torneo']; @endphp
            <section class="ui-panel">
                <div class="ui-panel__head">
                    <h2>Partita aperta</h2>
                    <span class="ui-panel__note">{{ $reservation->slots_taken }}/{{ $reservation->slots_total }} posti</span>
                </div>
                <div class="ui-facts">
                    <div class="ui-fact">
                        <span>Categoria</span>
                        <strong>{{ $cat_labels[$reservation->open_category] ?? 'Partita' }}</strong>
                    </div>
                    <div class="ui-fact">
                        <span>Livello richiesto</span>
                        <strong>{{ $reservation->levelLabel() }}</strong>
                    </div>
                    <div class="ui-fact">
                        <span>Iscrizioni fino al</span>
                        <strong>{{ $reservation->open_closes_at ? $reservation->open_closes_at->format('d/m/Y H:i') : 'inizio slot' }}</strong>
                    </div>
                </div>
                @if ($reservation->open_note)
                    <p class="ui-hint">{{ $reservation->open_note }}</p>
                @endif
                <form action="{{ route('admin.reservations.close_open', $reservation->id) }}" method="post">
                    @csrf
                    <button class="ui-btn ui-btn--danger" type="submit">Chiudi le iscrizioni</button>
                </form>
            </section>
        @endif

        <section class="ui-panel">
            <div class="ui-panel__head">
                <h2>Giocatori</h2>
                <span class="ui-panel__note">{{ $reservation->players->count() }} in elenco</span>
            </div>

            @forelse ($reservation->players as $p)
                @php
                    $join_labels = [
                        'accepted'  => ['ui-pill--accent', 'Iscritto'],
                        'pending'   => ['ui-pill--warn',   'In attesa'],
                        'rejected'  => ['ui-pill--danger', 'Rifiutato'],
                        'cancelled' => ['ui-pill--danger', 'Annullato'],
                    ];
                    $join = $join_labels[$p->pivot->join_status] ?? ['ui-pill--accent', 'Iscritto'];
                @endphp
                <div class="ui-row" style="padding: 14px 18px;">
                    <div class="ui-name">
                        <a href="{{ route('admin.players.show', $p) }}">#{{ $p->nickname }}</a>
                        <div class="ui-name__meta">
                            <span>{{ $p->name }} {{ $p->surname }}</span>
                            <span class="ui-pill {{ $join[0] }}">{{ $join[1] }}</span>
                            @if ($p->pivot->is_owner)
                                <span class="ui-pill">Organizzatore</span>
                            @endif
                            <span class="ui-code">livello {{ $p->level }}</span>
                        </div>
                    </div>
                    <div class="ui-actions">
                        <a class="ui-action ui-action--icon" href="{{ route('admin.players.show', $p) }}"
                           aria-label="Scheda di {{ $p->nickname }}" title="Scheda">
                            @include('admin.partials.ui-icon', ['name' => 'eye-fill', 'size' => 16])
                        </a>
                        <form action="{{ route('admin.reservations.participants.destroy', ['id' => $reservation->id, 'playerId' => $p->id]) }}"
                              method="post" onsubmit="return confirm('Rimuovere #{{ $p->nickname }} da questa prenotazione?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ui-action ui-action--icon ui-action--danger"
                                    aria-label="Rimuovi {{ $p->nickname }} dalla prenotazione" title="Rimuovi">
                                @include('admin.partials.ui-icon', ['name' => 'trash3-fill', 'size' => 16])
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="ui-hint">Nessun giocatore associato a questa prenotazione.</p>
            @endforelse

            <form class="ui-field" action="{{ route('admin.reservations.participants.store', $reservation->id) }}" method="post">
                @csrf
                <label for="player_id">Aggiungi un partecipante</label>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <select name="player_id" id="player_id" required style="flex:1 1 220px;">
                        <option value="">Seleziona un giocatore</option>
                        @foreach ($available_players as $ap)
                            <option value="{{ $ap->id }}">#{{ $ap->nickname }} — {{ $ap->name }} {{ $ap->surname }} (liv. {{ $ap->level }})</option>
                        @endforeach
                    </select>
                    <button class="ui-btn" type="submit">Aggiungi</button>
                </div>
                @error('player_id') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </form>
        </section>

        @if ($reservation->message)
            <section class="ui-panel">
                <div class="ui-panel__head"><h2>Nota di chi ha prenotato</h2></div>
                <p>{{ $reservation->message }}</p>
            </section>
        @endif
    </div>

    <aside class="ui-split__side">
        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Stato</h2></div>
            <span class="ui-status {{ $annullata ? 'ui-status--cancelled' : 'ui-status--running' }}">
                {{ $annullata ? 'Annullata' : 'Confermata' }}
            </span>

            <div class="ui-facts" style="grid-template-columns: 1fr;">
                <div class="ui-fact">
                    <span>Prenotato da</span>
                    <strong>
                        @if ($reservation->booking_subject)
                            <a href="{{ route('admin.players.show', $reservation->booking_subject) }}">{{ $intestatario ?: 'Ospite' }}</a>
                        @else
                            {{ $intestatario ?: 'Ospite' }}
                        @endif
                    </strong>
                </div>
                @if ($dinner_off)
                    <div class="ui-fact">
                        <span>Cena</span>
                        @if ($dinner['status'] ?? false)
                            <strong>{{ $dinner['guests'] }} coperti alle {{ $dinner['time'] }}</strong>
                        @else
                            <strong>Non prenotata</strong>
                        @endif
                    </div>
                @endif
            </div>

            @if (! $annullata)
                <button class="ui-btn ui-btn--danger" type="button" data-bs-toggle="modal" data-bs-target="#annullaPrenotazione">
                    @include('admin.partials.ui-icon', ['name' => 'ban', 'size' => 16])
                    <span>Annulla {{ Str::lower($tipo) }}</span>
                </button>
            @endif
        </section>

        <p class="ui-panel__note" style="padding: 0 6px;">
            Creata il {{ $reservation->created_at->format('d/m/Y H:i') }} ·
            aggiornata il {{ $reservation->updated_at->format('d/m/Y H:i') }} ·
            ID {{ $reservation->id }}
        </p>
    </aside>
</div>

@if (! $annullata)
    <div class="modal fade ui-modal" id="annullaPrenotazione" tabindex="-1" aria-labelledby="annullaPrenotazioneLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <h2 id="annullaPrenotazioneLabel" style="font-size:19px;font-weight:700;margin-bottom:10px;">
                        Annullare questa prenotazione?
                    </h2>
                    <p class="ui-hint">
                        Annullando da qui parte automaticamente la mail di disdetta a
                        {{ $intestatario ?: 'chi ha prenotato' }}.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ui-btn" data-bs-dismiss="modal">Lascia com'è</button>
                    <form action="{{ route('admin.reservations.cancel') }}" method="post">
                        @csrf
                        <input value="{{ $reservation->id }}" type="hidden" name="id">
                        <button class="ui-btn ui-btn--danger" type="submit">Annulla la prenotazione</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
