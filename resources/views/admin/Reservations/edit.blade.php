@extends('layouts.ui')

@section('title', 'Modifica prenotazione - F+')

@section('contents')

@php
    $datetime = Carbon\Carbon::parse($reservation->date_slot)->locale('it');
    $data     = $datetime->translatedFormat('l j F');
    $ora      = $datetime->format('H:i');
    $dinner   = json_decode($reservation->dinner, true);
    $intestatario = trim($reservation->booking_subject_name.' '.$reservation->booking_subject_surname);
    $scelti   = $reservation->players->pluck('id');
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.reservations.index') }}">Prenotazioni</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Modifica</b>
</nav>

@if ($errors->any())
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Modifica prenotazione</h1>
        <div class="ui-head__count">
            <span>{{ ucfirst($data) }}</span>
            <span>{{ $ora }}</span>
            <span>Campo <b>{{ $reservation->field }}</b></span>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.reservations.show', $reservation) }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna al dettaglio</span>
        </a>
    </div>
</header>

<form class="ui-form" action="{{ route('admin.reservations.update', $reservation) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="ui-form__main">
        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Prenotazione</h2></div>
            <div class="ui-fields ui-fields--2">
                <div class="ui-field">
                    <label for="lesson">Tipo</label>
                    <select name="lesson" id="lesson">
                        <option value="0" @selected(! $reservation->lesson || $reservation->lesson == 0)>Partita</option>
                        <option value="1" @selected($reservation->lesson == 1)>Lezione</option>
                        <option value="2" @selected($reservation->lesson == 2)>Partita di torneo</option>
                    </select>
                </div>
                <div class="ui-field">
                    <label for="status">Stato</label>
                    <select name="status" id="status">
                        <option value="1" @selected($reservation->status == 1)>Confermata</option>
                        <option value="0" @selected($reservation->status == 0)>Annullata</option>
                    </select>
                    <p class="ui-hint">Annullando da qui non parte la mail di disdetta: usa il pulsante nel dettaglio se vuoi avvisare il cliente.</p>
                </div>
            </div>
        </section>

        @if ($dinner_off)
            <section class="ui-panel">
                <div class="ui-panel__head"><h2>Cena</h2></div>
                @if ($dinner['status'] ?? false)
                    <div class="ui-fields ui-fields--2">
                        <div class="ui-field">
                            <label for="guests">Coperti</label>
                            <input type="number" name="guests" id="guests" min="1" value="{{ $dinner['guests'] }}">
                        </div>
                        <div class="ui-field">
                            <label for="time">Orario</label>
                            <input type="time" name="time" id="time" value="{{ $dinner['time'] }}">
                        </div>
                    </div>
                @else
                    <p class="ui-hint">Cena non prenotata per questa partita.</p>
                @endif
            </section>
        @endif

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Nota</h2></div>
            <div class="ui-field">
                <label class="ui-vh" for="message">Nota della prenotazione</label>
                <textarea name="message" id="message" placeholder="Nessuna nota">{{ trim($reservation->message ?? '') }}</textarea>
            </div>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head">
                <h2>Giocatori</h2>
                <span class="ui-panel__note"><span data-ui-chips-count>{{ $scelti->count() }}</span> selezionati</span>
            </div>

            <div class="ui-chips" data-ui-chips>
                <div class="ui-chips__head">
                    <div class="ui-search">
                        @include('admin.partials.ui-icon', ['name' => 'search', 'size' => 16])
                        <label class="ui-vh" for="chipsSearch">Cerca un giocatore</label>
                        <input type="search" id="chipsSearch" placeholder="Cerca giocatore..." autocomplete="off" data-ui-chips-search>
                    </div>
                </div>

                <div class="ui-chips__area">
                    {{-- Chi è già in partita viene prima: senza ordinamento andrebbe cercato nel mucchio --}}
                    @foreach ($players->sortByDesc(fn ($p) => $scelti->contains($p->id) ? 1 : 0) as $p)
                        <label class="ui-chips__item" data-ui-chip="{{ Str::lower($p->nickname.' '.$p->name.' '.$p->surname) }}">
                            <input type="checkbox" name="players[]" value="{{ $p->id }}" @checked($scelti->contains($p->id))>
                            <span>#{{ $p->nickname }}<small>liv {{ $p->level }}</small></span>
                        </label>
                    @endforeach
                </div>
                <p class="ui-chips__empty" data-ui-chips-empty hidden>Nessun giocatore con questo nome.</p>
            </div>
        </section>
    </div>

    <aside class="ui-form__side">
        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Riepilogo</h2></div>
            <div class="ui-facts" style="grid-template-columns: 1fr;">
                <div class="ui-fact">
                    <span>Prenotato da</span>
                    <strong>
                        @if ($reservation->booking_subject)
                            <a href="{{ route('admin.players.show', $reservation->booking_subject) }}">{{ $intestatario ?: 'Ospite' }}</a>
                        @else
                            {{ $intestatario ?: 'Ospite' }}
                        @endif
                    </strong>
                </div>
                <div class="ui-fact">
                    <span>Quando</span>
                    <strong>{{ ucfirst($data) }}</strong>
                    <small>Dalle {{ $ora }}</small>
                </div>
                <div class="ui-fact">
                    <span>Campo</span>
                    <strong>{{ $reservation->field }}</strong>
                </div>
            </div>
            <p class="ui-hint">Campo, data e orario non si modificano da qui: si spostano dal calendario.</p>
        </section>
    </aside>

    <div class="ui-savebar" style="grid-column: 1 / -1;">
        <span class="ui-savebar__note">Le modifiche valgono solo dopo il salvataggio.</span>
        <div class="ui-savebar__actions">
            <a class="ui-btn" href="{{ route('admin.reservations.show', $reservation) }}">Annulla</a>
            <button class="ui-btn ui-btn--primary" type="submit">Conferma modifiche</button>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ui-chips]').forEach((box) => {
        const search  = box.querySelector('[data-ui-chips-search]');
        const items   = Array.from(box.querySelectorAll('[data-ui-chip]'));
        const vuoto   = box.querySelector('[data-ui-chips-empty]');
        const counter = document.querySelector('[data-ui-chips-count]');

        search?.addEventListener('input', () => {
            const term = (search.value || '').toLowerCase().trim();
            let shown = 0;
            items.forEach((el) => {
                const ok = !term || el.dataset.uiChip.includes(term);
                el.hidden = !ok;
                if (ok) shown++;
            });
            if (vuoto) vuoto.hidden = shown > 0;
        });

        box.addEventListener('change', () => {
            if (counter) counter.textContent = box.querySelectorAll('input:checked').length;
        });
    });
});
</script>
@endsection
