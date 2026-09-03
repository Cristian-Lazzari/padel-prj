@extends('layouts.ui')

@section('title', 'Nuovo campo fisso - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.fixed-slots.index') }}">Campi fissi</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Nuovo</b>
</nav>

@if ($errors->any())
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <span>Controlla i campi segnati: {{ $errors->count() }} da correggere.</span>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Nuovo campo fisso</h1>
        <div class="ui-head__count"><span>Al salvataggio le prenotazioni finiscono subito in calendario, fino alla data di fine</span></div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.fixed-slots.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna all'elenco</span>
        </a>
    </div>
</header>

<form class="ui-form" action="{{ route('admin.fixed-slots.store') }}" method="POST">
    @csrf
    @include('admin.FixedSlots._form')

    <div class="ui-savebar" style="grid-column: 1 / -1;">
        <span class="ui-savebar__note">Nelle date in cui il campo è già prenotato da altri, la ricorrenza viene saltata e te lo diciamo.</span>
        <div class="ui-savebar__actions">
            <a class="ui-btn" href="{{ route('admin.fixed-slots.index') }}">Annulla</a>
            <button class="ui-btn ui-btn--primary" type="submit">Crea campo fisso</button>
        </div>
    </div>
</form>

@endsection
