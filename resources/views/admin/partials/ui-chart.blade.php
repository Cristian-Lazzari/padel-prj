{{--
    Un grafico, senza librerie: il disegno è HTML e CSS, la sola parte in SVG è la
    spezzata delle linee. Motivo: un SVG intero scalato a larghezza piena
    rimpicciolisce o ingrandisce anche il testo, mentre qui etichette, legenda e
    tabella restano testo vero, alla dimensione giusta su telefono e su desktop.

    Uso:
    @include('admin.partials.ui-chart', [
        'id'      => 'chartAndamento',
        'type'    => 'line',                 // line | bars | stack
        'labels'  => ['Gen', 'Feb', ...],    // etichette brevi dell'asse
        'full'    => ['Gennaio 2026', ...],  // etichette estese (tooltip e tabella)
        'series'  => [['name' => 'Campi', 'color' => 'var(--viz-1)', 'data' => [12, 8, ...]]],
        'unit'    => ' h',                   // opzionale
        'decimals'=> 1,                      // opzionale
        'axis'    => 'Mese',                 // intestazione della prima colonna in tabella
        'caption' => 'Prenotazioni per mese',// descrizione per lettori di schermo e tabella
    ])
--}}
@php
    $vizId      = $id ?? 'viz-'.uniqid();
    $vizType    = $type ?? 'line';
    $vizType    = in_array($vizType, ['line', 'bars', 'stack'], true) ? $vizType : 'line';
    $vizLabels  = array_values($labels ?? []);
    $vizFull    = array_values($full ?? $vizLabels);
    $vizSeries  = array_values($series ?? []);
    $vizUnit    = $unit ?? '';
    $vizDec     = $decimals ?? 0;
    $vizAxis    = $axis ?? 'Periodo';
    $vizCaption = $caption ?? 'Dati del grafico';
    $vizN       = count($vizLabels);
    $vizCount   = count($vizSeries);

    /* Il massimo dell'asse: la somma della colonna quando le barre sono impilate,
       il valore più alto negli altri casi. */
    $vizMax  = 0;
    $vizSum  = 0;
    for ($i = 0; $i < $vizN; $i++) {
        $column = 0;
        foreach ($vizSeries as $serie) {
            $value   = (float) ($serie['data'][$i] ?? 0);
            $vizSum += abs($value);
            $column  = $vizType === 'stack' ? $column + $value : max($column, $value);
        }
        $vizMax = max($vizMax, $column);
    }

    /* Tacche su numeri tondi (0 / 150 / 300 / 450), mai 0 / 3,7 / 7,4: si sceglie
       il passo su una scaletta di valori leggibili e poi si contano le tacche, così
       il tetto resta vicino al massimo invece di lasciare mezzo grafico vuoto. */
    if ($vizMax <= 0) {
        $vizPasso = 1;
    } else {
        $grezzo  = $vizMax / 4;
        $ordine  = pow(10, floor(log10($grezzo)));
        $scaletta = [1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10];

        $vizPasso = 10 * $ordine;
        foreach ($scaletta as $gradino) {
            if ($gradino * $ordine >= $grezzo) {
                $vizPasso = $gradino * $ordine;
                break;
            }
        }

        // Su dati interi anche il passo resta intero: mezze prenotazioni non esistono
        if ($vizDec === 0) {
            $vizPasso = max(1, ceil($vizPasso));
        }
    }
    $vizTacche   = max(1, (int) ceil($vizMax / $vizPasso));
    $vizTetto    = $vizPasso * $vizTacche;
    $vizPassoDec = fmod($vizPasso, 1) == 0 ? 0 : 1;

    /* Le etichette dell'asse X si diradano: al massimo una dozzina, mai sovrapposte. */
    $vizOgni = max(1, (int) ceil($vizN / 12));

    $vizNum = fn ($v, $dec = null) => number_format((float) $v, $dec ?? $vizDec, ',', '.');

    /* Ascissa del centro della colonna, in percentuale: barre e linee condividono
       la stessa griglia, così le etichette valgono per entrambe. */
    $vizX = fn ($i) => $vizN > 0 ? ($i + 0.5) / $vizN * 100 : 50;
    $vizY = fn ($v) => $vizTetto > 0 ? min(100, max(0, (float) $v / $vizTetto * 100)) : 0;

    $vizJson = [
        'labels' => $vizFull,
        'unit'   => $vizUnit,
        'dec'    => $vizDec,
        'top'    => $vizTetto,
        'type'   => $vizType,
        'series' => array_map(fn ($s) => [
            'name'  => (string) $s['name'],
            'color' => (string) $s['color'],
            'data'  => array_map(fn ($v) => (float) $v, array_values($s['data'] ?? [])),
        ], $vizSeries),
    ];
