@extends('layouts.ui')

@section('title', 'Giocatore - F+')

@section('contents')

@php
    $iniziali = strtoupper(substr($player->name, 0, 1).substr($player->surname, 0, 1));
    $cert = $player->certificate_status;
    $certLabel = [
        'valid'    => ['ui-pill--accent', 'Valido fino al '],
        'expiring' => ['ui-pill--warn',   'In scadenza il '],
        'expired'  => ['ui-pill--danger', 'Scaduto il '],
        'missing'  => ['ui-pill--warn',   'Scadenza '],
    ][$cert] ?? ['ui-pill--warn', 'Scadenza '];
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.players.index') }}">Giocatori</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>#{{ $player->nickname }}</b>
</nav>

@if (session('message'))
    <div class="ui-flash" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'check-circle-fill', 'size' => 20])
        <span>{{ session('message') }}</span>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title" style="display:flex; align-items:center; gap:16px;">
        @if ($player->img_url)
            <img class="ui-avatar ui-avatar--lg" src="{{ $player->img_url }}" alt="" loading="lazy">
        @else
            <span class="ui-avatar ui-avatar--lg" aria-hidden="true">{{ $iniziali }}</span>
        @endif
        <div>
            <h1>#{{ $player->nickname }}</h1>
            <div class="ui-head__count">
                <span>{{ $player->name }} {{ $player->surname }}</span>
                <span>Livello <b>{{ $player->level }}</b></span>
                @unless ($player->mail_verified)
                    <span class="ui-pill ui-pill--warn">Email non verificata</span>
                @endunless
            </div>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.players.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna all'elenco</span>
        </a>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.players.edit', $player) }}">
            @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
            <span>Modifica</span>
        </a>
    </div>
</header>

