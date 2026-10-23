@extends('layouts.ui')

@section('title', 'Prenotazioni - F+')

{{-- Le righe dentro ogni nome sono quelle compatte di ui-res-row: tre colonne --}}
@section('page_vars', '--ui-cols: 130px minmax(0, 1fr) 132px;')

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

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Prenotazioni</h1>
        <div class="ui-head__count">
            <span>A {{ $label }} <b>{{ $total }}</b></span>
            <span>Giocatori <b>{{ $groups->count() }}</b></span>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.reservations.index', ['view' => 'list']) }}">
            @include('admin.partials.ui-icon', ['name' => 'card-checklist', 'size' => 16])
            <span>Elenco completo</span>
        </a>
        <a class="ui-btn" href="{{ route('admin.dashboard') }}">
            @include('admin.partials.ui-icon', ['name' => 'calendar2-week', 'size' => 16])
            <span>Vai al calendario</span>
        </a>
    </div>
</header>

<form class="ui-filters" method="GET" action="{{ route('admin.reservations.index') }}" id="filtri">
    <input type="hidden" name="month" value="{{ $cursor->format('Y-m') }}">

    <div class="ui-monthnav">
        <a class="ui-pager__page" href="{{ route('admin.reservations.index', array_filter(['month' => $prev, 'q' => $q])) }}"
           aria-label="Mese precedente" title="Mese precedente">
            <span class="ui-monthnav__back">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 14])</span>
        </a>
        <strong class="ui-monthnav__label">{{ ucfirst($label) }}</strong>
        <a class="ui-pager__page" href="{{ route('admin.reservations.index', array_filter(['month' => $next, 'q' => $q])) }}"
           aria-label="Mese successivo" title="Mese successivo">
            @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 14])
        </a>
        @unless ($is_current)
            <a class="ui-chip" href="{{ route('admin.reservations.index', array_filter(['q' => $q])) }}">Mese corrente</a>
        @endunless
    </div>

    <div class="ui-search">
        @include('admin.partials.ui-icon', ['name' => 'search', 'size' => 16])
        <label class="ui-vh" for="searchInput">Cerca per nome di chi ha prenotato</label>
        <input type="search" id="searchInput" name="q" value="{{ $q }}"
               placeholder="Cerca un nome..." autocomplete="off">
    </div>

    <noscript><button type="submit" class="ui-btn">Applica</button></noscript>
</form>

@if ($groups->isNotEmpty())
    <div class="ui-people">
        @foreach ($groups as $g)
            <details class="ui-data ui-person">
                <summary>
                    <span class="ui-person__name">
                        <b>{{ $g['name'] }}</b>
                        @if ($g['nickname'])
                            <small>{{ '@'.$g['nickname'] }}</small>
                        @endif
                    </span>
                    <span class="ui-person__meta">
                        @if ($g['fixed'])
                            <span class="ui-pill ui-pill--accent">Fisso</span>
                        @endif
                        <span class="ui-pill">{{ $g['confirmed'] }} {{ $g['confirmed'] == 1 ? 'prenotazione' : 'prenotazioni' }}</span>
                        @if ($g['cancelled'])
                            <span class="ui-pill ui-pill--danger">{{ $g['cancelled'] }} {{ $g['cancelled'] == 1 ? 'annullata' : 'annullate' }}</span>
                        @endif
                        @include('admin.partials.ui-icon', ['name' => 'chevron-down', 'size' => 16])
                    </span>
                </summary>

                <div class="ui-person__body">
                    <div class="ui-list" role="table" aria-label="Prenotazioni di {{ $g['name'] }} a {{ $label }}">
                        @foreach ($g['items'] as $r)
                            @include('admin.partials.ui-res-row', ['r' => $r])
                        @endforeach
                    </div>
                    @if ($g['id'])
                        <a class="ui-btn ui-person__link" href="{{ route('admin.players.show', $g['id']) }}">
                            <span>Scheda giocatore</span>
                            @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 14])
                        </a>
                    @endif
                </div>
            </details>
        @endforeach
    </div>
@else
    <div class="ui-empty">
        <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => $q !== '' ? 'search' : 'card-checklist', 'size' => 25])</span>
        @if ($q !== '')
            <h2>Nessuno con questo nome a {{ $label }}</h2>
            <p>Prova un altro nome o cambia mese.</p>
            <a class="ui-btn" href="{{ route('admin.reservations.index', ['month' => $cursor->format('Y-m')]) }}">Azzera la ricerca</a>
        @else
            <h2>Nessuna prenotazione a {{ $label }}</h2>
            <p>Cambia mese con le frecce, oppure crea una prenotazione dal calendario.</p>
        @endif
    </div>
@endif

@endsection

@section('styles')
<style>
.ui-monthnav{ display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ui-monthnav__label{ min-width: 140px; text-align: center; font-size: 16px; font-weight: 700; }
.ui-monthnav__back{ display: inline-flex; transform: rotate(180deg); }

.ui-people{ display: grid; gap: 8px; }
.ui-person > summary{ text-transform: none; letter-spacing: 0; font-size: 15px; flex-wrap: wrap; }
.ui-person__name{ display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; min-width: 0; }
.ui-person__name b{ font-size: 16px; font-weight: 700; }
.ui-person__name small{ font-size: 12.5px; font-weight: 500; color: var(--ui-ink-soft); }
.ui-person__meta{ display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-left: auto; }
.ui-person__body{ display: grid; gap: 12px; padding: 0 12px 14px; }
.ui-person__body .ui-row{ background: var(--ui-surface-2); }
.ui-person__link{ justify-self: start; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
        b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
    });

    // La ricerca aspetta che si smetta di scrivere: una richiesta per parola.
    const filtri = document.getElementById('filtri');
    const search = document.getElementById('searchInput');
    let attesa = null;
    search?.addEventListener('input', () => {
        clearTimeout(attesa);
        attesa = setTimeout(() => filtri.submit(), 450);
    });
});
</script>
@endsection
