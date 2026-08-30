@extends('layouts.ui')

@section('title', 'Modifica giocatore - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.players.index') }}">Giocatori</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.players.show', $player) }}">#{{ $player->nickname }}</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Modifica</b>
</nav>

@if ($errors->any())
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <span>Controlla i campi segnati: {{ $errors->count() }} da correggere.</span>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Modifica #{{ $player->nickname }}</h1>
        <div class="ui-head__count"><span>{{ $player->name }} {{ $player->surname }}</span></div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.players.show', $player) }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna alla scheda</span>
        </a>
    </div>
</header>

<form class="ui-form" action="{{ route('admin.players.update', $player) }}" enctype="multipart/form-data" method="POST">
    @csrf
    @method('PUT')

    @include('admin.Players._form', ['player' => $player, 'nuovo' => false])

    <div class="ui-savebar" style="grid-column: 1 / -1;">
        <span class="ui-savebar__note">Le modifiche valgono solo dopo il salvataggio.</span>
        <div class="ui-savebar__actions">
            <a class="ui-btn" href="{{ route('admin.players.show', $player) }}">Annulla</a>
            <button class="ui-btn ui-btn--primary" type="submit">Salva modifiche</button>
        </div>
    </div>
</form>

@endsection
