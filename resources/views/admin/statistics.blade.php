@extends('layouts.ui')

@section('title', 'Statistiche - F+')

@section('contents')

@php
    $etichette = array_column($buckets, 'label');
    $estese    = array_column($buckets, 'full');

    $passo = [
        'day'   => ['Giorno', 'un punto al giorno'],
        'week'  => ['Settimana', 'un punto a settimana'],
        'month' => ['Mese', 'un punto al mese'],
    ][$granularity];

    $prenotazioni = array_sum($totals);
    $iscritti     = $tournaments->sum('confirmed_registrations_count');
    $attesa       = $tournaments->sum('waitlist_registrations_count');

    /* Le serie dei campi: oltre il quinto la coda diventa "Altri campi", perché
       un sesto colore non si distinguerebbe più dai precedenti. */
    $campiInGrafico = count($fields) > 5 ? array_slice($fields, 0, 4) : $fields;
    $campiInCoda    = count($fields) > 5 ? array_slice($fields, 4) : [];

    /* Nel grafico i campi restano nell'ordine delle impostazioni, non in quello
       della classifica: cambiando periodo la pila si rimescolerebbe sotto gli occhi. */
    usort($campiInGrafico, fn ($a, $b) => $a['slot'] <=> $b['slot']);

    $serieCampi = [];
    foreach ($campiInGrafico as $campo) {
        $serieCampi[] = [
            'name'  => $campo['name'],
            'color' => 'var(--viz-'.$campo['slot'].')',
            'data'  => $campo['hours'],
        ];
    }
    if (count($campiInCoda)) {
        $somma = array_fill(0, count($buckets), 0);
        foreach ($campiInCoda as $campo) {
            foreach ($campo['hours'] as $i => $ore) {
                $somma[$i] = round($somma[$i] + $ore, 1);
            }
        }
        $serieCampi[] = ['name' => 'Altri campi', 'color' => 'var(--viz-5)', 'data' => $somma];
    }

    $giorni = [1 => 'Lunedì', 2 => 'Martedì', 3 => 'Mercoledì', 4 => 'Giovedì', 5 => 'Venerdì', 6 => 'Sabato', 7 => 'Domenica'];

    $vuoto = $prenotazioni === 0 && $tournaments->isEmpty();
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Statistiche</b>
</nav>

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Statistiche</h1>
        <div class="ui-head__count">
            <span>Dal <b>{{ $from->locale('it')->translatedFormat('j M Y') }}</b> al <b>{{ $to->locale('it')->translatedFormat('j M Y') }}</b></span>
            <span>{{ ucfirst($passo[1]) }}</span>
        </div>
    </div>
</header>

{{-- Una sola barra di filtri, sopra tutto: ogni numero della pagina parla dello
     stesso intervallo, così i totali non si contraddicono mai fra loro. --}}
<form class="ui-filters" method="GET" action="{{ route('admin.statistics') }}">
    @foreach ($presets as $valore => $testo)
        <button type="submit" name="period" value="{{ $valore }}"
                class="ui-chip {{ $preset === $valore ? 'is-on' : '' }}"
                @if ($preset === $valore) aria-current="true" @endif>{{ $testo }}</button>
    @endforeach

    <details class="stat-range" @if ($preset === 'custom') open @endif>
        <summary>
            @include('admin.partials.ui-icon', ['name' => 'calendar-check', 'size' => 14])
            <span>Periodo scelto</span>
        </summary>
        <div class="stat-range__body">
            <label class="ui-vh" for="from">Dal giorno</label>
            <input type="date" id="from" name="from" value="{{ $from->format('Y-m-d') }}">
            <label class="ui-vh" for="to">Al giorno</label>
            <input type="date" id="to" name="to" value="{{ $to->format('Y-m-d') }}">
            {{-- period viaggia sul bottone premuto: un campo nascosto vincerebbe
                 anche sulle pastiglie qui sopra, che stanno nello stesso modulo --}}
            <button type="submit" name="period" value="custom" class="ui-btn ui-btn--primary">Mostra</button>
        </div>
    </details>
</form>

