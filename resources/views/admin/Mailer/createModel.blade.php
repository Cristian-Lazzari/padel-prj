@extends('layouts.ui')

@section('title', 'Nuovo modello - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.mailer.index') }}">Comunicazioni</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Nuovo modello</b>
</nav>

@if ($errors->any())
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <span>Controlla i campi segnati: {{ $errors->count() }} da correggere.</span>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Nuovo modello di email</h1>
        <div class="ui-head__count"><span>I campi con <b>*</b> sono obbligatori</span></div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.mailer.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna alle comunicazioni</span>
        </a>
    </div>
</header>

<form class="ui-form" action="{{ route('admin.mailer.create_m') }}" enctype="multipart/form-data" method="POST">
    @csrf

    @include('admin.Mailer._form', ['model' => null])

    <div class="ui-savebar" style="grid-column: 1 / -1;">
        <span class="ui-savebar__note">Il modello resta salvato: potrai riusarlo in più campagne.</span>
        <div class="ui-savebar__actions">
            <a class="ui-btn" href="{{ route('admin.mailer.index') }}">Annulla</a>
            <button class="ui-btn ui-btn--primary" type="submit">Crea modello</button>
        </div>
    </div>
</form>

@endsection
