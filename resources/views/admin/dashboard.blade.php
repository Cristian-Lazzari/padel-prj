@extends('layouts.ui')

@section('title', 'Calendario - F+')

@section('styles')
@include('admin.partials.ui-calendar')
@endsection

@section('contents')

@php
    $currentDay   = date('d');
    $currentMonth = date('m');
    $currentYear  = date('Y');
    $mesi = ['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno',
             'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];
    $giorni = ['lun' => 'edì', 'mar' => 'tedì', 'mer' => 'coledì', 'gio' => 'vedì',
               'ven' => 'erdì', 'sab' => 'ato', 'dom' => 'enica'];

    /* Tornei per giorno: un torneo occupa tutti i giorni fra inizio e fine, e i
       campi che ha dichiarato. Serve al segno sul mese e alla banda del giorno. */
    $tourneiPerGiorno = [];
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
            $tourneiPerGiorno[$giorno][] = $dati;
        }
    }

    /* I tipi di prenotazione: un'icona e un colore soli, usati nel riquadro del
       giorno, nella legenda e nelle fasce orarie. Le icone si disegnano una volta
       e non a ogni cella: il calendario ha centinaia di giorni e un @include per
       ciascuno costerebbe caro. */
    $tipi = [
        'match'      => ['icona' => 'circle-fill',      'uno' => 'prenotazione su campo', 'tanti' => 'prenotazioni su campo', 'legenda' => 'Campo prenotato'],
        'lesson'     => ['icona' => 'mortarboard-fill', 'uno' => 'lezione',               'tanti' => 'lezioni',               'legenda' => 'Lezione'],
        'tournament' => ['icona' => 'trophy-fill',      'uno' => 'partita di torneo',     'tanti' => 'partite di torneo',     'legenda' => 'Partita di torneo'],
        'dinner'     => ['icona' => 'cup-hot-fill',     'uno' => 'cena',                  'tanti' => 'cene',                  'legenda' => 'Cena'],
    ];

    $segni = [];
    foreach ($tipi as $chiave => $tipo) {
        $segni[$chiave] = view('admin.partials.ui-icon', ['name' => $tipo['icona'], 'size' => 11])->render();
    }

    /* La cena la usano solo alcuni circoli: se non compare mai, la sua voce di
       legenda sarebbe una riga che non spiega niente. */
    $ceneInUso = false;
    foreach ($year as $m) {
        foreach ($m['days'] as $d) {
            if ($d['reserved_dinner'] > 0) { $ceneInUso = true; break 2; }
        }
    }

    /* Bootstrap mostra solo la slide con .active: se il mese corrente non è tra
       quelli caricati, senza questo ripiego il calendario resterebbe vuoto. */
    $meseAperto = null;
    foreach ($year as $i => $m) {
        if ($currentMonth == $m['month'] && $currentYear == $m['year']) { $meseAperto = $i; break; }
    }
    $meseAperto ??= 0;
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <b>Gestionale</b>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Calendario</b>
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

@if (session('error'))
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <span>{{ session('error') }}</span>
        <button type="button" class="ui-flash__close" data-ui-dismiss aria-label="Chiudi avviso">
            @include('admin.partials.ui-icon', ['name' => 'x-lg', 'size' => 14])
        </button>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Calendario</h1>
        <div class="ui-head__count">
            <span>Scegli un giorno per vedere e prenotare le fasce orarie</span>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.reservations.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'card-checklist', 'size' => 16])
            <span>Tutte le prenotazioni</span>
        </a>
        <button type="button" class="ui-btn ui-btn--danger" data-bs-toggle="modal" data-bs-target="#bloccaGiorni">
            @include('admin.partials.ui-icon', ['name' => 'ban', 'size' => 16])
            <span>Blocca giorni</span>
        </button>
    </div>
</header>