@if ($vuoto)
    <div class="ui-empty">
        @include('admin.partials.ui-icon', ['name' => 'bar-chart', 'size' => 30])
        <h2>Nessun dato in questo periodo</h2>
        <p>Non risultano prenotazioni né tornei fra le date scelte. Prova ad allargare l'intervallo: con un anno intero l'andamento si legge meglio.</p>
        <a class="ui-btn" href="{{ route('admin.statistics', ['period' => '12m']) }}">Guarda gli ultimi 12 mesi</a>
    </div>
@else

<div class="ui-facts">
    <div class="ui-fact ui-fact--lead">
        <span>Prenotazioni</span>
        <strong>{{ number_format($prenotazioni, 0, ',', '.') }}</strong>
        <small>campi, lezioni e tornei insieme</small>
    </div>
    <div class="ui-fact">
        <span>Ore di campo</span>
        <strong>{{ number_format($hoursTotal, 0, ',', '.') }} h</strong>
        <small>tempo effettivamente giocato</small>
    </div>
    <div class="ui-fact">
        <span>Occupazione</span>
        <strong>{{ $occupancy }}%</strong>
        <small>sulle ore di apertura</small>
    </div>
    <div class="ui-fact">
        <span>Lezioni</span>
        <strong>{{ number_format($totals['lesson'], 0, ',', '.') }}</strong>
        <small>{{ $prenotazioni ? round($totals['lesson'] / $prenotazioni * 100) : 0 }}% delle prenotazioni</small>
    </div>
    <div class="ui-fact">
        <span>Iscritti ai tornei</span>
        <strong>{{ number_format($iscritti, 0, ',', '.') }}</strong>
        <small>{{ $tournaments->count() }} {{ $tournaments->count() === 1 ? 'torneo' : 'tornei' }} nel periodo</small>
    </div>
    <div class="ui-fact">
        <span>Nuovi giocatori</span>
        <strong>{{ number_format($newPlayers, 0, ',', '.') }}</strong>
        <small>iscritti al circolo nel periodo</small>
    </div>
</div>

<section class="ui-panel">
    <div class="ui-panel__head">
        <h2>Campi, lezioni e tornei nel tempo</h2>
        <span class="ui-panel__note">prenotazioni · {{ $passo[1] }}</span>
    </div>

    @include('admin.partials.ui-chart', [
        'id'       => 'chartAndamento',
        'type'     => 'line',
        'labels'   => $etichette,
        'full'     => $estese,
        'axis'     => $passo[0],
        'caption'  => 'Prenotazioni di campi, lezioni e tornei',
        'series'   => [
            ['name' => 'Campi',   'color' => 'var(--viz-1)', 'data' => $trend['match']],
            ['name' => 'Lezioni', 'color' => 'var(--viz-2)', 'data' => $trend['lesson']],
            ['name' => 'Tornei',  'color' => 'var(--viz-3)', 'data' => $trend['tournament']],
        ],
    ])

    <p class="ui-hint">
        Ogni prenotazione conta una volta: le lezioni sono le fasce assegnate a un istruttore,
        i tornei sono le partite giocate dentro un torneo, i campi sono tutto il resto.
    </p>
</section>

<section class="ui-panel">
    <div class="ui-panel__head">
        <h2>Ore prenotate per campo</h2>
        <span class="ui-panel__note">ore · {{ $passo[1] }}</span>
    </div>

    @include('admin.partials.ui-chart', [
        'id'       => 'chartCampi',
        'type'     => 'stack',
        'labels'   => $etichette,
        'full'     => $estese,
        'unit'     => ' h',
        'decimals' => 1,
        'axis'     => $passo[0],
        'caption'  => 'Ore prenotate per campo',
        'series'   => $serieCampi,
    ])

    @if (count($fields))
        <div class="ui-list" role="table" aria-label="Ore e occupazione campo per campo" style="--ui-cols: minmax(0, 1.6fr) 150px 150px;">
            <div class="ui-list__head" role="row">
                <span role="columnheader">Campo</span>
                <span role="columnheader">Ore prenotate</span>
                <span role="columnheader">Occupazione</span>
            </div>
            @foreach ($fields as $campo)
                <div class="ui-row" role="row">
                    <div class="ui-name" role="cell">
                        <span class="ui-name__title">
                            <i class="stat-dot" style="--k: var(--viz-{{ $campo['slot'] }})" aria-hidden="true"></i>
                            {{ $campo['name'] }}
                        </span>
                        <span class="ui-name__meta">{{ number_format($campo['capacity'], 0, ',', '.') }} h di apertura nel periodo</span>
                    </div>
                    <div class="ui-cell" data-label="Ore prenotate" role="cell">
                        <strong>{{ number_format($campo['booked'], 1, ',', '.') }} h</strong>
                    </div>
                    <div class="ui-meter" data-label="Occupazione" role="cell">
                        {{-- Sotto l'1% l'arrotondamento direbbe "zero" anche con ore giocate --}}
                        <div class="ui-meter__value">{{ $campo['rate'] > 0 ? $campo['rate'] : ($campo['booked'] > 0 ? '<1' : '0') }}<small>%</small></div>
                        <div class="ui-meter__track">
                            <span class="ui-meter__fill" style="width: {{ $campo['booked'] > 0 ? max(2, min(100, $campo['rate'])) : 0 }}%"></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="ui-hint">
            L'occupazione confronta le ore prenotate con le ore di apertura del campo,
            togliendo i giorni di chiusura del circolo e quelli chiusi per quel campo.
        </p>
    @endif
