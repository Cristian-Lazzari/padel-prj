{{--
    Riga compatta di prenotazione, usata negli elenchi secondari (scheda giocatore,
    campo fisso). Prima questo markup era copiato due volte per pagina, con anche
    le finestre della cena duplicate e lo stesso id ripetuto: qui la cena è una
    pillola, l'orario si legge nel dettaglio.
    Attende: $r (prenotazione), $field_set, $dinner_off.
--}}
@php
    $dt        = Carbon\Carbon::parse($r->date_slot)->locale('it');
    $m_during  = $field_set[$r->field]['m_during'] ?? 30;
    $ora_fine  = $dt->copy()->addMinutes($m_during * $r->duration)->format('H:i');
    $dinner    = json_decode($r->dinner, true);
    $annullata = $r->status == 0;
@endphp
<article class="ui-row {{ $annullata ? 'ui-row--muted' : '' }}" role="row">
    <div class="ui-cell" data-label="Quando" role="cell">
        <strong>{{ $dt->translatedFormat('D j M') }}</strong>
        <span>{{ $dt->format('H:i') }} → {{ $ora_fine }}</span>
    </div>

    <div class="ui-name" role="cell">
        <a href="{{ route('admin.reservations.show', $r) }}">
            {{ [0 => 'Partita', 1 => 'Lezione', 2 => 'Partita di torneo'][$r->lesson ?? 0] ?? 'Partita' }} sul {{ $r->field }}
        </a>
        <div class="ui-name__meta">
            @if ($annullata)
                <span class="ui-status ui-status--cancelled">Annullata</span>
            @endif
            @if (count($r->players))
                <span>{{ count($r->players) }} giocatori</span>
            @endif
            @if ($r->message)
                <span class="ui-pill">Con nota</span>
            @endif
            @if ($dinner_off && ($dinner['status'] ?? false))
                <span class="ui-pill ui-pill--accent">Cena {{ $dinner['time'] }}</span>
            @endif
        </div>
    </div>

    <div class="ui-actions" role="cell">
        <a class="ui-action ui-action--icon" href="{{ route('admin.reservations.edit', $r) }}"
           aria-label="Modifica la prenotazione del {{ $dt->format('d/m') }}" title="Modifica">
            @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
        </a>
        <a class="ui-action ui-action--icon" href="{{ route('admin.reservations.show', $r) }}"
           aria-label="Apri la prenotazione del {{ $dt->format('d/m') }}" title="Apri">
            @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 16])
        </a>
    </div>
</article>