{{-- ============ Il mese ============ --}}
<section class="cal">
    <div id="carouselExampleIndicators" class="carousel slide">
        <div class="carousel-indicators">
            @foreach ($year as $i => $m)
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $i }}"
                    @if ($i === $meseAperto) class="active" aria-current="true" @endif
                    aria-label="{{ $mesi[$m['month']].' '.$m['year'] }}"></button>
            @endforeach
        </div>

        <div id="calendar" class="carousel-inner">
            @foreach ($year as $i => $m)
                <div class="carousel-item @if ($i === $meseAperto) active @endif">
                    <div class="cal__head">
                        <h2 class="cal__month">{{ $mesi[$m['month']] }} {{ $m['year'] }}</h2>
                        <div class="cal__nav">
                            <button class="ui-action ui-action--icon" type="button"
                                    data-bs-target="#carouselExampleIndicators" data-bs-slide="prev" aria-label="Mese precedente">
                                @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                            </button>
                            <button class="ui-action ui-action--icon" type="button"
                                    data-bs-target="#carouselExampleIndicators" data-bs-slide="next" aria-label="Mese successivo">
                                @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                            </button>
                        </div>
                    </div>

                    <div class="cal__dow" aria-hidden="true">
                        @foreach ($giorni as $breve => $resto)
                            <span>{{ $breve }}<i>{{ $resto }}</i></span>
                        @endforeach
                    </div>

                    <div class="cal__grid">
                        @foreach ($m['days'] as $d)
                            @php
                                $giornata = $tourneiPerGiorno[$d['date']] ?? [];

                                /* Quello che dice il lettore di schermo: gli stessi numeri
                                   che si vedono, a parole. Niente title da scoprire col mouse. */
                                $voce = [];
                                foreach ($tipi as $chiave => $tipo) {
                                    $quanti = (int) $d['reserved_'.$chiave];
                                    if ($quanti > 0) {
                                        $voce[] = $quanti.' '.($quanti === 1 ? $tipo['uno'] : $tipo['tanti']);
                                    }
                                }
                                if ($giornata) {
                                    $voce[] = 'giornata di torneo: '.collect($giornata)->pluck('nome')->implode(', ');
                                }
                                if (! $d['status']) {
                                    $voce[] = 'circolo chiuso';
                                }
                            @endphp
                            <button type="button" data-day='@json($d)'
                                    class="cal__day @if ($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) current @endif @if (! $d['status']) day_off @endif"
                                    style="grid-column-start: {{ $d['day_w'] }}"
                                    aria-label="{{ $d['day'] }} {{ $mesi[$m['month']] }}{{ $voce ? ' — '.implode(', ', $voce) : ', niente in programma' }}"
                                    @if ($voce) title="{{ implode(' · ', $voce) }}" @endif>
                                {{-- Fascia in cima: il torneo occupa la giornata intera, non una fascia
                                     oraria, e il blu è lo stesso delle sue partite più sotto --}}
                                @if ($giornata)
                                    <span class="cal__band" aria-hidden="true"></span>
                                @endif

                                <p class="p_day">{{ $d['day'] }}</p>

                                @if ($d['reserved_match'] + $d['reserved_lesson'] + $d['reserved_tournament'] + $d['reserved_dinner'] > 0)
                                    <span class="cal__marks" aria-hidden="true">
                                        @foreach ($tipi as $chiave => $tipo)
                                            @continue($d['reserved_'.$chiave] < 1)
                                            <span class="cal__mark cal__mark--{{ $chiave }}">
                                                {!! $segni[$chiave] !!}<b>{{ $d['reserved_'.$chiave] }}</b>
                                            </span>
                                        @endforeach
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Legenda: vale sia per i riquadri del mese sia per le fasce orarie qui
         sotto, che usano le stesse icone e gli stessi colori. --}}
    <div class="cal__legend">
        <span class="cal__legend__title">Legenda</span>

        @foreach ($tipi as $chiave => $tipo)
            @continue($chiave === 'dinner' && ! $ceneInUso)
            <span class="cal__key cal__key--{{ $chiave }}">
                @include('admin.partials.ui-icon', ['name' => $tipo['icona'], 'size' => 13])
                <span>{{ $tipo['legenda'] }}</span>
            </span>
        @endforeach

        <span class="cal__key cal__key--band">
            <i aria-hidden="true"></i>
            <span>Giornata di torneo</span>
        </span>

        <span class="cal__key cal__key--fixed">
            @include('admin.partials.ui-icon', ['name' => 'arrow-repeat', 'size' => 13])
            <span>Campo fisso</span>
        </span>

        <span class="cal__key cal__key--off">
            <i aria-hidden="true"></i>
            <span>Circolo chiuso</span>
        </span>
    </div>
