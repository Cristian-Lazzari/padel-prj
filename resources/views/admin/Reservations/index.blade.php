@extends('layouts.ui')

@section('title', 'Prenotazioni - F+')

@section('page_vars', '--ui-cols: 130px minmax(0, 2fr) 120px 140px 132px;')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Prenotazioni</b>
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

@php
    /* Filtri e ordinamento stanno nell'indirizzo, non nel browser: l'elenco
       arriva già filtrato dal server, una pagina per volta. Prima si mandavano
       giù tutte le prenotazioni dell'archivio e si nascondevano col javascript. */
    $filtrato = $q !== '' || $status !== 'all' || $open;
@endphp

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Prenotazioni</h1>
        <div class="ui-head__count">
            @if ($filtrato)
                <span>Trovate <b>{{ $reservations->total() }}</b></span>
            @else
                <span>In tutto <b>{{ $reservations->total() }}</b></span>
            @endif
            @if ($counts['aperte'])
                <span>Partite aperte <b>{{ $counts['aperte'] }}</b></span>
            @endif
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.dashboard') }}">
            @include('admin.partials.ui-icon', ['name' => 'calendar2-week', 'size' => 16])
            <span>Vai al calendario</span>
        </a>
    </div>
</header>

<form class="ui-filters" method="GET" action="{{ route('admin.reservations.index') }}" id="filtri">
    {{-- Stato, "solo aperte" e ordinamento viaggiano nascosti: li cambiano i
         pulsanti qui sotto, che poi mandano il modulo. --}}
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="hidden" name="open" value="{{ $open ? '1' : '0' }}">

    <div class="ui-search">
        @include('admin.partials.ui-icon', ['name' => 'search', 'size' => 16])
        <label class="ui-vh" for="searchInput">Cerca per nome di chi ha prenotato o per campo</label>
        <input type="search" id="searchInput" name="q" value="{{ $q }}"
               placeholder="Cerca nome o campo..." autocomplete="off">
    </div>

    <button type="button" class="ui-chip {{ $status === 'all' ? 'is-on' : '' }}" data-ui-set="status" data-ui-value="all"
            aria-pressed="{{ $status === 'all' ? 'true' : 'false' }}">Tutte<span class="ui-chip__count">{{ $counts['tutte'] }}</span></button>
    <button type="button" class="ui-chip {{ $status === 'confirmed' ? 'is-on' : '' }}" data-ui-set="status" data-ui-value="confirmed"
            aria-pressed="{{ $status === 'confirmed' ? 'true' : 'false' }}">Confermate<span class="ui-chip__count">{{ $counts['confermate'] }}</span></button>
    <button type="button" class="ui-chip {{ $status === 'cancelled' ? 'is-on' : '' }}" data-ui-set="status" data-ui-value="cancelled"
            aria-pressed="{{ $status === 'cancelled' ? 'true' : 'false' }}">Annullate<span class="ui-chip__count">{{ $counts['annullate'] }}</span></button>
    <button type="button" class="ui-chip {{ $open ? 'is-on' : '' }}" data-ui-set="open" data-ui-value="{{ $open ? '0' : '1' }}"
            aria-pressed="{{ $open ? 'true' : 'false' }}">Solo partite aperte<span class="ui-chip__count">{{ $counts['aperte'] }}</span></button>

    <div class="ui-perpage">
        <label for="sortSelect">Ordina</label>
        <select id="sortSelect" name="sort" style="min-width:190px;">
            <option value="slot_desc"    @selected($sort === 'slot_desc')>Dalla più recente</option>
            <option value="slot_asc"     @selected($sort === 'slot_asc')>Dalla più lontana</option>
            <option value="created_desc" @selected($sort === 'created_desc')>Prenotate di recente</option>
            <option value="created_asc"  @selected($sort === 'created_asc')>Prenotate per prime</option>
        </select>
    </div>

    <div class="ui-perpage">
        <label for="perPage">Per pagina</label>
        <select id="perPage" name="per_page">
            @foreach ($per_page_opts as $n)
                <option value="{{ $n }}" @selected($per_page === $n)>{{ $n }}</option>
            @endforeach
        </select>
    </div>

    {{-- Senza javascript i filtri funzionano lo stesso: resta un pulsante --}}
    <noscript><button type="submit" class="ui-btn">Applica</button></noscript>
