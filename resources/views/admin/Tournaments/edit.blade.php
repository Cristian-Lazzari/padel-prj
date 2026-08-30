@extends('layouts.ui')

@section('title', 'Modifica torneo - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.tournaments.index') }}">Tornei</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.tournaments.show', $tournament) }}">{{ $tournament->name }}</a>
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
        <h1>Modifica torneo</h1>
        <div class="ui-head__count"><span>{{ $tournament->name }}</span></div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.tournaments.show', $tournament) }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna al torneo</span>
        </a>
    </div>
</header>

<form class="ui-form" action="{{ route('admin.tournaments.update', $tournament) }}" enctype="multipart/form-data" method="POST">
    @csrf
    @method('PUT')

    @include('admin.Tournaments._form')

    <div class="ui-savebar" style="grid-column: 1 / -1;">
        <span class="ui-savebar__note">Le modifiche valgono solo dopo il salvataggio.</span>
        <div class="ui-savebar__actions">
            <a class="ui-btn" href="{{ route('admin.tournaments.show', $tournament) }}">Annulla</a>
            <button class="ui-btn ui-btn--primary" type="submit">Salva modifiche</button>
        </div>
    </div>
</form>

@endsection