</section>

{{-- ============ Prenotazione: fasce orarie del giorno scelto ============ --}}
<form id="bookingForm" action="{{ route('admin.reservations.createFromD') }}" method="POST">
    @csrf

    {{-- I tornei del giorno scelto: la riempie il JS leggendo la mappa qui sotto --}}
    <div id="tournamentBand"></div>
    <script type="application/json" id="tournamentsByDay">@json($tourneiPerGiorno)</script>

    <div id="slots">
        <div class="ui-empty">
            <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'calendar2-week', 'size' => 25])</span>
            <h2>Scegli un giorno</h2>
            <p>Tocca una data qui sopra: compaiono i campi con le fasce libere e quelle già occupate.</p>
        </div>
    </div>

    {{-- Finestra di conferma della prenotazione --}}
    <div class="modal fade ui-modal" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <h2 id="exampleModalLabel" style="font-size:19px;font-weight:700;margin-bottom:16px;">Completa la prenotazione</h2>

                    <div class="ui-fields ui-fields--2">
                        <div class="ui-field">
                            <label for="lesson">Tipo</label>
                            <select name="lesson" id="lesson">
                                <option value="0">Partita</option>
                                <option value="1">Lezione</option>
                                <option value="2">Partita di torneo</option>
                            </select>
                        </div>
                        <div class="ui-field">
                            <label for="note">Note</label>
                            <input type="text" name="message" id="note" placeholder="Facoltative">
                        </div>
                    </div>

                    <div class="ui-field" style="margin-top:16px;">
                        <label>Giocatori</label>
                        <div class="ui-chips" data-ui-chips>
                            <div class="ui-chips__head">
                                <div class="ui-search">
                                    @include('admin.partials.ui-icon', ['name' => 'search', 'size' => 16])
                                    <label class="ui-vh" for="playerSearch">Cerca un giocatore</label>
                                    <input type="search" id="playerSearch" placeholder="Cerca giocatore..." autocomplete="off">
                                </div>
                            </div>
                            <div class="ui-chips__area">
                                @foreach ($players as $p)
                                    <label class="ui-chips__item" data-ui-chip="{{ strtolower($p->nickname.' '.$p->name.' '.$p->surname) }}">
                                        <input type="checkbox" name="players[]" value="{{ $p->id }}">
                                        <span>#{{ $p->nickname }}<small>liv {{ $p->level }}</small></span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="ui-chips__empty" data-ui-chips-empty hidden>Nessun giocatore con questo nome.</p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="ui-btn" data-bs-dismiss="modal">Annulla</button>
                    <button class="ui-btn ui-btn--primary" name="type_res" value="singola" id="smbt_btn" type="submit">
                        Conferma prenotazione
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- ============ Blocca giorni ============ --}}
<form action="{{ route('admin.settings.cancelDates') }}" method="POST">
    @csrf
    <div class="modal fade ui-modal" id="bloccaGiorni" tabindex="-1" aria-labelledby="bloccaGiorniLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <h2 id="bloccaGiorniLabel" style="font-size:19px;font-weight:700;margin-bottom:6px;">Giorni di chiusura</h2>
                    <p class="ui-hint" style="margin-bottom:16px;">
                        I giorni segnati in rosso non sono prenotabili dal sito. Ritoccali e conferma.
                    </p>

                    <div id="c2" class="carousel slide">
                        <div class="carousel-indicators">
                            @foreach ($year as $i => $m)
                                <button type="button" data-bs-target="#c2" data-bs-slide-to="{{ $i }}"
                                    @if ($i === $meseAperto) class="active" aria-current="true" @endif
                                    aria-label="{{ $mesi[$m['month']].' '.$m['year'] }}"></button>
                            @endforeach
                        </div>

                        <div class="carousel-inner">
                            @foreach ($year as $i => $m)
                                <div class="carousel-item @if ($i === $meseAperto) active @endif">
                                    <div class="cal__head">
                                        <h3 class="cal__month">{{ $mesi[$m['month']] }} {{ $m['year'] }}</h3>
                                        <div class="cal__nav">
                                            <button class="ui-action ui-action--icon" type="button"
                                                    data-bs-target="#c2" data-bs-slide="prev" aria-label="Mese precedente">
                                                @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                                            </button>
                                            <button class="ui-action ui-action--icon" type="button"
                                                    data-bs-target="#c2" data-bs-slide="next" aria-label="Mese successivo">
                                                @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                                            </button>
                                        </div>
                                    </div>

                                    <div class="cal__dow" aria-hidden="true">
                                        @foreach ($giorni as $breve => $resto)
                                            <span>{{ $breve }}<i>{{ $resto }}</i></span>
                                        @endforeach
                                    </div>

                                    <div class="cal__grid">
                                        @foreach ($m['days'] as $d)
                                            <input type="checkbox" name="day_off[]" id="off_{{ $d['date'] }}" value="{{ $d['date'] }}"
                                                   @checked(! $d['status'])>
                                            <label for="off_{{ $d['date'] }}"
                                                   class="cal__day @if ($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) current @endif"
                                                   style="grid-column-start: {{ $d['day_w'] }}">
                                                <p class="p_day">{{ $d['day'] }}</p>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="ui-btn" type="button" data-bs-dismiss="modal">Annulla</button>
                    <button class="ui-btn ui-btn--primary" type="submit">Conferma i giorni chiusi</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
        b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
    });

    // ---------- Icone delle fasce ----------
    // Le stesse quattro icone del riquadro del giorno, disegnate qui una volta
    // sola: sono marcatura statica del server, non dati dell'utente.
    const ICONE = {
        match:      `@include('admin.partials.ui-icon', ['name' => 'circle-fill', 'size' => 12])`,
        lesson:     `@include('admin.partials.ui-icon', ['name' => 'mortarboard-fill', 'size' => 14])`,
        tournament: `@include('admin.partials.ui-icon', ['name' => 'trophy-fill', 'size' => 14])`,
        fixed:      `@include('admin.partials.ui-icon', ['name' => 'arrow-repeat', 'size' => 13])`,
    };

    const NOMI = {
        match:      'Campo prenotato',
        lesson:     'Lezione',
        tournament: 'Partita di torneo',
    };

    // Stato globale delle selezioni: { data: { campo: [orari] } }
    const selectedSlots = {};

    const bookingForm    = document.getElementById('bookingForm');
    const slotsContainer = document.getElementById('slots');
    const dayButtons     = document.querySelectorAll('#calendar .cal__day');

    dayButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            // Salva le selezioni del giorno corrente prima di cambiare
            saveCurrentDaySelections();

            dayButtons.forEach((e) => e.classList.remove('selected'));
            const day = JSON.parse(btn.dataset.day);
            btn.classList.add('selected');

            mostraTornei(day.date);

            slotsContainer.innerHTML = `
                <div class="fields" id="fields"></div>
                <div style="display:flex; justify-content:center; margin-top:20px;">
                    <button id="unique-btn" type="button" class="ui-btn ui-btn--primary" style="display:none;"
                            data-bs-toggle="modal" data-bs-target="#exampleModal">Prenota</button>
                </div>`;

            const fieldsContainer = document.getElementById('fields');

            Object.entries(day.fields).forEach(([fieldName, fieldData]) => {
                // Un campo chiuso quel giorno arriva come array vuoto, senza chiave
                // "times": senza il valore di scorta qui sotto il ciclo esplode e
                // i campi successivi non vengono più disegnati.
                const { times = [], match } = fieldData;

                const title = document.createElement('h4');
                title.textContent = fieldName;
                if (match !== undefined) {
                    const span = document.createElement('span');
                    span.textContent = match + ' prenotate';
                    title.appendChild(span);
                }
                fieldsContainer.appendChild(title);

                const fieldDiv = document.createElement('div');
                fieldDiv.classList.add('field');
                fieldDiv.dataset.field = fieldName;

                if (times.length > 0) {
                    times.forEach((slot) => {
                        const safeTime = String(slot.time).replace(/[^a-z0-9]/gi, '_');
                        const inputId = `i_${safeTime}_${fieldName}`;
                        const timeDiv = document.createElement('div');
                        timeDiv.classList.add('time');

                        if (slot.status == 1) {
                            timeDiv.classList.add('trainer_slot');
                            timeDiv.style.setProperty('--flag', slot.flag);
                        }

                        const role = '{{ auth()->user()->role }}';
                        const userId = {{ auth()->user()->id }};

                        if (slot.status == 2) {
                            // Il tipo decide icona, tinta e parola: le tre cose insieme,
                            // perché il colore da solo non basta a nessuno
                            const tipo = slot.lesson == 1 ? 'lesson' : (slot.lesson == 2 ? 'tournament' : 'match');

                            if (tipo === 'lesson') {
                                timeDiv.classList.add('lesson');
                                timeDiv.style.setProperty('--flag', slot.flag);
                            } else if (tipo === 'tournament') {
                                timeDiv.classList.add('booked', 'trophy');
                            } else {
                                timeDiv.classList.add('booked');
                            }
                            timeDiv.classList.add(`bk_${slot.d > 3 ? '3' : slot.d}`);
                            // Campo fisso: si distingue dalle prenotazioni normali
                            if (slot.fixed) timeDiv.classList.add('fixed_slot');

                            const link = document.createElement('a');
                            link.href = `/admin/reservations/${slot.id}`;
                            link.title = `${NOMI[tipo]}${slot.fixed ? ' (campo fisso)' : ''} · ${slot.time} · ${slot.booking_subject}`;

                            const segno = document.createElement('span');
                            segno.classList.add('slot_icon');
                            segno.innerHTML = ICONE[tipo];
                            link.appendChild(segno);

                            const spanTime = document.createElement('span');
                            spanTime.classList.add('time_b');
                            spanTime.textContent = slot.time;

                            const spanSubj = document.createElement('span');
                            spanSubj.classList.add('booking_subject');
                            spanSubj.textContent = `#${slot.booking_subject}`;

                            link.appendChild(spanTime);
                            link.appendChild(spanSubj);

                            // Il campo fisso porta il suo segno di ricorrenza accanto al nome
                            if (slot.fixed) {
                                const ricorre = document.createElement('span');
                                ricorre.classList.add('slot_icon', 'slot_icon--fixed');
                                ricorre.innerHTML = ICONE.fixed;
                                link.appendChild(ricorre);
                            }

                            // La parola resta, oltre all'icona: è il canale che non
                            // dipende né dal colore né dalla forma.
                            const tag = document.createElement('em');
                            tag.classList.add('slot_tag');
                            tag.textContent = tipo === 'lesson' ? 'lezione' : (tipo === 'tournament' ? 'torneo' : 'campo');
                            link.appendChild(tag);

                            timeDiv.appendChild(link);

                        } else if ((role == 'admin' || slot.trainer_id.includes(userId) && slot.status == 1) || slot.status == 0) {
                            const input = document.createElement('input');
                            input.type = 'checkbox';
                            input.classList.add('slot-checkbox');
                            input.value = `${day.date}/${slot.time}/${fieldName}`;
                            input.id = inputId;

                            const label = document.createElement('label');
                            label.htmlFor = inputId;
                            if (!slot.s) label.classList.add('middle');
                            label.textContent = slot.time;

                            timeDiv.appendChild(input);
                            timeDiv.appendChild(label);

                            input.addEventListener('change', () => {
                                const currentDayBtn = document.querySelector('#calendar .cal__day.selected');
                                if (!currentDayBtn) return;
                                const currentDate = JSON.parse(currentDayBtn.dataset.day).date;

                                if (!selectedSlots[currentDate]) selectedSlots[currentDate] = {};
                                if (!selectedSlots[currentDate][fieldName]) selectedSlots[currentDate][fieldName] = [];

                                const arr = selectedSlots[currentDate][fieldName];

                                if (input.checked) {
                                    if (!arr.includes(slot.time)) arr.push(slot.time);
                                } else {
                                    const index = arr.indexOf(slot.time);
                                    if (index > -1) arr.splice(index, 1);
                                    if (arr.length === 0) delete selectedSlots[currentDate][fieldName];
                                    if (Object.keys(selectedSlots[currentDate]).length === 0) delete selectedSlots[currentDate];
                                }

                                checkCheckboxes();
                            });

                        } else {
                            const label = document.createElement('label');
                            label.htmlFor = inputId;
                            if (!slot.s) label.classList.add('middle');
                            label.textContent = slot.time;
                            timeDiv.appendChild(label);
                        }

                        fieldDiv.appendChild(timeDiv);
                    });
                } else {
                    const p = document.createElement('p');
                    p.classList.add('null_p');
                    p.textContent = 'Campo non disponibile in questa data';
                    fieldDiv.appendChild(p);
                }

                fieldsContainer.appendChild(fieldDiv);
            });

            restoreSelections(day.date);
            checkCheckboxes();
        });
    });

    // ---------- Tornei del giorno ----------
    const banda = document.getElementById('tournamentBand');
    const tourneiPerGiorno = JSON.parse(document.getElementById('tournamentsByDay')?.textContent || '{}');

    function mostraTornei(data) {
        if (!banda) return;

        const tornei = tourneiPerGiorno[data] || [];
        if (!tornei.length) {
            banda.innerHTML = '';
            return;
        }

        banda.innerHTML = tornei.map((t) => {
            const quota = t.posti > 0 ? Math.min(100, Math.round(t.iscritte / t.posti * 100)) : 0;
            const iscrizioni = t.aperto
                ? `<span class="ui-pill ui-pill--accent">Iscrizioni aperte${t.chiusura ? ' fino al ' + t.chiusura : ''}</span>`
                : '<span class="ui-pill">Iscrizioni chiuse</span>';

            return `
                <article class="tband">
                    <span class="tband__icon" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'trophy', 'size' => 20])</span>
                    <div class="tband__body">
                        <a class="tband__name" href="${t.url}">${t.nome}</a>
                        <div class="tband__meta">
                            <span class="ui-status ui-status--${t.statoKey}">${t.stato}</span>
                            <span>${t.formula}</span>
                            <span class="ui-code">${t.campi}</span>
                            ${t.ora ? `<span>dalle ${t.ora}</span>` : ''}
                            ${iscrizioni}
                            ${t.attesa ? `<span class="ui-pill ui-pill--warn">${t.attesa} in attesa</span>` : ''}
                        </div>
                    </div>
                    <div class="ui-meter tband__meter">
                        <div class="ui-meter__value">${t.iscritte}<small>/${t.posti}</small></div>
                        <div class="ui-meter__track">
                            <span class="ui-meter__fill ${quota >= 100 ? 'ui-meter__fill--warn' : ''}" style="width:${quota}%"></span>
                        </div>
                    </div>
                    <div class="tband__actions">
                        <a class="ui-action" href="${t.url}#iscritti">Iscrizioni</a>
                        <a class="ui-action ui-action--icon" href="${t.url}" aria-label="Apri il torneo ${t.nome}">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 16])</a>
                    </div>
                </article>`;
        }).join('');
    }

    function saveCurrentDaySelections() {
        const activeBtn = document.querySelector('#calendar .cal__day.selected');
        if (!activeBtn) return;

        const { date } = JSON.parse(activeBtn.dataset.day);
        selectedSlots[date] = {};

        document.querySelectorAll('#fields .field').forEach((fieldDiv) => {
            const fieldName = fieldDiv.dataset.field;
            const checked = fieldDiv.querySelectorAll('.slot-checkbox:checked');
            if (!checked.length) return;

            selectedSlots[date][fieldName] = [];
            checked.forEach((cb) => selectedSlots[date][fieldName].push(cb.value.split('/')[1]));
        });

        if (!Object.keys(selectedSlots[date]).length) delete selectedSlots[date];
    }

    function restoreSelections(date) {
        if (!selectedSlots[date]) return;

        document.querySelectorAll('.slot-checkbox').forEach((cb) => {
            const [, time, fieldName] = cb.value.split('/');
            if (selectedSlots[date][fieldName] && selectedSlots[date][fieldName].includes(time)) {
                cb.checked = true;
            }
        });
    }

    function checkCheckboxes() {
        let totalSelections = 0;
        const uniqueBtn = document.getElementById('unique-btn');

        Object.values(selectedSlots).forEach((fields) => {
            Object.values(fields).forEach((times) => totalSelections += times.length);
        });

        if (!uniqueBtn) return;

        uniqueBtn.style.display = totalSelections > 0 ? 'inline-flex' : 'none';

        // Su più giorni la prenotazione diventa multipla: lo dice il bottone e lo dice il form
        const multi = Object.keys(selectedSlots).length > 1;
        uniqueBtn.textContent = multi ? 'Prenota su più giorni' : `Prenota ${totalSelections} ${totalSelections === 1 ? 'fascia' : 'fasce'}`;
        document.querySelector('#smbt_btn').value = multi ? 'multipla' : 'singola';
    }

    // Al submit aggiungo un campo nascosto per ogni fascia selezionata
    bookingForm.addEventListener('submit', () => {
        bookingForm.querySelectorAll('.dynamic-slot').forEach((e) => e.remove());

        Object.entries(selectedSlots).forEach(([date, fields]) => {
            Object.entries(fields).forEach(([field, times]) => {
                times.forEach((time) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'times[]';
                    input.value = `${date}/${time}/${field}`;
                    input.classList.add('dynamic-slot');
                    bookingForm.appendChild(input);
                });
            });
        });
    });

    // All'apertura la banda mostra già i tornei di oggi: la fascia oraria
    // richiede un clic, ma "oggi c'è un torneo" si deve vedere subito.
    mostraTornei('{{ now()->format('Y-m-d') }}');

    // Ricerca dei giocatori nella finestra di conferma
    const searchInput = document.getElementById('playerSearch');
    const chips = document.querySelectorAll('[data-ui-chip]');
    const chipsEmpty = document.querySelector('[data-ui-chips-empty]');

    searchInput?.addEventListener('input', function () {
        const value = this.value.toLowerCase().trim();
        let shown = 0;
        chips.forEach((item) => {
            const ok = !value || item.dataset.uiChip.includes(value);
            item.hidden = !ok;
            if (ok) shown++;
        });
        if (chipsEmpty) chipsEmpty.hidden = shown > 0;
    });
});
</script>
@endsection