<div class="ui-split">
    <div class="ui-split__main">

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Anagrafica</h2></div>
            <div class="ui-facts">
                <div class="ui-fact"><span>Città</span><strong>{{ $player->city ?: '—' }}</strong></div>
                <div class="ui-fact">
                    <span>Nascita</span>
                    <strong>{{ $player->birth_date ? $player->birth_date->format('d/m/Y') : '—' }}</strong>
                </div>
                <div class="ui-fact">
                    <span>Mano</span>
                    <strong>@if ($player->hand === 'dx') Destro @elseif ($player->hand === 'sx') Mancino @else — @endif</strong>
                </div>
                <div class="ui-fact">
                    <span>Posizione</span>
                    <strong>{{ $player->preferred_position ? ucfirst($player->preferred_position) : '—' }}</strong>
                </div>
                <div class="ui-fact"><span>Sesso</span><strong>{{ $player->sex == 'm' ? 'Uomo' : 'Donna' }}</strong></div>
                <div class="ui-fact"><span>Livello</span><strong>{{ $player->level }} su 5</strong></div>
            </div>

            @if ($player->bio)
                <div class="ui-field">
                    <label>Bio</label>
                    <p class="ui-hint">{{ $player->bio }}</p>
                </div>
            @endif
            @if ($player->note)
                <div class="ui-field">
                    <label>Note interne</label>
                    <p class="ui-hint">{{ $player->note }}</p>
                </div>
            @endif
        </section>

        @php $prenotate = $player_reservations; @endphp
        <section class="ui-panel">
            <div class="ui-panel__head">
                <h2>Prenotazioni fatte da lui</h2>
                <span class="ui-panel__note">{{ count($prenotate) }}</span>
            </div>
            @if (count($prenotate))
                <div class="ui-list" role="table" aria-label="Prenotazioni intestate a {{ $player->nickname }}"
                     style="--ui-cols: 140px minmax(0, 1fr) 110px;">
                    <div class="ui-list__head" role="row">
                        <span role="columnheader">Quando</span>
                        <span role="columnheader">Prenotazione</span>
                        <span role="columnheader" class="ui-vh">Azioni</span>
                    </div>
                    @foreach ($prenotate as $r)
                        @include('admin.partials.ui-res-row', ['r' => $r, 'field_set' => $field_set, 'dinner_off' => $dinner_off])
                    @endforeach
                </div>
            @else
                <p class="ui-hint">Non ha ancora prenotato nessun campo.</p>
            @endif
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head">
                <h2>Partite giocate</h2>
                <span class="ui-panel__note">{{ count($player->reservations) }}</span>
            </div>
            @if (count($player->reservations))
                <div class="ui-list" role="table" aria-label="Partite giocate da {{ $player->nickname }}"
                     style="--ui-cols: 140px minmax(0, 1fr) 110px;">
                    <div class="ui-list__head" role="row">
                        <span role="columnheader">Quando</span>
                        <span role="columnheader">Partita</span>
                        <span role="columnheader" class="ui-vh">Azioni</span>
                    </div>
                    @foreach ($player->reservations as $r)
                        @include('admin.partials.ui-res-row', ['r' => $r, 'field_set' => $field_set, 'dinner_off' => $dinner_off])
                    @endforeach
                </div>
            @else
                <p class="ui-hint">Non è ancora sceso in campo con nessuno.</p>
            @endif
        </section>
    </div>

    <aside class="ui-split__side">
        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Contatti</h2></div>
            <div class="ui-facts" style="grid-template-columns: 1fr;">
                <div class="ui-fact">
                    <span>Telefono</span>
                    <strong><a href="tel:{{ $player->phone }}">{{ $player->phone ?: '—' }}</a></strong>
                </div>
                <div class="ui-fact">
                    <span>Email</span>
                    <strong style="font-size:14px; word-break:break-all;"><a href="mailto:{{ $player->mail }}">{{ $player->mail }}</a></strong>
                    <small>{{ $player->mail_verified ? 'Verificata' : 'Non verificata' }}</small>
                </div>
            </div>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Certificato medico</h2></div>
            @if ($player->certificate_expires_at)
                <span class="ui-pill {{ $certLabel[0] }}">{{ $certLabel[1] }}{{ $player->certificate_expires_at->format('d/m/Y') }}</span>
            @else
                <p class="ui-hint">Nessuna scadenza registrata.</p>
            @endif
            @if ($player->certificate)
                <a class="ui-btn" href="{{ Storage::disk('public')->url($player->certificate) }}" target="_blank" rel="noopener noreferrer">
                    @include('admin.partials.ui-icon', ['name' => 'eye-fill', 'size' => 16])
                    <span>Apri il documento</span>
                </a>
            @else
                <p class="ui-hint">Nessun documento caricato.</p>
            @endif
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Zona pericolosa</h2></div>
            <p class="ui-hint">Eliminando il giocatore perdi anche il collegamento con le sue prenotazioni passate.</p>
            <button class="ui-btn ui-btn--danger" type="button" data-bs-toggle="modal" data-bs-target="#eliminaGiocatore">
                @include('admin.partials.ui-icon', ['name' => 'trash3-fill', 'size' => 16])
                <span>Elimina giocatore</span>
            </button>
        </section>

        <p class="ui-panel__note" style="padding: 0 6px;">
            Creato il {{ $player->created_at->format('d/m/Y') }} ·
            aggiornato il {{ $player->updated_at->format('d/m/Y') }}
        </p>
    </aside>
</div>

<div class="modal fade ui-modal" id="eliminaGiocatore" tabindex="-1" aria-labelledby="eliminaGiocatoreLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <h2 id="eliminaGiocatoreLabel" style="font-size:19px;font-weight:700;margin-bottom:10px;">
                    Eliminare #{{ $player->nickname }}?
                </h2>
                <p class="ui-hint">
                    {{ $player->name }} {{ $player->surname }} sparisce dall'anagrafica. L'operazione non si annulla.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="ui-btn" data-bs-dismiss="modal">Lascia com'è</button>
                <form action="{{ route('admin.players.destroy', ['player' => $player]) }}" method="post">
                    @method('delete')
                    @csrf
                    <button class="ui-btn ui-btn--danger" type="submit">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
