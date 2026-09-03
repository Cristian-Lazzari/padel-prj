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
    $annullate = $reservations->where('status', 0)->count();
    $aperte    = $reservations->where('is_open', true)->count();
@endphp

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Prenotazioni</h1>
        @if ($reservations->isNotEmpty())
            <div class="ui-head__count">
                <span data-ui-count-all>In tutto <b>{{ $reservations->count() }}</b></span>
                <span data-ui-count-shown hidden>Mostrate <b>0</b> di {{ $reservations->count() }}</span>
                @if ($aperte)
                    <span>Partite aperte <b>{{ $aperte }}</b></span>
                @endif
            </div>
        @endif
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.dashboard') }}">
            @include('admin.partials.ui-icon', ['name' => 'calendar2-week', 'size' => 16])
            <span>Vai al calendario</span>
        </a>
    </div>
</header>

@if ($reservations->isNotEmpty())
    <div class="ui-filters">
        <div class="ui-search">
            @include('admin.partials.ui-icon', ['name' => 'search', 'size' => 16])
            <label class="ui-vh" for="searchInput">Cerca per nome di chi ha prenotato o per campo</label>
            <input type="search" id="searchInput" placeholder="Cerca nome o campo..." autocomplete="off">
        </div>

        <button type="button" class="ui-chip is-on" data-ui-status="all" aria-pressed="true">Tutte<span class="ui-chip__count">{{ $reservations->count() }}</span></button>
        <button type="button" class="ui-chip" data-ui-status="confirmed" aria-pressed="false">Confermate<span class="ui-chip__count">{{ $reservations->count() - $annullate }}</span></button>
        @if ($annullate)
            <button type="button" class="ui-chip" data-ui-status="cancelled" aria-pressed="false">Annullate<span class="ui-chip__count">{{ $annullate }}</span></button>
        @endif
        @if ($aperte)
            <button type="button" class="ui-chip" data-ui-open="open" aria-pressed="false">Solo partite aperte<span class="ui-chip__count">{{ $aperte }}</span></button>
        @endif

        {{-- L'ordinamento è sulla data di prenotazione, come nella versione precedente --}}
        <button type="button" class="ui-chip" id="sortToggle" aria-pressed="false" title="Ordina per data di prenotazione">
            @include('admin.partials.ui-icon', ['name' => 'sort-down-alt', 'size' => 14])
            <span>Prenotate di recente</span>
        </button>
    </div>
@endif

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
        @endphp

        <article class="ui-row {{ $annullata ? 'ui-row--muted' : '' }}" role="row"
                 data-created="{{ $r->created_at }}"
                 data-slot="{{ $r->date_slot }}"
                 data-open="{{ $r->is_open ? '1' : '0' }}"
                 data-status="{{ $annullata ? 'cancelled' : 'confirmed' }}"
                 data-name="{{ Str::lower($nome.' '.$r->field) }}">

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

            @php $iscritti = count($r->players); @endphp
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
        <div class="ui-empty">
            <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'card-checklist', 'size' => 25])</span>
            <h2>Nessuna prenotazione</h2>
            <p>Quando qualcuno prenota dal sito la trovi qui. Puoi crearne una a mano dal calendario, scegliendo giorno, campo e orario.</p>
            <a class="ui-btn ui-btn--primary" href="{{ route('admin.dashboard') }}">
                @include('admin.partials.ui-icon', ['name' => 'calendar2-week', 'size' => 16])
                <span>Vai al calendario</span>
            </a>
        </div>
    @endforelse
</div>

<div class="ui-empty" id="noResults" hidden>
    <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'search', 'size' => 25])</span>
    <h2>Nessuna prenotazione con questi filtri</h2>
    <p>Prova a cambiare stato o a cercare un altro nome.</p>
    <button type="button" class="ui-btn" data-ui-reset>Azzera i filtri</button>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
        b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
    });

    const list   = document.getElementById('reservations-list');
    const search = document.getElementById('searchInput');
    if (!list || !search) return;

    const rows       = Array.from(list.querySelectorAll('.ui-row'));
    const statusChips= Array.from(document.querySelectorAll('[data-ui-status]'));
    const openChip   = document.querySelector('[data-ui-open]');
    const sortToggle = document.getElementById('sortToggle');
    const empty      = document.getElementById('noResults');
    const countAll   = document.querySelector('[data-ui-count-all]');
    const countShown = document.querySelector('[data-ui-count-shown]');

    let status = 'all';
    let soloAperte = false;
    let ordine = 'desc';

    function apply() {
        const term = (search.value || '').toLowerCase().trim();
        let shown = 0;

        rows.forEach((row) => {
            const ok = (!term || row.dataset.name.includes(term))
                    && (status === 'all' || row.dataset.status === status)
                    && (!soloAperte || row.dataset.open === '1');
            row.hidden = !ok;
            if (ok) shown++;
        });

        list.hidden = rows.length > 0 && shown === 0;
        if (empty) empty.hidden = !(rows.length > 0 && shown === 0);

        const filtrato = shown !== rows.length;
        if (countAll)   countAll.hidden = filtrato;
        if (countShown) {
            countShown.hidden = !filtrato;
            countShown.querySelector('b').textContent = shown;
        }

        // Riordino solo le righe visibili, per data di prenotazione
        rows.filter((r) => !r.hidden)
            .sort((a, b) => {
                const d = new Date(a.dataset.created) - new Date(b.dataset.created);
                return ordine === 'asc' ? d : -d;
            })
            .forEach((r) => list.appendChild(r));
    }

    search.addEventListener('input', apply);

    statusChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            status = chip.dataset.uiStatus;
            statusChips.forEach((c) => {
                const on = c === chip;
                c.classList.toggle('is-on', on);
                c.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            apply();
        });
    });

    openChip?.addEventListener('click', () => {
        soloAperte = !soloAperte;
        openChip.classList.toggle('is-on', soloAperte);
        openChip.setAttribute('aria-pressed', soloAperte ? 'true' : 'false');
        apply();
    });

    sortToggle?.addEventListener('click', () => {
        ordine = ordine === 'desc' ? 'asc' : 'desc';
        sortToggle.classList.toggle('is-on', ordine === 'asc');
        sortToggle.setAttribute('aria-pressed', ordine === 'asc' ? 'true' : 'false');
        sortToggle.querySelector('span').textContent = ordine === 'asc' ? 'Prenotate per prime' : 'Prenotate di recente';
        apply();
    });

    document.querySelector('[data-ui-reset]')?.addEventListener('click', () => {
        search.value = '';
        soloAperte = false;
        openChip?.classList.remove('is-on');
        openChip?.setAttribute('aria-pressed', 'false');
        statusChips[0]?.click();
        search.focus();
    });

    apply();
});
</script>
@endsection
