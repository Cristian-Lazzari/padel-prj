@extends('layouts.ui')

@section('title', 'Giocatori - F+')

@section('page_vars', '--ui-cols: minmax(0, 2.4fr) 120px 150px 160px 132px;')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Giocatori</b>
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
    $certScaduti  = $players->where('certificate_status', 'expired')->count();
@endphp

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Giocatori</h1>
        @if ($players->isNotEmpty())
            <div class="ui-head__count">
                <span data-ui-count-all>In tutto <b>{{ $players->count() }}</b></span>
                <span data-ui-count-shown hidden>Mostrati <b>0</b> di {{ $players->count() }}</span>
                @if ($certScaduti)
                    <span>Certificati scaduti <b>{{ $certScaduti }}</b></span>
                @endif
            </div>
        @endif
    </div>
    @if ($players->isNotEmpty())
        <div class="ui-head__actions">
            <a class="ui-btn ui-btn--primary" href="{{ route('admin.players.create') }}">
                @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                <span>Nuovo giocatore</span>
            </a>
        </div>
    @endif
</header>

@if ($players->isNotEmpty())
    <div class="ui-filters">
        <div class="ui-search">
            @include('admin.partials.ui-icon', ['name' => 'search', 'size' => 16])
            <label class="ui-vh" for="searchInput">Cerca un giocatore per nome o soprannome</label>
            <input type="search" id="searchInput" placeholder="Cerca giocatore..." autocomplete="off">
        </div>

        <button type="button" class="ui-chip is-on" data-ui-level="all" aria-pressed="true">Tutti i livelli</button>
        @for ($l = 1; $l <= 5; $l++)
            @php $n = $players->where('level', $l)->count(); @endphp
            @continue($n === 0)
            <button type="button" class="ui-chip" data-ui-level="{{ $l }}" aria-pressed="false">
                Livello {{ $l }}<span class="ui-chip__count">{{ $n }}</span>
            </button>
        @endfor

        <button type="button" class="ui-chip" data-ui-sex="m" aria-pressed="false">Uomini<span class="ui-chip__count">{{ $players->where('sex', 'm')->count() }}</span></button>
        <button type="button" class="ui-chip" data-ui-sex="f" aria-pressed="false">Donne<span class="ui-chip__count">{{ $players->where('sex', '!=', 'm')->count() }}</span></button>
    </div>
@endif

