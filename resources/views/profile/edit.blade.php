@extends('layouts.ui')

@section('title', 'Il tuo account - F+')

@section('contents')

@php
    /* Colori scelti per il flag: si riconoscono sul fondo scuro del gestionale. */
    $colors = [
        'future +'          => '#23B792',
        'giallo oro'        => '#F5C542',
        'giallo lime'       => '#D6FF3F',
        'verde menta'       => '#3FF4C6',
        'turchese acceso'   => '#1DE9B6',
        'azzurro elettrico' => '#4DA3FF',
        'viola neon'        => '#9B5CFF',
        'fucsia'            => '#FF4FD8',
        'corallo'           => '#FF6B4A',
        'arancio acceso'    => '#FF9F1C',
        'rosso lampone'     => '#E63946',
    ];
    $flagAttuale = old('flag', $user->flag);
    $ruolo = ['admin' => 'Amministratore', 'trainer' => 'Istruttore'][$user->role] ?? $user->role;
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Il tuo account</b>
</nav>

@if (session('status') === 'profile-updated')
    <div class="ui-flash" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'check-circle-fill', 'size' => 20])
        <span>Dati dell'account aggiornati.</span>
        <button type="button" class="ui-flash__close" data-ui-dismiss aria-label="Chiudi avviso">
            @include('admin.partials.ui-icon', ['name' => 'x-lg', 'size' => 14])
        </button>
    </div>
@endif

@if (session('status') === 'password-updated')
    <div class="ui-flash" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'check-circle-fill', 'size' => 20])
        <span>Password aggiornata.</span>
        <button type="button" class="ui-flash__close" data-ui-dismiss aria-label="Chiudi avviso">
            @include('admin.partials.ui-icon', ['name' => 'x-lg', 'size' => 14])
        </button>
    </div>
@endif

@if (session('status') === 'verification-link-sent')
    <div class="ui-flash" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'envelope-at', 'size' => 20])
        <span>Ti abbiamo inviato un nuovo link di verifica.</span>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title" style="display:flex; align-items:center; gap:16px;">
        <span class="ui-avatar ui-avatar--lg" aria-hidden="true"
              style="background: {{ $flagAttuale ?: '#23B792' }}; color: #090333;">
            {{ strtoupper(mb_substr($user->name, 0, 2)) }}
        </span>
        <div>
            <h1>{{ $user->name }}</h1>
            <div class="ui-head__count">
                <span>{{ $ruolo }}</span>
                <span>{{ $user->email }}</span>
            </div>
        </div>
    </div>
</header>

{{-- Il modulo di rinvio della verifica sta fuori: il bottone lo richiama con l'attributo form --}}
<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<div class="ui-split">
    <div class="ui-split__main">

        <form class="ui-panel" method="post" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('patch')

            <div class="ui-panel__head"><h2>I tuoi dati</h2></div>

            <div class="ui-fields ui-fields--2">
                <div class="ui-field">
                    <label for="name">Nome <b>*</b></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @error('name') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
                <div class="ui-field">
                    <label for="email">Email <b>*</b></label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    <p class="ui-hint">È il nome utente con cui entri nel gestionale.</p>
                    @error('email') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="ui-flash ui-flash--warn" role="alert">
                    @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
                    <span>Il tuo indirizzo email non è ancora verificato.</span>
                    <button form="send-verification" class="ui-action" type="submit">Rinvia la verifica</button>
                </div>
            @endif

            <div class="ui-field">
                <label>Colore dell'account</label>
                <p class="ui-hint">Ti riconosce nel calendario: gli slot che tieni tu prendono questo colore.</p>
                <div class="ui-chips__area" role="radiogroup" aria-label="Colore dell'account">
                    @foreach ($colors as $nome => $valore)
                        <label class="ui-chips__item flag_pick" style="--flag-color: {{ $valore }}">
                            <input type="radio" name="flag" value="{{ $valore }}" @checked($flagAttuale == $valore) required>
                            <span>
                                <i aria-hidden="true"></i>
                                {{ $nome }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('flag') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>

            <div>
                <button type="submit" class="ui-btn ui-btn--primary">Salva i dati</button>
            </div>
        </form>

        <form class="ui-panel" method="post" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            <div class="ui-panel__head">
                <h2>Password</h2>
                <span class="ui-panel__note">almeno 8 caratteri</span>
            </div>

            <div class="ui-field">
                <label for="current_password">Password attuale <b>*</b></label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
                {{-- Gli errori della password vivono nel sacchetto "updatePassword":
                     con @error('current_password') non sarebbero mai comparsi. --}}
                @foreach ($errors->updatePassword->get('current_password') as $message)
                    <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p>
                @endforeach
            </div>

            <div class="ui-fields ui-fields--2">
                <div class="ui-field">
                    <label for="password">Nuova password <b>*</b></label>
                    <input id="password" name="password" type="password" required autocomplete="new-password">
                    @foreach ($errors->updatePassword->get('password') as $message)
                        <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p>
                    @endforeach
                </div>
                <div class="ui-field">
                    <label for="password_confirmation">Ripeti la nuova password <b>*</b></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                    @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                        <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p>
                    @endforeach
                </div>
            </div>

            <div>
                <button type="submit" class="ui-btn">Aggiorna la password</button>
            </div>
        </form>
    </div>

    <aside class="ui-split__side">
        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Account</h2></div>
            <div class="ui-facts" style="grid-template-columns: 1fr;">
                <div class="ui-fact">
                    <span>Ruolo</span>
                    <strong>{{ $ruolo }}</strong>
                </div>
                <div class="ui-fact">
                    <span>Email</span>
                    <strong style="font-size:14px; word-break:break-all;">{{ $user->email }}</strong>
                    <small>
                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            Non verificata
                        @else
                            Verificata
                        @endif
                    </small>
                </div>
                <div class="ui-fact">
                    <span>Colore</span>
                    <strong style="display:flex; align-items:center; gap:8px;">
                        <i aria-hidden="true" style="width:14px; height:14px; border-radius:50%; background: {{ $flagAttuale ?: '#23B792' }}; display:inline-block;"></i>
                        {{ array_search($flagAttuale, $colors, true) ?: 'personalizzato' }}
                    </strong>
                </div>
            </div>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Sessione</h2></div>
            <p class="ui-hint">Esci dal gestionale su questo dispositivo.</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="ui-btn ui-btn--danger" type="submit" style="width:100%;">
                    @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
                    <span>Esci</span>
                </button>
            </form>
        </section>
    </aside>
</div>

@endsection

@section('styles')
<style>
    /* La pastiglia scelta si accende del colore che rappresenta, non di verde:
       altrimenti il colore selezionato è l'unico che non si vede. */
    .flag_pick i{
        width: 12px; height: 12px;
        border-radius: 50%;
        background: var(--flag-color);
        display: inline-block;
    }
    .flag_pick input:checked + span{
        background: var(--flag-color);
        color: #090333;
    }
    .flag_pick input:checked + span i{ background: rgba(9, 3, 51, .55); }
</style>
@endsection

@section('scripts')
<script>
document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
    b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
});
</script>
@endsection
