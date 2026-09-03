@extends('layouts.ui')

@section('title', 'Calendario - F+')

@section('styles')
@include('admin.partials.ui-calendar')
@endsection

@section('contents')

@php
    $mesi = ['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno',
             'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];
    $giorni = ['lun' => 'edì', 'mar' => 'tedì', 'mer' => 'coledì', 'gio' => 'vedì',
               'ven' => 'erdì', 'sab' => 'ato', 'dom' => 'enica'];

    $meseCorrente = $mesi[$m['month']].' '.$m['year'];

    /* La cena la usano solo alcuni circoli: se è spenta, la sua voce di
       legenda sarebbe una riga che non spiega niente. */
    $tipiLegenda = [
        'match'      => ['icona' => 'circle-fill',      'legenda' => 'Campo prenotato'],
        'lesson'     => ['icona' => 'mortarboard-fill', 'legenda' => 'Lezione'],
        'tournament' => ['icona' => 'trophy-fill',      'legenda' => 'Partita di torneo'],
        'dinner'     => ['icona' => 'cup-hot-fill',     'legenda' => 'Cena'],
    ];
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

{{-- ============ Il mese ============ ---
     Si carica un mese alla volta: le frecce e il selettore chiedono il mese
     successivo al server e sostituiscono solo la griglia, con la rotellina
     sopra al calendario e non su tutta la pagina. --}}
<section class="cal" id="calendarShell"
         data-month-url="{{ route('admin.calendar.month') }}"
         data-year="{{ $m['year'] }}" data-month="{{ $m['month'] }}">

    <div class="cal__head">
        <h2 class="cal__month" id="calLabel">{{ $meseCorrente }}</h2>
        <div class="cal__nav">
            <button class="ui-action ui-action--icon" type="button" id="calPrev"
                    data-year="{{ $prev_year }}" data-month="{{ $prev_month }}"
                    @disabled(! $has_prev) aria-label="Mese precedente">
                @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
            </button>
            <button class="ui-action ui-action--icon" type="button" id="calNext"
                    data-year="{{ $next_year }}" data-month="{{ $next_month }}"
                    @disabled(! $has_next) aria-label="Mese successivo">
                @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
            </button>
        </div>
    </div>

    <div class="cal__pick">
        <label class="ui-vh" for="calJump">Vai a un mese</label>
        <select id="calJump">
            @foreach ($months as $opt)
                <option value="{{ $opt['year'] }}-{{ $opt['month'] }}"
                        @selected($opt['year'] == $m['year'] && $opt['month'] == $m['month'])>{{ $opt['label'] }}</option>
            @endforeach
        </select>
    </div>

    <div class="cal__dow" aria-hidden="true">
        @foreach ($giorni as $breve => $resto)
            <span>{{ $breve }}<i>{{ $resto }}</i></span>
        @endforeach
    </div>

    {{-- Solo questo pezzo cambia quando si cambia mese --}}
    <div class="cal__body" id="calBody" aria-busy="false" aria-live="polite">
        @include('admin.partials.cal-month', ['m' => $m, 'tourneiPerGiorno' => $tourneiPerGiorno, 'dinner_off' => $dinner_off])

        <div class="cal__loading" hidden>
            <span class="cal__spin" aria-hidden="true"></span>
            <span>Carico il mese…</span>
        </div>
    </div>

    {{-- Legenda: vale sia per i riquadri del mese sia per le fasce orarie qui
         sotto, che usano le stesse icone e gli stessi colori. --}}
    <div class="cal__legend">
        <span class="cal__legend__title">Legenda</span>

        @foreach ($tipiLegenda as $chiave => $tipo)
            @continue($chiave === 'dinner' && ! $dinner_off)
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
<form action="{{ route('admin.settings.cancelDates') }}" method="POST" id="dayOffForm">
    @csrf
    <div class="modal fade ui-modal" id="bloccaGiorni" tabindex="-1" aria-labelledby="bloccaGiorniLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <h2 id="bloccaGiorniLabel" style="font-size:19px;font-weight:700;margin-bottom:6px;">Giorni di chiusura</h2>
                    <p class="ui-hint" style="margin-bottom:16px;">
                        I giorni segnati in rosso non sono prenotabili dal sito. Cambia mese con le frecce:
                        le scelte fatte sugli altri mesi restano, si salvano tutte insieme.
                    </p>

                    <div class="cal" id="dayOffShell" data-year="{{ $m['year'] }}" data-month="{{ $m['month'] }}">
                        <div class="cal__head">
                            <h3 class="cal__month" id="offLabel">{{ $meseCorrente }}</h3>
                            <div class="cal__nav">
                                <button class="ui-action ui-action--icon" type="button" id="offPrev"
                                        data-year="{{ $prev_year }}" data-month="{{ $prev_month }}"
                                        @disabled(! $has_prev) aria-label="Mese precedente">
                                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                                </button>
                                <button class="ui-action ui-action--icon" type="button" id="offNext"
                                        data-year="{{ $next_year }}" data-month="{{ $next_month }}"
                                        @disabled(! $has_next) aria-label="Mese successivo">
                                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 15])
                                </button>
                            </div>
                        </div>

                        <div class="cal__dow" aria-hidden="true">
                            @foreach ($giorni as $breve => $resto)
                                <span>{{ $breve }}<i>{{ $resto }}</i></span>
                            @endforeach
                        </div>

                        <div class="cal__body" id="offBody" aria-busy="false">
                            @include('admin.partials.cal-month-off', ['m' => $m])

                            <div class="cal__loading" hidden>
                                <span class="cal__spin" aria-hidden="true"></span>
                                <span>Carico il mese…</span>
                            </div>
                        </div>

                        <p class="ui-hint" id="offCount"></p>
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

    const ROLE    = '{{ auth()->user()->role }}';
    const USER_ID = {{ auth()->user()->id }};

    // Stato globale delle selezioni: { data: { campo: [orari] } }
    const selectedSlots = {};

    const bookingForm    = document.getElementById('bookingForm');
    const slotsContainer = document.getElementById('slots');

    const shell    = document.getElementById('calendarShell');
    const calBody  = document.getElementById('calBody');
    const calLabel = document.getElementById('calLabel');
    const calPrev  = document.getElementById('calPrev');
    const calNext  = document.getElementById('calNext');
    const calJump  = document.getElementById('calJump');
    const MONTH_URL = shell.dataset.monthUrl;

    // Gli stessi comandi, dentro la finestra "Blocca giorni"
    const offShell = document.getElementById('dayOffShell');
    const offBody  = document.getElementById('offBody');
    const offLabel = document.getElementById('offLabel');
    const offPrev  = document.getElementById('offPrev');
    const offNext  = document.getElementById('offNext');
    const offCount = document.getElementById('offCount');
    const dayOffForm = document.getElementById('dayOffForm');

    // ---------- Cambio mese ----------
    // Il mese arriva dal server già disegnato: la pagina non si ricarica e la
    // rotellina copre solo il calendario. I mesi già visti restano in memoria,
    // così tornare indietro è immediato.
    const cache = new Map();
    let caricando = false;

    function chiave(y, mm) { return y + '-' + mm; }

    /* Il velo copre solo la griglia. Le frecce non si disabilitano qui: il loro
       stato dice se il mese prima o dopo esiste, e riaccenderle a fine
       caricamento farebbe uscire dal periodo consentito. Durante l'attesa
       basta la classe, e i comandi ignorano i clic. */
    function attesa(body, on) {
        body.querySelector('.cal__loading').hidden = !on;
        body.setAttribute('aria-busy', on ? 'true' : 'false');
        body.classList.toggle('is-loading', on);
        caricando = on;
    }

    function applica(dati, target) {
        const body  = target === 'off' ? offBody  : calBody;
        const label = target === 'off' ? offLabel : calLabel;
        const prev  = target === 'off' ? offPrev  : calPrev;
        const next  = target === 'off' ? offNext  : calNext;
        const host  = target === 'off' ? offShell : shell;

        // Prima di ridisegnare, tengo le fasce spuntate del mese che se ne va
        if (target !== 'off') saveCurrentDaySelections();

        const loading = body.querySelector('.cal__loading');
        body.innerHTML = target === 'off' ? dati.offGrid : dati.grid;
        if (loading) body.appendChild(loading);

        label.textContent = dati.label;
        host.dataset.year  = dati.year;
        host.dataset.month = dati.month;

        prev.dataset.year = dati.prev_year;  prev.dataset.month = dati.prev_month;
        next.dataset.year = dati.next_year;  next.dataset.month = dati.next_month;
        prev.disabled = !dati.has_prev;
        next.disabled = !dati.has_next;

        if (target === 'off') {
            segnaGiorniChiusi();
        } else {
            if (calJump) calJump.value = dati.year + '-' + dati.month;
            // Il giorno aperto sotto al calendario può stare nel mese appena
            // caricato: se c'è lo rimetto in evidenza.
            const aperto = document.querySelector('#calBody .cal__day.selected');
            if (!aperto && giornoAperto) {
                const btn = trovaGiorno(giornoAperto);
                if (btn) btn.classList.add('selected');
            }
        }
    }

    async function caricaMese(y, mm, target) {
        if (caricando) return;

        const k = chiave(y, mm);
        const body = target === 'off' ? offBody : calBody;

        if (cache.has(k)) { applica(cache.get(k), target); return; }

        attesa(body, true);

        try {
            const url = `${MONTH_URL}?year=${encodeURIComponent(y)}&month=${encodeURIComponent(mm)}`;
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            if (!res.ok) throw new Error(res.status);
            const dati = await res.json();
            cache.set(chiave(dati.year, dati.month), dati);
            applica(dati, target);
        } catch (e) {
            const avviso = document.createElement('p');
            avviso.className = 'ui-hint';
            avviso.textContent = 'Non sono riuscito a caricare il mese. Riprova.';
            body.prepend(avviso);
            setTimeout(() => avviso.remove(), 4000);
        } finally {
            attesa(body, false);
        }
    }

    calPrev?.addEventListener('click', () => caricaMese(calPrev.dataset.year, calPrev.dataset.month));
    calNext?.addEventListener('click', () => caricaMese(calNext.dataset.year, calNext.dataset.month));
    calJump?.addEventListener('change', () => {
        const [y, mm] = calJump.value.split('-');
        caricaMese(y, mm);
    });

    // ---------- Il giorno scelto ----------
    // Delega sul contenitore: i riquadri cambiano a ogni mese caricato, e
    // riagganciare un listener per ciascuno sarebbe lavoro sprecato.
    let giornoAperto = null;

    function trovaGiorno(data) {
        return Array.from(calBody.querySelectorAll('.cal__day'))
            .find((b) => JSON.parse(b.dataset.day).date === data) || null;
    }

    calBody.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.cal__day');
        if (!btn || !calBody.contains(btn)) return;

        // Salva le selezioni del giorno corrente prima di cambiare
        saveCurrentDaySelections();

        calBody.querySelectorAll('.cal__day').forEach((e) => e.classList.remove('selected'));
        const day = JSON.parse(btn.dataset.day);
        btn.classList.add('selected');
        giornoAperto = day.date;

        mostraTornei(day.date);
        disegnaFasce(day);
    });

    function disegnaFasce(day) {
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
                    // Le fasce arrivano senza le chiavi che valgono zero: qui si
                    // rimettono i valori di partenza, una volta sola.
                    const stato   = slot.status || 0;
                    const maestri = slot.trainer_id || [];
                    const intera  = slot.s || 0;

                    const safeTime = String(slot.time).replace(/[^a-z0-9]/gi, '_');
                    const inputId = `i_${safeTime}_${fieldName}`;
                    const timeDiv = document.createElement('div');
                    timeDiv.classList.add('time');

                    if (stato == 1) {
                        timeDiv.classList.add('trainer_slot');
                        timeDiv.style.setProperty('--flag', slot.flag);
                    }

                    if (stato == 2) {
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

                    } else if ((ROLE == 'admin' || maestri.includes(USER_ID) && stato == 1) || stato == 0) {
                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.classList.add('slot-checkbox');
                        input.value = `${day.date}/${slot.time}/${fieldName}`;
                        input.id = inputId;

                        const label = document.createElement('label');
                        label.htmlFor = inputId;
                        if (!intera) label.classList.add('middle');
                        label.textContent = slot.time;

                        timeDiv.appendChild(input);
                        timeDiv.appendChild(label);

                        input.addEventListener('change', () => {
                            const currentDate = day.date;

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
                        if (!intera) label.classList.add('middle');
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
    }

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
        if (!giornoAperto) return;

        const date = giornoAperto;
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
        saveCurrentDaySelections();
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

    // ---------- Blocca giorni ----------
    // I giorni chiusi stanno tutti qui dentro, non nelle caselle a schermo: il
    // salvataggio riscrive l'elenco intero, quindi va rimandato intero anche
    // quando si è visto un mese solo.
    const chiusi = new Set(@json($day_off));

    function segnaGiorniChiusi() {
        offBody.querySelectorAll('[data-ui-dayoff]').forEach((cb) => {
            cb.checked = chiusi.has(cb.dataset.uiDayoff);
        });
        aggiornaConteggio();
    }

    function aggiornaConteggio() {
        if (!offCount) return;
        const n = chiusi.size;
        offCount.textContent = n === 0
            ? 'Nessun giorno chiuso.'
            : (n === 1 ? '1 giorno chiuso in tutto il calendario.' : n + ' giorni chiusi in tutto il calendario.');
    }

    offBody.addEventListener('change', (ev) => {
        const cb = ev.target.closest('[data-ui-dayoff]');
        if (!cb) return;
        if (cb.checked) chiusi.add(cb.dataset.uiDayoff);
        else chiusi.delete(cb.dataset.uiDayoff);
        aggiornaConteggio();
    });

    offPrev?.addEventListener('click', () => caricaMese(offPrev.dataset.year, offPrev.dataset.month, 'off'));
    offNext?.addEventListener('click', () => caricaMese(offNext.dataset.year, offNext.dataset.month, 'off'));

    dayOffForm.addEventListener('submit', () => {
        dayOffForm.querySelectorAll('.dynamic-off').forEach((e) => e.remove());
        chiusi.forEach((data) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'day_off[]';
            input.value = data;
            input.classList.add('dynamic-off');
            dayOffForm.appendChild(input);
        });
    });

    aggiornaConteggio();

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