@endphp

@if ($vizN === 0 || $vizSum <= 0)
    <p class="viz__void">Nessun dato in questo periodo.</p>
@else
<figure class="viz viz--{{ $vizType }}" id="{{ $vizId }}" data-viz>

    @if ($vizCount > 1)
        <div class="viz__legend">
            @foreach ($vizSeries as $serie)
                <span class="viz__key"><i style="--k: {{ $serie['color'] }}"></i>{{ $serie['name'] }}</span>
            @endforeach
        </div>
    @endif

    <div class="viz__frame">
        <div class="viz__axis" aria-hidden="true">
            @for ($t = $vizTacche; $t >= 0; $t--)
                <span style="top: {{ round((1 - $t / $vizTacche) * 100, 3) }}%">{{ $vizNum($vizPasso * $t, $vizPassoDec) }}</span>
            @endfor
        </div>

        {{-- role="group" e non "img": dentro c'è la lettura al passaggio, che è
             una regione viva, e un'immagine renderebbe presentazionale il contenuto --}}
        <div class="viz__plot" tabindex="0" role="group"
             aria-label="{{ $vizCaption }}. Frecce sinistra e destra per scorrere i valori; l'elenco completo è nella tabella qui sotto.">

            <div class="viz__grid" aria-hidden="true">
                @for ($t = 0; $t <= $vizTacche; $t++)
                    <i class="{{ $t === 0 ? 'is-base' : '' }}" style="bottom: {{ round($t / $vizTacche * 100, 3) }}%"></i>
                @endfor
            </div>

            <span class="viz__band" aria-hidden="true"></span>

            @if ($vizType === 'line')
                <svg class="viz__svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                    @foreach ($vizSeries as $serie)
                        @php
                            $punti = [];
                            for ($i = 0; $i < $vizN; $i++) {
                                $punti[] = round($vizX($i), 3).','.round(100 - $vizY($serie['data'][$i] ?? 0), 3);
                            }
                            $tracciato = implode(' ', $punti);
                        @endphp

                        {{-- Con una serie sola la campitura aiuta a leggere il volume; con più serie sporcherebbe --}}
                        @if ($vizCount === 1)
                            <polygon class="viz__area" fill="{{ $serie['color'] }}"
                                     points="{{ round($vizX(0), 3) }},100 {{ $tracciato }} {{ round($vizX($vizN - 1), 3) }},100"></polygon>
                        @endif

                        <polyline class="viz__line" points="{{ $tracciato }}"
                                  fill="none" stroke="{{ $serie['color'] }}" stroke-width="2"
                                  stroke-linejoin="round" stroke-linecap="round"
                                  vector-effect="non-scaling-stroke"></polyline>
                    @endforeach
                </svg>

                {{-- Pallini di fine serie: in HTML e non in SVG, così lo schiacciamento
                     orizzontale del viewBox non li deforma in ellissi. --}}
                @foreach ($vizSeries as $serie)
                    <i class="viz__dot" aria-hidden="true"
                       style="--k: {{ $serie['color'] }}; left: {{ round($vizX($vizN - 1), 3) }}%; bottom: {{ round($vizY($serie['data'][$vizN - 1] ?? 0), 3) }}%"></i>
                @endforeach
                @foreach ($vizSeries as $serie)
                    <i class="viz__dot viz__dot--live" aria-hidden="true" hidden style="--k: {{ $serie['color'] }}"></i>
                @endforeach

                <span class="viz__cross" aria-hidden="true" hidden></span>
            @else
                <div class="viz__cols" aria-hidden="true" style="--m: {{ max(1, $vizCount) }}">
                    @for ($i = 0; $i < $vizN; $i++)
                        @php
                            /* L'ultima serie non nulla porta l'angolo arrotondato: è la
                               cima della pila, le altre restano squadrate. */
                            $cima = null;
                            foreach ($vizSeries as $k => $serie) {
                                if ((float) ($serie['data'][$i] ?? 0) > 0) { $cima = $k; }
                            }
                        @endphp
                        <div class="viz__col">
                            @foreach ($vizSeries as $k => $serie)
                                @php $valore = (float) ($serie['data'][$i] ?? 0); @endphp
                                @continue($valore <= 0)
                                <span class="viz__bar {{ $vizType === 'stack' && $k === $cima ? 'is-top' : '' }}"
                                      style="--k: {{ $serie['color'] }}; --h: {{ round($vizY($valore), 3) }}%"></span>
                            @endforeach
                        </div>
                    @endfor
                </div>
            @endif

            <div class="viz__tip" role="status" aria-live="polite" hidden></div>
        </div>

        <div class="viz__x" aria-hidden="true">
            @foreach ($vizLabels as $i => $label)
                <span>{{ $i % $vizOgni === 0 ? $label : '' }}</span>
            @endforeach
        </div>
    </div>

    {{-- Il gemello leggibile del grafico: ogni valore resta raggiungibile senza passarci sopra --}}
    <details class="ui-data">
        <summary>
            <span>{{ $vizCaption }}: i numeri</span>
            @include('admin.partials.ui-icon', ['name' => 'chevron-down', 'size' => 16])
        </summary>
        <div class="ui-data__scroll">
            <table>
                <thead>
                    <tr>
                        <th scope="col">{{ $vizAxis }}</th>
                        @foreach ($vizSeries as $serie)
                            <th scope="col">{{ $serie['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vizFull as $i => $label)
                        <tr>
                            <th scope="row">{{ $label }}</th>
                            @foreach ($vizSeries as $serie)
                                <td>{{ $vizNum($serie['data'][$i] ?? 0) }}{{ $vizUnit }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>

    <script type="application/json" class="viz__json">@json($vizJson, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
</figure>
@endif

@once
<style>
/* =========================================================================
   GRAFICI
   Le tinte --viz-1..5 e --viz-surface arrivano dai token del design system
   (ui-style): sono le stesse che il Calendario usa per i suoi segni.
   L'ordine è fisso: la stessa serie tiene lo stesso colore in ogni grafico.
   ========================================================================= */
.ui-page{ --viz-h: 240px; }

.viz{ display: grid; gap: 14px; margin: 0; }
.viz__void{ padding: 24px 0; font-size: 13.5px; color: var(--ui-ink-soft); }

/* ---------- Legenda ---------- */
.viz__legend{ display: flex; flex-wrap: wrap; gap: 8px 18px; font-size: 13px; color: var(--ui-ink-soft); }
.viz__key{ display: inline-flex; align-items: center; gap: 8px; }
.viz__key i{ width: 12px; height: 12px; border-radius: 3px; background: var(--k); }
/* La chiave imita il segno: rettangolo per le barre, tratto per le linee */
.viz--line .viz__key i{ width: 16px; height: 3px; border-radius: 999px; }

/* ---------- Impianto ---------- */
.viz__frame{
    display: grid;
    grid-template-columns: max-content minmax(0, 1fr);
    column-gap: 12px;
    row-gap: 8px;
}
.viz__axis{ grid-column: 1; grid-row: 1; position: relative; height: var(--viz-h); }
.viz__axis span{
    position: absolute;
    right: 0;
    transform: translateY(-50%);
    font-family: var(--ui-mono);
    font-size: 11px;
    color: var(--ui-ink-soft);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}
.viz__plot{ grid-column: 2; grid-row: 1; position: relative; height: var(--viz-h); border-radius: 10px; }

.viz__grid{ position: absolute; inset: 0; }
/* Righe continue e sottilissime: tratteggiarle le farebbe leggere come soglie */
.viz__grid i{ position: absolute; left: 0; right: 0; height: 1px; background: var(--ui-rule); }
.viz__grid i.is-base{ background: var(--ui-mute-dim); }

.viz__x{
    grid-column: 2;
    grid-row: 2;
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: 1fr;
    font-family: var(--ui-mono);
    font-size: 11px;
    color: var(--ui-ink-soft);
}
.viz__x span{ text-align: center; white-space: nowrap; }

/* ---------- Linee ---------- */
.viz__svg{ position: absolute; inset: 0; width: 100%; height: 100%; overflow: visible; }
.viz__area{ opacity: .1; }
.viz__dot{
    position: absolute;
    width: 9px; height: 9px;
    margin: 0 0 -4.5px -4.5px;
    border-radius: 50%;
    background: var(--k);
    /* anello nel colore della superficie: il pallino resta leggibile anche
       dove due serie si incrociano */
    box-shadow: 0 0 0 2px var(--viz-surface);
}

/* ---------- Barre ---------- */
.viz__cols{
    position: absolute;
    inset: 0;
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: 1fr;
    align-items: end;
}
.viz__col{
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 2px;
    height: 100%;
}
.viz__bar{
    width: min(24px, calc(100% / var(--m, 1) - 3px));
    height: var(--h);
    min-height: 2px;
    border-radius: 4px 4px 0 0;
    background: var(--k);
}
/* Pila: i segmenti si toccano, li separa un vuoto di 2px sottratto all'altezza
   (mai un bordo: sarebbe inchiostro che non è dato) */
.viz--stack .viz__col{ flex-direction: column-reverse; align-items: center; justify-content: flex-start; gap: 0; }
.viz--stack .viz__bar{ width: min(24px, 62%); border-radius: 0; }
.viz--stack .viz__bar + .viz__bar{ margin-bottom: 2px; height: calc(var(--h) - 2px); }
.viz--stack .viz__bar.is-top{ border-radius: 4px 4px 0 0; }

/* ---------- Lettura al passaggio ---------- */
.viz__band{
    position: absolute;
    top: 0; bottom: 0;
    border-radius: 10px;
    background: var(--ui-surface-2);
    opacity: 0;
    pointer-events: none;
    transition: opacity .12s ease;
}
.viz__cross{
    position: absolute;
    top: 0; bottom: 0;
    width: 1px;
    background: rgba(216, 221, 232, .3);
    pointer-events: none;
}
.viz__tip{
    position: absolute;
    top: 6px;
    z-index: 3;
    min-width: 132px;
    max-width: 240px;
    padding: 10px 13px;
    border-radius: 16px;
    /* tinta piena: sotto ci passano le barre */
    background: #0d0640;
    box-shadow: 0 12px 30px rgba(0, 0, 0, .45);
    pointer-events: none;
}
.viz__tip b{
    display: block;
    margin-bottom: 7px;
    font-family: var(--ui-mono);
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--ui-ink-soft);
}
.viz__tip dl{ display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 5px 14px; margin: 0; }
.viz__tip dt{
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--ui-ink-soft);
}
.viz__tip dt i{ width: 12px; height: 3px; border-radius: 999px; background: var(--k); flex-shrink: 0; }
/* Il numero è quello che si cerca: pieno contrasto, il nome resta di servizio */
.viz__tip dd{
    margin: 0;
    font-family: var(--ui-mono);
    font-size: 13px;
    font-weight: 700;
    text-align: right;
    font-variant-numeric: tabular-nums;
    color: var(--ui-ink);
}

@media (max-width: 640px){
    .ui-page{ --viz-h: 190px; }
    .viz__frame{ column-gap: 8px; }
    /* su schermo stretto un riquadro largo coprirebbe tutto il grafico */
    .viz__tip{ max-width: 190px; padding: 9px 11px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.viz[data-viz]').forEach((figura) => {
        const sorgente = figura.querySelector('.viz__json');
        const riquadro = figura.querySelector('.viz__plot');
        if (!sorgente || !riquadro) return;

        let dati;
        try { dati = JSON.parse(sorgente.textContent); } catch (e) { return; }

        const n = (dati.labels || []).length;
        if (!n) return;

        const tip   = figura.querySelector('.viz__tip');
        const banda = figura.querySelector('.viz__band');
        const croce = figura.querySelector('.viz__cross');
        const punti = Array.from(figura.querySelectorAll('.viz__dot--live'));
        let attivo  = -1;

        const numero = (v) => Number(v).toLocaleString('it-IT', {
            minimumFractionDigits: dati.dec,
            maximumFractionDigits: dati.dec,
        });

        function mostra(i) {
            if (i < 0 || i >= n) return nascondi();
            attivo = i;

            const centro = (i + 0.5) / n * 100;

            // I nomi delle serie arrivano dai dati: sempre come testo, mai come HTML
            tip.textContent = '';
            const titolo = document.createElement('b');
            titolo.textContent = dati.labels[i];
            tip.appendChild(titolo);

            const elenco = document.createElement('dl');
            dati.series.forEach((serie) => {
                const nome = document.createElement('dt');
                const segno = document.createElement('i');
                segno.style.setProperty('--k', serie.color);
                nome.appendChild(segno);
                nome.appendChild(document.createTextNode(serie.name));

                const valore = document.createElement('dd');
                valore.textContent = numero(serie.data[i] || 0) + (dati.unit || '');

                elenco.appendChild(nome);
                elenco.appendChild(valore);
            });
            tip.appendChild(elenco);

            // Il riquadro resta dentro il grafico: lo si misura e lo si trattiene
            // ai bordi, altrimenti su telefono sborderebbe dalla pagina
            tip.hidden = false;
            const largo = riquadro.clientWidth;
            const suo   = tip.offsetWidth;
            tip.style.left = Math.round(Math.min(Math.max(0, centro / 100 * largo - suo / 2), Math.max(0, largo - suo))) + 'px';

            if (dati.type === 'line') {
                croce.style.left = centro + '%';
                croce.hidden = false;
                punti.forEach((punto, k) => {
                    const serie = dati.series[k];
                    if (!serie) return;
                    const alto = dati.top > 0 ? Math.min(100, Math.max(0, (serie.data[i] || 0) / dati.top * 100)) : 0;
                    punto.style.left = centro + '%';
                    punto.style.bottom = alto + '%';
                    punto.hidden = false;
                });
            } else {
                banda.style.left = (i / n * 100) + '%';
                banda.style.width = (100 / n) + '%';
                banda.style.opacity = '1';
            }
        }

        function nascondi() {
            attivo = -1;
            tip.hidden = true;
            if (croce) croce.hidden = true;
            if (banda) banda.style.opacity = '0';
            punti.forEach((punto) => { punto.hidden = true; });
        }

        function indiceDa(clientX) {
            const misure = riquadro.getBoundingClientRect();
            if (!misure.width) return 0;
            return Math.min(n - 1, Math.max(0, Math.floor((clientX - misure.left) / misure.width * n)));
        }

        riquadro.addEventListener('pointermove', (e) => mostra(indiceDa(e.clientX)));
        riquadro.addEventListener('pointerdown', (e) => mostra(indiceDa(e.clientX)));
        riquadro.addEventListener('pointerleave', nascondi);
        // Su telefono non esiste l'uscita del puntatore: si chiude toccando fuori
        document.addEventListener('pointerdown', (e) => {
            if (!riquadro.contains(e.target)) nascondi();
        });
        riquadro.addEventListener('blur', nascondi);
        riquadro.addEventListener('focus', () => mostra(attivo < 0 ? 0 : attivo));

        // Da tastiera il grafico è un solo punto di tabulazione: le frecce
        // scorrono le colonne, e si legge esattamente quello che legge il mouse
        riquadro.addEventListener('keydown', (e) => {
            const passi = { ArrowRight: 1, ArrowLeft: -1 };
            if (e.key in passi) {
                mostra(Math.min(n - 1, Math.max(0, (attivo < 0 ? 0 : attivo) + passi[e.key])));
                e.preventDefault();
            } else if (e.key === 'Home') {
                mostra(0); e.preventDefault();
            } else if (e.key === 'End') {
                mostra(n - 1); e.preventDefault();
            } else if (e.key === 'Escape') {
                nascondi();
            }
        });
    });
});
</script>
@endonce