</section>

<section class="ui-panel">
    <div class="ui-panel__head">
        <h2>Iscritti ai tornei</h2>
        <span class="ui-panel__note">per {{ Str::lower($passo[0]) }} di svolgimento</span>
    </div>

    @include('admin.partials.ui-chart', [
        'id'      => 'chartTornei',
        'type'    => 'stack',
        'labels'  => $etichette,
        'full'    => $estese,
        'axis'    => $passo[0],
        'caption' => 'Iscritti ai tornei',
        'series'  => [
            ['name' => 'Confermati',      'color' => 'var(--viz-1)', 'data' => $tournamentSeries['confirmed']],
            ['name' => "Lista d'attesa",  'color' => 'var(--viz-2)', 'data' => $tournamentSeries['waitlist']],
        ],
    ])

    @if ($tournaments->isNotEmpty())
        <div class="ui-list" role="table" aria-label="Tornei del periodo" style="--ui-cols: minmax(0, 2fr) 150px 160px;">
            <div class="ui-list__head" role="row">
                <span role="columnheader">Torneo</span>
                <span role="columnheader">Quando</span>
                <span role="columnheader">Riempimento</span>
            </div>
            @foreach ($tournaments as $torneo)
                @php
                    $posti  = max(1, (int) $torneo->teams_max);
                    $pieno  = min(100, (int) round($torneo->confirmed_registrations_count / $posti * 100));
                @endphp
                <div class="ui-row" role="row">
                    <div class="ui-name" role="cell">
                        <a href="{{ route('admin.tournaments.show', $torneo) }}">{{ $torneo->name }}</a>
                        <span class="ui-name__meta">
                            {{ ucfirst($torneo->format) }}
                            @if ($torneo->waitlist_registrations_count)
                                · {{ $torneo->waitlist_registrations_count }} in lista d'attesa
                            @endif
                        </span>
                    </div>
                    <div class="ui-cell" data-label="Quando" role="cell">
                        <strong>{{ $torneo->starts_at->format('d/m/Y') }}</strong>
                        <span>{{ $torneo->statusLabel() }}</span>
                    </div>
                    <div class="ui-meter" data-label="Riempimento" role="cell">
                        <div class="ui-meter__value">{{ $torneo->confirmed_registrations_count }}<small>/{{ $torneo->teams_max }}</small></div>
                        <div class="ui-meter__track">
                            <span class="ui-meter__fill {{ $pieno >= 100 ? 'ui-meter__fill--warn' : '' }}" style="width: {{ $pieno }}%"></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<div class="stat-duo">
    <section class="ui-panel">
        <div class="ui-panel__head">
            <h2>Le ore più richieste</h2>
            <span class="ui-panel__note">prenotazioni per orario di inizio</span>
        </div>

        @include('admin.partials.ui-chart', [
            'id'      => 'chartOre',
            'type'    => 'bars',
            'labels'  => array_keys($byHour),
            'full'    => array_map(fn ($h) => 'Alle '.$h.':00', array_keys($byHour)),
            'axis'    => 'Ora',
            'caption' => 'Prenotazioni per orario di inizio',
            'series'  => [['name' => 'Prenotazioni', 'color' => 'var(--viz-1)', 'data' => array_values($byHour)]],
        ])
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head">
            <h2>I giorni più pieni</h2>
            <span class="ui-panel__note">prenotazioni per giorno della settimana</span>
        </div>

        @include('admin.partials.ui-chart', [
            'id'      => 'chartGiorni',
            'type'    => 'bars',
            'labels'  => array_map(fn ($g) => Str::substr($g, 0, 3), array_values($giorni)),
            'full'    => array_values($giorni),
            'axis'    => 'Giorno',
            'caption' => 'Prenotazioni per giorno della settimana',
            'series'  => [['name' => 'Prenotazioni', 'color' => 'var(--viz-1)', 'data' => array_values($byWeekday)]],
        ])
    </section>
