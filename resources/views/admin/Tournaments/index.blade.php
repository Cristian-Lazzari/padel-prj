@extends('layouts.ui')

@section('title', 'Tornei - F+')

{{-- Larghezze di colonna: la stessa griglia serve intestazione e righe --}}
@section('page_vars', '--ui-cols: minmax(0, 2.6fr) 130px 170px 150px 120px;')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Tornei</b>
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
        <h1>Tornei</h1>
        @if ($tournaments->isNotEmpty())
            <div class="ui-head__count">
                <span data-ui-count-all>In tutto <b>{{ $tournaments->count() }}</b></span>
                <span data-ui-count-shown hidden>Mostrati <b>0</b> di {{ $tournaments->count() }}</span>
                @if ($tournaments->where('status', 'open')->count())
                    <span>Iscrizioni aperte <b>{{ $tournaments->where('status', 'open')->count() }}</b></span>
                @endif
            </div>
        @endif
    </div>
    @if ($tournaments->isNotEmpty())
        <div class="ui-head__actions">
            <a class="ui-btn ui-btn--primary" href="{{ route('admin.tournaments.create') }}">
                @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                <span>Nuovo torneo</span>
            </a>
        </div>
    @endif
</header>

@if ($tournaments->isNotEmpty())
    <div class="ui-filters">
        <div class="ui-search">
            @include('admin.partials.ui-icon', ['name' => 'search', 'size' => 16])
            <label class="ui-vh" for="searchInput">Cerca un torneo per nome</label>
            <input type="search" id="searchInput" placeholder="Cerca torneo..." autocomplete="off">
        </div>

        @php
            /* Solo gli stati che esistono davvero in elenco: un filtro che non filtra nulla è rumore. */
            $uiStates = [
                'all'      => 'Tutti',
                'open'     => 'Iscrizioni aperte',
                'running'  => 'In corso',
                'closed'   => 'Iscrizioni chiuse',
                'finished' => 'Conclusi',
                'draft'    => 'Bozze',
            ];
        @endphp
        @foreach ($uiStates as $value => $label)
            @php $count = $value === 'all' ? $tournaments->count() : $tournaments->where('status', $value)->count(); @endphp
            @continue($count === 0 && $value !== 'all')
            <button type="button" class="ui-chip {{ $value === 'all' ? 'is-on' : '' }}"
                    data-ui-filter="{{ $value }}" aria-pressed="{{ $value === 'all' ? 'true' : 'false' }}">
                {{ $label }}<span class="ui-chip__count">{{ $count }}</span>
            </button>
        @endforeach
    </div>
@endif

