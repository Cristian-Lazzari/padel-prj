@extends('layouts.ui')

@section('title', 'Nuovo istruttore - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.players.index') }}">Giocatori</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Nuovo istruttore</b>
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

@if ($errors->any())
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <span>Controlla i campi segnati: {{ $errors->count() }} da correggere.</span>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Registra un istruttore</h1>
        <div class="ui-head__count">
            <span>Oltre alla scheda giocatore viene creato un accesso al gestionale</span>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.players.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna all'elenco</span>
        </a>
    </div>
</header>

{{-- Nessun campo file: create_register() salva solo anagrafica e utenza,
     il certificato si carica poi dalla scheda del giocatore. --}}
<form class="ui-form" action="{{ route('admin.players.create_register') }}" method="POST">
    @csrf

    <div class="ui-form__main">
        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Identità</h2></div>
            <div class="ui-fields ui-fields--2">
                <div class="ui-field">
                    <label for="nickname">Soprannome <b>*</b></label>
                    <input type="text" name="nickname" id="nickname" value="{{ old('nickname') }}" required>
                    @error('nickname') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
                <div class="ui-field">
                    <label for="sex">Sesso <b>*</b></label>
                    <select name="sex" id="sex" required>
                        <option value="m" @selected(old('sex') === 'm')>Uomo</option>
                        <option value="f" @selected(old('sex') === 'f')>Donna</option>
                    </select>
                    @error('sex') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
                <div class="ui-field">
                    <label for="name">Nome <b>*</b></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                    @error('name') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
                <div class="ui-field">
                    <label for="surname">Cognome <b>*</b></label>
                    <input type="text" name="surname" id="surname" value="{{ old('surname') }}" required>
                    @error('surname') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Contatti e livello</h2></div>
            <div class="ui-fields ui-fields--2">
                <div class="ui-field">
                    <label for="mail">Email <b>*</b></label>
                    <input type="email" name="mail" id="mail" value="{{ old('mail') }}" required>
                    <p class="ui-hint">È anche il nome utente con cui entrerà nel gestionale.</p>
                    @error('mail') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
                <div class="ui-field">
                    <label for="phone">Telefono <b>*</b></label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required>
                    @error('phone') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
                <div class="ui-field">
                    <label for="level">Livello <b>*</b></label>
                    <input type="number" name="level" id="level" min="1" max="5" step="1" value="{{ old('level', 5) }}" required>
                    @error('level') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
            </div>
        </section>
    </div>

    <aside class="ui-form__side">
        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Accesso al gestionale</h2></div>
            <div class="ui-field">
                <label for="password">Password <b>*</b></label>
                <input type="password" name="password" id="password" required autocomplete="new-password">
                <p class="ui-hint">Almeno 8 caratteri. Comunicala all'istruttore: potrà cambiarla dal suo profilo.</p>
            </div>
            <div class="ui-field">
                <label for="password_confirmation">Conferma password <b>*</b></label>
                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
                @error('password') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </section>
    </aside>

    <div class="ui-savebar" style="grid-column: 1 / -1;">
        <span class="ui-savebar__note">Vengono creati insieme la scheda giocatore e l'utenza.</span>
        <div class="ui-savebar__actions">
            <a class="ui-btn" href="{{ route('admin.players.index') }}">Annulla</a>
            <button class="ui-btn ui-btn--primary" type="submit">Registra istruttore</button>
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
