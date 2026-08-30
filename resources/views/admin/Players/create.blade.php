@extends('layouts.ui')

@section('title', 'Nuovo giocatore - F+')

@section('contents')

@php
    // Il controller non passa un modello: ne creiamo uno vuoto così i campi
    // condivisi con la modifica possono leggere sempre da $player.
    $player = new \App\Models\Player();
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.players.index') }}">Giocatori</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Nuovo</b>
</nav>

@if (session('create_success'))
    <div class="ui-flash" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'check-circle-fill', 'size' => 20])
        <span>{{ session('create_success') }}</span>
        <button type="button" class="ui-flash__close" data-ui-dismiss aria-label="Chiudi avviso">
            @include('admin.partials.ui-icon', ['name' => 'x-lg', 'size' => 14])
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <span>Controlla i campi segnati: {{ $errors->count() }} da correggere.</span>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Nuovo giocatore</h1>
        <div class="ui-head__count"><span>I campi con <b>*</b> sono obbligatori</span></div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.players.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna all'elenco</span>
        </a>
    </div>
</header>

<form class="ui-form" action="{{ route('admin.players.store') }}" enctype="multipart/form-data" method="POST">
    @csrf

    @include('admin.Players._form', ['player' => $player, 'nuovo' => true])

    <div class="ui-savebar" style="grid-column: 1 / -1;">
        <span class="ui-savebar__note">Il giocatore riceverà la verifica email al primo accesso.</span>
        <div class="ui-savebar__actions">
            <button class="ui-btn" name="add_new" value="1" type="submit">Salva e creane un altro</button>
            <button class="ui-btn ui-btn--primary" type="submit">Salva giocatore</button>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
    b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
});
</script>
@endsection
