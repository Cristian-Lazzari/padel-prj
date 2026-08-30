@extends('layouts.ui')

@section('title', 'Bacheca annunci - F+')

@section('page_vars', '--ui-cols: minmax(0, 2.4fr) 130px 150px 260px;')

@section('contents')

@php
    $tabs = [
        ''          => 'Tutti',
        'pending'   => 'Da approvare',
        'published' => 'Pubblicati',
        'sold'      => 'Venduti',
        'expired'   => 'Scaduti',
        'rejected'  => 'Rifiutati',
    ];
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Bacheca</b>
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
        <h1>Bacheca annunci</h1>
        <div class="ui-head__count">
            <span>In questa vista <b>{{ count($listings) }}</b></span>
            @if ($counts['pending'] ?? 0)
                <span>Da approvare <b>{{ $counts['pending'] }}</b></span>
            @endif
        </div>
    </div>
</header>

{{-- I filtri sono link: lo stato arriva dal server, non dal browser --}}
<div class="ui-filters" role="group" aria-label="Filtra per stato">
    @foreach ($tabs as $value => $label)
        <a class="ui-chip {{ (string) $status === (string) $value ? 'is-on' : '' }}"
           href="{{ route('admin.listings.index', $value ? ['status' => $value] : []) }}"
           @if ((string) $status === (string) $value) aria-current="page" @endif>
            {{ $label }}
            @if ($value && ($counts[$value] ?? 0))<span class="ui-chip__count">{{ $counts[$value] }}</span>@endif
        </a>
    @endforeach
</div>

<div class="ui-list" role="table" aria-label="Elenco degli annunci">
    @if (count($listings))
        <div class="ui-list__head" role="row">
            <span role="columnheader">Annuncio</span>
            <span role="columnheader">Prezzo</span>
            <span role="columnheader">Scadenza</span>
            <span role="columnheader">Moderazione</span>
        </div>
    @endif

    @forelse ($listings as $l)
        <article class="ui-row {{ in_array($l->status, ['rejected', 'expired'], true) ? 'ui-row--muted' : '' }}" role="row">
            <div class="ui-name ui-name--media" role="cell">
                @if ($l->images->first())
                    <img class="ui-avatar ui-avatar--square" src="{{ $l->images->first()->url }}" alt="" loading="lazy">
                @else
                    <span class="ui-avatar ui-avatar--square" aria-hidden="true">—</span>
                @endif
                <div class="ui-name__body">
                    <a href="{{ route('admin.listings.show', $l) }}">{{ $l->title }}</a>
                    <div class="ui-name__meta">
                        <span class="ui-pill {{ $l->status === 'published' ? 'ui-pill--accent' : ($l->status === 'pending' ? 'ui-pill--warn' : ($l->status === 'rejected' ? 'ui-pill--danger' : '')) }}">
                            {{ $l->statusLabel() }}
                        </span>
                        <span>{{ ucfirst($l->category) }} · {{ ucfirst($l->condition) }}</span>
                        <a href="{{ route('admin.players.show', $l->player_id) }}" class="ui-code">#{{ $l->player?->nickname }}</a>
                        <span>{{ $l->images_count }} foto · {{ $l->views }} viste</span>
                    </div>
                    @if ($l->reject_reason)
                        <p class="ui-err">
                            @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13])
                            Rifiutato: {{ $l->reject_reason }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="ui-cell ui-cell--money" data-label="Prezzo" role="cell">
                <strong>{{ $l->priceLabel() }}</strong>
                <span>inviato il {{ $l->created_at->format('d/m/Y') }}</span>
            </div>

            <div class="ui-cell" data-label="Scadenza" role="cell">
                <strong>{{ $l->expires_at?->format('d/m/Y') ?: '—' }}</strong>
            </div>

            <div class="ui-actions" role="cell">
                @if ($l->status !== 'published')
                    <form action="{{ route('admin.listings.approve', $l) }}" method="post" style="display:flex; gap:8px; align-items:center;">
                        @csrf
                        <label class="ui-vh" for="days{{ $l->id }}">Giorni di pubblicazione</label>
                        <input type="number" name="days" id="days{{ $l->id }}" min="1" max="365"
                               placeholder="{{ $default_days }} gg" style="width:92px; min-height:38px; padding:8px 12px;">
                        <button class="ui-action" type="submit">Pubblica</button>
                    </form>
                @else
                    <form action="{{ route('admin.listings.status', $l) }}" method="post">
                        @csrf
                        <input type="hidden" name="status" value="pending">
                        <button class="ui-action" type="submit">Togli dalla bacheca</button>
                    </form>
                @endif
                <a class="ui-action ui-action--icon" href="{{ route('admin.listings.show', $l) }}"
                   aria-label="Apri l'annuncio {{ $l->title }}" title="Apri">
                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 16])
                </a>
            </div>
        </article>
    @empty
        <div class="ui-empty">
            <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'shop', 'size' => 25])</span>
            <h2>Nessun annuncio in questa vista</h2>
            <p>Gli annunci arrivano dai giocatori attraverso il sito e restano qui in attesa di approvazione.</p>
            @if ($status)
                <a class="ui-btn" href="{{ route('admin.listings.index') }}">Vedi tutti gli annunci</a>
            @endif
        </div>
    @endforelse
</div>

<section class="ui-panel">
    <div class="ui-panel__head"><h2>Impostazioni della bacheca</h2></div>
    <form action="{{ route('admin.listings.settings') }}" method="post">
        @csrf
        <div class="ui-field" style="max-width: 320px;">
            <label for="default_days">Durata di default degli annunci</label>
            <input type="number" name="default_days" id="default_days" min="1" max="365" value="{{ $default_days }}">
            <p class="ui-hint">In giorni. Si applica quando approvi un annuncio senza indicare una durata diversa.</p>
        </div>
        <div>
            <button class="ui-btn" type="submit">Salva impostazione</button>
        </div>
    </form>
</section>

@endsection

@section('scripts')
<script>
document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
    b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
});
</script>
@endsection
