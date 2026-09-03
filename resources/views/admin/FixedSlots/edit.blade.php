@extends('layouts.ui')

@section('title', 'Modifica campo fisso - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.fixed-slots.index') }}">Campi fissi</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Modifica</b>
</nav>

@if ($errors->any())
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <span>Controlla i campi segnati: {{ $errors->count() }} da correggere.</span>
    </div>
@endif

<div class="ui-flash ui-flash--warn" role="alert">
    @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
    <span>Salvando, le prenotazioni future di questo campo fisso vengono cancellate e ricreate con i nuovi dati, fino alla data di fine validità. Quelle già passate restano in archivio.</span>
</div>

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Modifica campo fisso</h1>
        <div class="ui-head__count"><span>#{{ $slot->player?->nickname }}</span></div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.fixed-slots.show', $slot) }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna al dettaglio</span>
        </a>
    </div>
</header>

<form class="ui-form" action="{{ route('admin.fixed-slots.update', $slot) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.FixedSlots._form')

    <div class="ui-savebar" style="grid-column: 1 / -1;">
        <span class="ui-savebar__note">Le modifiche valgono solo dopo il salvataggio.</span>
        <div class="ui-savebar__actions">
            <a class="ui-btn" href="{{ route('admin.fixed-slots.show', $slot) }}">Annulla</a>
            <button class="ui-btn ui-btn--primary" type="submit">Salva modifiche</button>
        </div>
    </div>
</form>

@endsection
