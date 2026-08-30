@extends('layouts.ui')

@section('title', 'Invia campagna - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.mailer.index') }}">Comunicazioni</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Campagna</b>
</nav>

@if (session('send_success'))
    <div class="ui-flash" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'check-circle-fill', 'size' => 20])
        <span>{{ session('send_success') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Avvia una campagna</h1>
        <div class="ui-head__count"><span>Scegli a chi scrivere e con quale modello</span></div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.mailer.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna alle comunicazioni</span>
        </a>
    </div>
</header>

<form action="{{ route('admin.mailer.send_m') }}" method="POST" style="display:grid; gap:18px;">
    @csrf

    <section class="ui-section">
        <div class="ui-section__head"><h2>1. Destinatari</h2></div>
        <div class="ui-cards" role="group" aria-label="Liste di destinatari">
            <label class="ui-pick">
                <input type="checkbox" name="recipients[]" value="3">
                <span class="ui-pick__box">
                    <h3>Contatti aggiunti a mano</h3>
                    <small>{{ $n_c[0] }} contatti in lista</small>
                </span>
            </label>
            <label class="ui-pick">
                <input type="checkbox" name="recipients[]" value="4">
                <span class="ui-pick__box">
                    <h3>Contattati nell'ultima mail</h3>
                    <small>{{ $n_c[1] }} contatti in lista</small>
                </span>
            </label>
        </div>
        @error('recipients') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
    </section>

    <section class="ui-section">
        <div class="ui-section__head"><h2>2. Modello</h2></div>

        @if (count($models))
            <div class="ui-cards" role="group" aria-label="Modelli di email">
                @foreach ($models as $m)
                    <label class="ui-pick">
                        <input type="radio" name="models[]" value="{{ $m->id }}">
                        <span class="ui-pick__box">
                            <span class="ui-pill ui-pill--accent">{{ $m->name }}</span>
                            <h3>{{ $m->heading }}</h3>
                            @if ($m->img_1 !== null)
                                <img src="{{ asset('public/storage/'.$m->img_1) }}" alt="" loading="lazy"
                                     style="width:100%; border-radius:14px;">
                            @endif
                            <small>{{ Str::limit(strip_tags(str_replace('/*/', ' ', $m->body)), 120) }}</small>
                        </span>
                    </label>
                @endforeach
            </div>
        @else
            <div class="ui-empty">
                <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'envelope-at', 'size' => 25])</span>
                <h2>Nessun modello disponibile</h2>
                <p>Serve almeno un modello per inviare una campagna.</p>
                <a class="ui-btn ui-btn--primary" href="{{ route('admin.mailer.create_model') }}">Crea un modello</a>
            </div>
        @endif

        @error('models') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
    </section>

    <div class="ui-savebar">
        <span class="ui-savebar__note">L'invio parte subito e non si annulla.</span>
        <div class="ui-savebar__actions">
            <a class="ui-btn" href="{{ route('admin.mailer.index') }}">Annulla</a>
            <button class="ui-btn ui-btn--primary" type="submit">
                @include('admin.partials.ui-icon', ['name' => 'envelope-at', 'size' => 16])
                <span>Invia la campagna</span>
            </button>
        </div>
    </div>
</form>

@endsection