</div>

@if ($topPlayers->isNotEmpty())
    <section class="ui-panel">
        <div class="ui-panel__head">
            <h2>Chi prenota di più</h2>
            <span class="ui-panel__note">primi {{ $topPlayers->count() }} nel periodo</span>
        </div>

        @php $vetta = max(1, (int) $topPlayers->max('n')); @endphp

        <div class="ui-list" role="table" aria-label="Giocatori che hanno prenotato di più" style="--ui-cols: minmax(0, 2fr) 110px 170px;">
            <div class="ui-list__head" role="row">
                <span role="columnheader">Giocatore</span>
                <span role="columnheader">Livello</span>
                <span role="columnheader">Prenotazioni</span>
            </div>
            @foreach ($topPlayers as $giocatore)
                <div class="ui-row" role="row">
                    <div class="ui-name ui-name--media" role="cell">
                        <span class="ui-avatar" aria-hidden="true">{{ Str::upper(Str::substr($giocatore->name, 0, 1).Str::substr($giocatore->surname, 0, 1)) }}</span>
                        <span class="ui-name__body">
                            <a href="{{ route('admin.players.show', $giocatore->id) }}">{{ $giocatore->name }} {{ $giocatore->surname }}</a>
                            <span class="ui-name__meta">{{ $giocatore->nickname }}</span>
                        </span>
                    </div>
                    <div class="ui-cell" data-label="Livello" role="cell">
                        <strong>{{ $giocatore->level }}</strong>
                    </div>
                    <div class="ui-meter" data-label="Prenotazioni" role="cell">
                        <div class="ui-meter__value">{{ $giocatore->n }}</div>
                        <div class="ui-meter__track">
                            <span class="ui-meter__fill" style="width: {{ round($giocatore->n / $vetta * 100) }}%"></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif

@endif

@endsection

@section('styles')
<style>
/* Due pannelli affiancati quando lo schermo lo permette: sono due letture
   della stessa domanda ("quando si gioca") e stanno bene una accanto all'altra. */
.stat-duo{ display: grid; gap: 18px; }
@media (min-width: 1000px){
    .stat-duo{ grid-template-columns: 1fr 1fr; }
}

/* Il periodo su misura vive dentro la barra dei filtri, chiuso finché non serve */
.stat-range{ align-self: center; }
/* Aperto prende una riga tutta sua: incastrato fra le pastiglie
   sposterebbe in basso l'intera barra dei filtri */
.stat-range[open]{ flex: 1 1 100%; }
.stat-range > summary{
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 38px;
    padding: 0 16px;
    border-radius: var(--ui-pill);
    background: var(--ui-surface);
    color: var(--ui-ink-soft);
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    list-style: none;
}
.stat-range > summary::-webkit-details-marker{ display: none; }
.stat-range > summary:hover{ background: var(--ui-surface-2); color: var(--ui-ink); }
.stat-range[open] > summary{ background: var(--ui-accent-dim); color: var(--ui-accent); }
.stat-range__body{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
.stat-range__body input{ flex: 0 1 180px; min-height: 42px; }

/* Pastiglia di colore accanto al nome del campo: rimanda alla serie del grafico */
.stat-dot{
    display: inline-block;
    width: 10px; height: 10px;
    margin-right: 4px;
    border-radius: 3px;
    background: var(--k);
    vertical-align: baseline;
}
</style>
@endsection