<div class="ui-list" role="table" aria-label="Elenco dei giocatori" id="playersList">
    @if ($players->isNotEmpty())
        <div class="ui-list__head" role="row">
            <span role="columnheader">Giocatore</span>
            <span role="columnheader">Livello</span>
            <span role="columnheader">Telefono</span>
            <span role="columnheader">Certificato</span>
            <span role="columnheader" class="ui-vh">Azioni</span>
        </div>
    @endif

    @forelse ($players as $r)
        @php
            $iniziali = strtoupper(substr($r->name, 0, 1).substr($r->surname, 0, 1));
            $cert = $r->certificate_status;
        @endphp
        <article class="ui-row" role="row"
                 data-level="{{ $r->level }}" data-sex="{{ $r->sex }}"
                 data-name="{{ Str::lower($r->nickname.' '.$r->name.' '.$r->surname.' '.$r->city) }}">

            <div class="ui-name ui-name--media" role="cell">
                @if ($r->img_url)
                    <img class="ui-avatar" src="{{ $r->img_url }}" alt="" loading="lazy">
                @else
                    <span class="ui-avatar" aria-hidden="true">{{ $iniziali }}</span>
                @endif
                <div class="ui-name__body">
                    <a href="{{ route('admin.players.show', $r) }}">#{{ $r->nickname }}</a>
                    <div class="ui-name__meta">
                        <span>{{ $r->name }} {{ $r->surname }}</span>
                        @if ($r->city)<span>{{ $r->city }}</span>@endif
                        @unless ($r->mail_verified)
                            <span class="ui-pill ui-pill--warn">Email da verificare</span>
                        @endunless
                    </div>
                </div>
            </div>

            <div class="ui-meter" data-label="Livello" role="cell">
                <div class="ui-meter__value">{{ $r->level }}<small>/5</small></div>
                <div class="ui-meter__track">
                    <span class="ui-meter__fill" style="width: {{ min(100, $r->level / 5 * 100) }}%"></span>
                </div>
            </div>

            <div class="ui-cell ui-cell--money" data-label="Telefono" role="cell">
                <strong>{{ $r->phone ?: '—' }}</strong>
                <span>{{ $r->sex == 'm' ? 'Uomo' : 'Donna' }}</span>
            </div>

            <div class="ui-cell" data-label="Certificato" role="cell">
                @if ($cert === 'expired')
                    <strong><span class="ui-pill ui-pill--danger">Scaduto</span></strong>
                @elseif ($cert === 'expiring')
                    <strong><span class="ui-pill ui-pill--warn">In scadenza</span></strong>
                @elseif ($cert === 'valid')
                    <strong><span class="ui-pill ui-pill--accent">In regola</span></strong>
                @else
                    {{-- 'missing': niente pillola, uno stato neutro è assenza di segnale --}}
                    <strong>—</strong>
                @endif
                @if ($r->certificate_expires_at)
                    <span>{{ \Carbon\Carbon::parse($r->certificate_expires_at)->format('d/m/Y') }}</span>
                @endif
            </div>

            <div class="ui-actions" role="cell">
                @if ($r->phone)
                    <a class="ui-action ui-action--icon" href="tel:{{ $r->phone }}"
                       aria-label="Chiama {{ $r->nickname }}" title="Chiama">
                        @include('admin.partials.ui-icon', ['name' => 'telephone-fill', 'size' => 16])
                    </a>
                @endif
                <a class="ui-action ui-action--icon" href="{{ route('admin.players.edit', $r) }}"
                   aria-label="Modifica {{ $r->nickname }}" title="Modifica">
                    @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
                </a>
                <a class="ui-action ui-action--icon" href="{{ route('admin.players.show', $r) }}"
                   aria-label="Apri la scheda di {{ $r->nickname }}" title="Apri">
                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 16])
                </a>
            </div>
        </article>
    @empty
        <div class="ui-empty">
            <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'people-fill', 'size' => 25])</span>
            <h2>Nessun giocatore in anagrafica</h2>
            <p>Registra il primo giocatore: da qui tieni contatti, livello, certificato medico e storico delle prenotazioni.</p>
            <a class="ui-btn ui-btn--primary" href="{{ route('admin.players.create') }}">
                @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                <span>Nuovo giocatore</span>
            </a>
        </div>
    @endforelse
</div>

<div class="ui-empty" id="noResults" hidden>
    <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'search', 'size' => 25])</span>
    <h2>Nessun giocatore con questi filtri</h2>
    <p>Prova a cercare un altro nome o a togliere il filtro di livello.</p>
    <button type="button" class="ui-btn" data-ui-reset>Azzera i filtri</button>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
        b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
    });

    const list   = document.getElementById('playersList');
    const search = document.getElementById('searchInput');
    if (!list || !search) return;

    const rows       = Array.from(list.querySelectorAll('.ui-row'));
    const levelChips = Array.from(document.querySelectorAll('[data-ui-level]'));
    const sexChips   = Array.from(document.querySelectorAll('[data-ui-sex]'));
    const empty      = document.getElementById('noResults');
    const countAll   = document.querySelector('[data-ui-count-all]');
    const countShown = document.querySelector('[data-ui-count-shown]');

    let level = 'all';
    let sex = null;

    function apply() {
        const term = (search.value || '').toLowerCase().trim();
        let shown = 0;

        rows.forEach((row) => {
            const ok = (!term || row.dataset.name.includes(term))
                    && (level === 'all' || row.dataset.level === level)
                    && (!sex || (sex === 'm' ? row.dataset.sex === 'm' : row.dataset.sex !== 'm'));
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
    }

    search.addEventListener('input', apply);

    levelChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            level = chip.dataset.uiLevel;
            levelChips.forEach((c) => {
                const on = c === chip;
                c.classList.toggle('is-on', on);
                c.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            apply();
        });
    });

    sexChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            // Un secondo clic sulla stessa pastiglia toglie il filtro
            sex = sex === chip.dataset.uiSex ? null : chip.dataset.uiSex;
            sexChips.forEach((c) => {
                const on = c.dataset.uiSex === sex;
                c.classList.toggle('is-on', on);
                c.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            apply();
        });
    });

    document.querySelector('[data-ui-reset]')?.addEventListener('click', () => {
        search.value = '';
        sex = null;
        sexChips.forEach((c) => { c.classList.remove('is-on'); c.setAttribute('aria-pressed', 'false'); });
        levelChips[0]?.click();
        search.focus();
    });

    apply();
});
</script>
@endsection