</form>

<div class="ui-list" role="table" aria-label="Elenco delle prenotazioni" id="reservations-list">
    @if ($reservations->isNotEmpty())
        <div class="ui-list__head" role="row">
            <span role="columnheader">Quando</span>
            <span role="columnheader">Prenotazione</span>
            <span role="columnheader">Campo</span>
            <span role="columnheader">Giocatori</span>
            <span role="columnheader" class="ui-vh">Azioni</span>
        </div>
    @endif

    @forelse ($reservations as $r)
        @php
            $datetime  = Carbon\Carbon::parse($r->date_slot)->locale('it');
            $data      = $datetime->translatedFormat('D j M');
            $ora       = $datetime->format('H:i');
            $m_during  = $field_set[$r->field]['m_during'] ?? 30;
            $ora_fine  = $datetime->copy()->addMinutes($r->duration * $m_during)->format('H:i');
            $annullata = $r->status == 0;
            $dinner    = json_decode($r->dinner, true);
            $cena      = $dinner_off && ($dinner['status'] ?? false);
            $nome      = trim($r->booking_subject_name.' '.$r->booking_subject_surname) ?: 'Senza intestatario';
            $iscritti  = (int) $r->players_count;
        @endphp

        <article class="ui-row {{ $annullata ? 'ui-row--muted' : '' }}" role="row">

            <div class="ui-cell" data-label="Quando" role="cell">
                <strong>{{ $data }}</strong>
                <span>{{ $ora }} → {{ $ora_fine }}</span>
            </div>

            <div class="ui-name" role="cell">
                <a href="{{ route('admin.reservations.show', $r) }}">{{ $nome }}</a>
                <div class="ui-name__meta">
                    @if ($annullata)
                        <span class="ui-status ui-status--cancelled">Annullata</span>
                    @else
                        <span class="ui-status ui-status--running">Confermata</span>
                    @endif

                    @if ($r->lesson == 1)
                        <span class="ui-pill">Lezione</span>
                    @elseif ($r->lesson == 0)
                        <span class="ui-pill">Partita</span>
                    @else
                        {{-- Non è il torneo: è una partita che ne fa parte --}}
                        <span class="ui-pill">Partita di torneo</span>
                    @endif

                    @if ($r->is_open)
                        <span class="ui-pill {{ $r->is_full ? 'ui-pill--accent' : 'ui-pill--warn' }}">
                            {{ $r->is_full ? 'Aperta · completa' : 'Aperta · '.$r->slots_left.' '.($r->slots_left == 1 ? 'posto' : 'posti') }}
                        </span>
                    @endif

                    @if ($r->message)
                        {{-- Si mostra solo il tratto attivo: la nota c'è, il testo si legge nel dettaglio --}}
                        <span class="ui-pill">Con nota</span>
                    @endif

                    @if ($cena)
                        <span class="ui-pill ui-pill--accent">Cena {{ $dinner['time'] }}</span>
                    @endif
                </div>
            </div>

            <div class="ui-cell" data-label="Campo" role="cell">
                <strong>{{ $r->field }}</strong>
                <span>{{ $r->duration * $m_during }} min</span>
            </div>

            <div class="ui-meter {{ $r->is_open || $iscritti ? '' : 'ui-meter--empty' }}" data-label="Giocatori" role="cell">
                @if ($r->is_open && $r->slots_total)
                    <div class="ui-meter__value">{{ $r->slots_taken }}<small>/{{ $r->slots_total }}</small></div>
                    <div class="ui-meter__track">
                        <span class="ui-meter__fill {{ $r->is_full ? 'ui-meter__fill--warn' : '' }}"
                              style="width: {{ min(100, round($r->slots_taken / max(1, $r->slots_total) * 100)) }}%"></span>
                    </div>
                @elseif ($iscritti)
                    <div class="ui-meter__value">{{ $iscritti }}<small> iscritti</small></div>
                @else
                    <div class="ui-meter__value">—</div>
                @endif
            </div>

            <div class="ui-actions" role="cell">
                @if ($cena)
                    <button type="button" class="ui-action ui-action--icon" data-bs-toggle="modal"
                            data-bs-target="#cena{{ $r->id }}" aria-label="Dettagli della cena" title="Cena">
                        @include('admin.partials.ui-icon', ['name' => 'clock-history', 'size' => 16])
                    </button>
                @endif
                <a class="ui-action ui-action--icon" href="{{ route('admin.reservations.edit', $r) }}"
                   aria-label="Modifica la prenotazione di {{ $nome }}" title="Modifica">
                    @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
                </a>
                <a class="ui-action ui-action--icon" href="{{ route('admin.reservations.show', $r) }}"
                   aria-label="Apri la prenotazione di {{ $nome }}" title="Apri">
                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 16])
                </a>
            </div>
        </article>

        @if ($cena)
            <div class="modal fade ui-modal" id="cena{{ $r->id }}" tabindex="-1"
                 aria-labelledby="cena{{ $r->id }}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <h2 id="cena{{ $r->id }}Label" style="font-size:19px;font-weight:700;margin-bottom:14px;">Cena di {{ $nome }}</h2>
                            <div class="ui-facts">
                                <div class="ui-fact">
                                    <span>Coperti</span>
                                    <strong>{{ $dinner['guests'] }}</strong>
                                </div>
                                <div class="ui-fact">
                                    <span>Orario</span>
                                    <strong>{{ $dinner['time'] }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="ui-btn" data-bs-dismiss="modal">Chiudi</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @empty
        @if ($filtrato)
            <div class="ui-empty">
                <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'search', 'size' => 25])</span>
                <h2>Nessuna prenotazione con questi filtri</h2>
                <p>Prova a cambiare stato o a cercare un altro nome.</p>
                <a class="ui-btn" href="{{ route('admin.reservations.index', ['per_page' => $per_page]) }}">Azzera i filtri</a>
            </div>
        @else
            <div class="ui-empty">
                <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'card-checklist', 'size' => 25])</span>
                <h2>Nessuna prenotazione</h2>
                <p>Quando qualcuno prenota dal sito la trovi qui. Puoi crearne una a mano dal calendario, scegliendo giorno, campo e orario.</p>
                <a class="ui-btn ui-btn--primary" href="{{ route('admin.dashboard') }}">
                    @include('admin.partials.ui-icon', ['name' => 'calendar2-week', 'size' => 16])
                    <span>Vai al calendario</span>
                </a>
            </div>
        @endif
    @endforelse
</div>

{{ $reservations->links('admin.partials.ui-pager') }}

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
        b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
    });

    const filtri = document.getElementById('filtri');
    if (!filtri) return;

    // I pulsanti scrivono nel campo nascosto e mandano il modulo: l'elenco
    // arriva filtrato dal server, così i numeri e le pagine restano veri.
    filtri.querySelectorAll('[data-ui-set]').forEach((btn) => {
        btn.addEventListener('click', () => {
            filtri.querySelector(`input[name="${btn.dataset.uiSet}"]`).value = btn.dataset.uiValue;
            filtri.submit();
        });
    });

    ['sortSelect', 'perPage'].forEach((id) => {
        document.getElementById(id)?.addEventListener('change', () => filtri.submit());
    });

    // La ricerca aspetta che si smetta di scrivere: una richiesta per parola,
    // non una per tasto.
    const search = document.getElementById('searchInput');
    let attesa = null;
    search?.addEventListener('input', () => {
        clearTimeout(attesa);
        attesa = setTimeout(() => filtri.submit(), 450);
    });
});
</script>
@endsection