<div class="ui-list" role="table" aria-label="Elenco dei tornei" id="tournamentsList">
    @if ($tournaments->isNotEmpty())
    <div class="ui-list__head" role="row">
        <span role="columnheader">Torneo</span>
        <span role="columnheader">Inizio</span>
        <span role="columnheader">Formula</span>
        <span role="columnheader">Iscrizioni</span>
        <span role="columnheader" class="ui-vh">Azioni</span>
    </div>
    @endif

    @forelse ($tournaments as $t)
        @php
            $posti  = max((int) $t->teams_max, 0);
            $iscr   = (int) $t->confirmed_registrations_count;
            $quota  = $posti > 0 ? min(100, round($iscr / $posti * 100)) : 0;
        @endphp
        <article class="ui-row {{ $t->status === 'draft' || $t->status === 'cancelled' ? 'ui-row--muted' : '' }}"
                 role="row" data-status="{{ $t->status }}" data-name="{{ Str::lower($t->name) }}">

            <div class="ui-name" role="cell">
                <a href="{{ route('admin.tournaments.show', $t) }}">{{ $t->name }}</a>
                <div class="ui-name__meta">
                    <span class="ui-status ui-status--{{ $t->status }}">{{ $t->statusLabel() }}</span>
                    @if (in_array($t->status, \App\Models\Tournament::PUBLIC_STATUSES, true))
                        <span class="ui-pill ui-pill--accent">Sul sito</span>
                    @endif
                    <span class="ui-code">{{ $t->fieldsLabel() }}</span>
                    @if ($t->location)
                        <span>{{ $t->location }}</span>
                    @endif
                    @if ($t->waitlist_registrations_count)
                        <span class="ui-pill ui-pill--warn">{{ $t->waitlist_registrations_count }} in attesa</span>
                    @endif
                </div>
            </div>

            <div class="ui-cell" data-label="Inizio" role="cell">
                <strong>{{ $t->starts_at?->format('d/m/Y') ?? '—' }}</strong>
                <span>{{ $t->starts_at?->format('H:i') }}</span>
            </div>

            <div class="ui-cell" data-label="Formula" role="cell">
                <strong>{{ $t->formatLabel() }}</strong>
                <span>{{ $t->is_pair ? 'Coppie' : 'Singolo' }} · {{ $t->levelLabel() }}</span>
            </div>

            <div class="ui-meter {{ $posti === 0 ? 'ui-meter--empty' : '' }}" data-label="Iscrizioni" role="cell">
                @if ($posti > 0)
                    <div class="ui-meter__value">{{ $iscr }}<small>/{{ $posti }}</small></div>
                    <div class="ui-meter__track">
                        <span class="ui-meter__fill {{ $quota >= 100 ? 'ui-meter__fill--warn' : '' }}" style="width: {{ $quota }}%"></span>
                    </div>
                @else
                    <div class="ui-meter__value">—</div>
                @endif
            </div>

            <div class="ui-actions" role="cell">
                <a class="ui-action ui-action--icon" href="{{ route('admin.tournaments.edit', $t) }}"
                   aria-label="Modifica {{ $t->name }}" title="Modifica">
                    @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
                </a>
                <a class="ui-action ui-action--icon" href="{{ route('admin.tournaments.show', $t) }}"
                   aria-label="Apri {{ $t->name }}" title="Apri">
                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 16])
                </a>
            </div>
        </article>
    @empty
        <div class="ui-empty">
            <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'trophy', 'size' => 25])</span>
            <h2>Nessun torneo in archivio</h2>
            <p>Crea il primo torneo: da qui gestisci iscrizioni, gironi e risultati, e lo pubblichi sul sito quando è pronto.</p>
            <a class="ui-btn ui-btn--primary" href="{{ route('admin.tournaments.create') }}">
                @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                <span>Nuovo torneo</span>
            </a>
        </div>
    @endforelse
</div>

{{-- Vuoto di secondo tipo: l'elenco ha righe ma nessuna passa i filtri --}}
<div class="ui-empty" id="noResults" hidden>
    <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'search', 'size' => 25])</span>
    <h2>Nessun torneo con questi filtri</h2>
    <p>Prova a cambiare lo stato o a cercare un altro nome.</p>
    <button type="button" class="ui-btn" data-ui-reset>Azzera i filtri</button>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Avvisi richiudibili senza dipendere dal JS di Bootstrap
    document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
        b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
    });

    const list    = document.getElementById('tournamentsList');
    const search  = document.getElementById('searchInput');
    const chips   = Array.from(document.querySelectorAll('[data-ui-filter]'));
    const empty   = document.getElementById('noResults');
    const countAll   = document.querySelector('[data-ui-count-all]');
    const countShown = document.querySelector('[data-ui-count-shown]');
    if (!list || !search) return;

    const rows = Array.from(list.querySelectorAll('.ui-row'));
    let state = 'all';

    function apply() {
        const term = (search.value || '').toLowerCase().trim();
        let shown = 0;

        rows.forEach((row) => {
            const ok = (!term || row.dataset.name.includes(term))
                    && (state === 'all' || row.dataset.status === state);
            row.hidden = !ok;
            if (ok) shown++;
        });

        // L'intestazione di colonna non ha senso senza righe sotto
        list.hidden = rows.length > 0 && shown === 0;
        if (empty) empty.hidden = !(rows.length > 0 && shown === 0);
        // Il conteggio filtrato compare solo quando qualcosa è effettivamente nascosto
        const filtrato = shown !== rows.length;
        if (countAll)   countAll.hidden = filtrato;
        if (countShown) {
            countShown.hidden = !filtrato;
            countShown.querySelector('b').textContent = shown;
        }
    }

    search.addEventListener('input', apply);

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            state = chip.dataset.uiFilter;
            chips.forEach((c) => {
                const on = c === chip;
                c.classList.toggle('is-on', on);
                c.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            apply();
        });
    });

    document.querySelector('[data-ui-reset]')?.addEventListener('click', () => {
        search.value = '';
        chips[0]?.click();
        search.focus();
    });

    apply();
});
</script>
@endsection
