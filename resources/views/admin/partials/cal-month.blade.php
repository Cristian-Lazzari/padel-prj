{{--
    La griglia di un solo mese del calendario. È il pezzo che si ricarica in
    ajax quando si cambia mese: fuori restano intestazione, frecce e legenda,
    che non cambiano da un mese all'altro.
    Attende: $m (mese), $tourneiPerGiorno, $dinner_off.
--}}
@php
    $currentDate = date('Y-m-d');

    /* I tipi di prenotazione: un'icona e un colore soli, usati nel riquadro del
       giorno, nella legenda e nelle fasce orarie. Le icone si disegnano una volta
       e non a ogni cella: un @include per ciascuna costerebbe caro. */
    $tipi = [
        'match'      => ['icona' => 'circle-fill',      'uno' => 'prenotazione su campo', 'tanti' => 'prenotazioni su campo'],
        'lesson'     => ['icona' => 'mortarboard-fill', 'uno' => 'lezione',               'tanti' => 'lezioni'],
        'tournament' => ['icona' => 'trophy-fill',      'uno' => 'partita di torneo',     'tanti' => 'partite di torneo'],
        'dinner'     => ['icona' => 'cup-hot-fill',     'uno' => 'cena',                  'tanti' => 'cene'],
    ];

    if (! $dinner_off) {
        unset($tipi['dinner']);
    }

    $segni = [];
    foreach ($tipi as $chiave => $tipo) {
        $segni[$chiave] = view('admin.partials.ui-icon', ['name' => $tipo['icona'], 'size' => 11])->render();
    }

    $mesi = ['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno',
             'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'];
@endphp

<div class="cal__grid">
    @foreach ($m['days'] as $d)
        @php
            $giornata = $tourneiPerGiorno[$d['date']] ?? [];

            /* Quello che dice il lettore di schermo: gli stessi numeri
               che si vedono, a parole. Niente title da scoprire col mouse. */
            $voce = [];
            foreach ($tipi as $chiave => $tipo) {
                $quanti = (int) $d['reserved_'.$chiave];
                if ($quanti > 0) {
                    $voce[] = $quanti.' '.($quanti === 1 ? $tipo['uno'] : $tipo['tanti']);
                }
            }
            if ($giornata) {
                $voce[] = 'giornata di torneo: '.collect($giornata)->pluck('nome')->implode(', ');
            }
            if (! $d['status']) {
                $voce[] = 'circolo chiuso';
            }

            $segnati = $d['reserved_match'] + $d['reserved_lesson'] + $d['reserved_tournament']
                     + ($dinner_off ? $d['reserved_dinner'] : 0);
        @endphp
        <button type="button" data-day='@json($d)'
                class="cal__day @if ($currentDate === $d['date']) current @endif @if (! $d['status']) day_off @endif"
                style="grid-column-start: {{ $d['day_w'] }}"
                aria-label="{{ $d['day'] }} {{ $mesi[$m['month']] }}{{ $voce ? ' — '.implode(', ', $voce) : ', niente in programma' }}"
                @if ($voce) title="{{ implode(' · ', $voce) }}" @endif>
            {{-- Fascia in cima: il torneo occupa la giornata intera, non una fascia
                 oraria, e il blu è lo stesso delle sue partite più sotto --}}
            @if ($giornata)
                <span class="cal__band" aria-hidden="true"></span>
            @endif

            <p class="p_day">{{ $d['day'] }}</p>

            @if ($segnati > 0)
                <span class="cal__marks" aria-hidden="true">
                    @foreach ($tipi as $chiave => $tipo)
                        @continue($d['reserved_'.$chiave] < 1)
                        <span class="cal__mark cal__mark--{{ $chiave }}">
                            {!! $segni[$chiave] !!}<b>{{ $d['reserved_'.$chiave] }}</b>
                        </span>
                    @endforeach
                </span>
            @endif
        </button>
    @endforeach
</div>
