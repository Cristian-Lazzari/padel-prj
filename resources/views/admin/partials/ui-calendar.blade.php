{{--
    CSS della schermata Calendario. Sta in un partial suo (e non nel foglio del
    design system) perché serve a una famiglia di pagine sola.
    Le classi create dal JavaScript — .fields, .field, .time, .booked, .lesson,
    .trophy, .trainer_slot, .fixed_slot, .slot-checkbox, .middle, .null_p,
    .time_b, .booking_subject, .slot_icon, .slot_tag, .bk_2, .bk_3 — sono agganci:
    non si rinominano.
--}}
@once
<style>
/* ---------- I tre (quattro) tipi ----------
   Un colore e un'icona soli per ogni tipo, dal riquadro del giorno alla fascia
   oraria. Le tinte sono quelle dei grafici (--viz-*, dichiarate nei token del
   design system): "lezione" è arancio in Statistiche e arancio qui. */
.cal, .ui-page .fields, .cal__legend{
    --c-match:      var(--viz-1);
    --c-lesson:     var(--viz-2);
    --c-tournament: var(--viz-3);
    --c-dinner:     var(--viz-4);
}

/* ---------- Mese ---------- */
.cal{ display: grid; gap: 10px; }
.cal__head{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.cal__month{
    font-size: 17px;
    font-weight: 700;
    text-transform: capitalize;
    letter-spacing: -.01em;
}
.cal__nav{ display: flex; gap: 8px; }
/* Stessa icona per i due versi: quella indietro è la stessa ruotata */
.cal__nav button:first-child svg{ transform: rotate(180deg); }

.cal__dow{
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
    padding: 0 2px;
    font-family: var(--ui-mono);
    font-size: 10.5px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--ui-ink-soft);
    text-align: center;
}
/* Su telefono l'iniziale basta: "mercoledì" non ci sta in 40px */
.cal__dow span i{ font-style: normal; }
@media (max-width: 620px){ .cal__dow span i{ display: none; } }


.cal__grid{
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}
.cal__day{
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    min-height: 62px;
    padding: 6px 4px;
    border: 0;
    border-radius: 16px;
    /* la fascia del torneo è incollata al bordo: senza ritaglio i suoi angoli
       squadrati sporgerebbero dalle spalle arrotondate del riquadro */
    overflow: hidden;
    background: var(--ui-surface);
    color: var(--ui-ink);
    font-family: inherit;
    cursor: pointer;
    transition: background-color .16s ease, color .16s ease, box-shadow .16s ease;
}
.cal__day:hover{ background: var(--ui-surface-2); }
.cal__day .p_day{ margin: 0; font-size: 16px; font-weight: 600; font-variant-numeric: tabular-nums; }
/* Il giorno di oggi è pieno di accento: è il "sei qui" del calendario */
.cal__day.current{ background: var(--ui-accent); color: var(--c1); }
.cal__day.current .p_day{ font-weight: 700; }
/* Il giorno scelto si segna con un anello, così resta leggibile anche su accento */
.cal__day.selected{ box-shadow: inset 0 0 0 2px var(--ui-accent); background: var(--ui-accent-dim); }
/* Oggi ed è anche il giorno scelto: resta pieno di accento (altrimenti il testo
   scuro finirebbe su fondo scuro) e l'anello diventa blu profondo. */
.cal__day.current.selected{
    background: var(--ui-accent);
    color: var(--c1);
    box-shadow: inset 0 0 0 3px rgba(9, 3, 51, .7);
}
.cal__day.day_off{ opacity: .45; }

/* Un segno per tipo: l'icona porta il colore, il numero resta inchiostro.
   Colorare anche la cifra la renderebbe meno leggibile senza dire niente in più. */
.cal__marks{ display: flex; align-items: center; gap: 5px; flex-wrap: wrap; justify-content: center; }
.cal__mark{
    display: inline-flex;
    align-items: center;
    gap: 2px;
    line-height: 1;
}
.cal__mark b{
    font-family: var(--ui-mono);
    font-size: 10px;
    font-weight: 700;
    color: var(--ui-ink);
    font-variant-numeric: tabular-nums;
}
.cal__mark svg{ flex-shrink: 0; }
.cal__mark--match      svg{ color: var(--c-match); }
.cal__mark--lesson     svg{ color: var(--c-lesson); }
.cal__mark--tournament svg{ color: var(--c-tournament); }
.cal__mark--dinner     svg{ color: var(--c-dinner); }
/* Il pallino del campo è il segno più piccolo: gli altri sono sagome, lui un disco */
.cal__mark--match svg{ width: 8px; height: 8px; }

/* Sul giorno di oggi il fondo è pieno di accento: lì nessuna tinta reggerebbe il
   contrasto, e a distinguere i tipi resta la forma dell'icona (la legenda la spiega). */
.cal__day.current .cal__mark b,
.cal__day.current .cal__mark svg{ color: rgba(9, 3, 51, .82); }

/* Giornata di torneo: una fascia in cima al riquadro, larga quanto il giorno.
   Occupa la giornata intera, quindi non è uno dei segni contati qui sotto. */
.cal__band{
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 5px;
    border-radius: 16px 16px 0 0;
    background: var(--c-tournament);
}
/* Su oggi il fondo è accento: il blu ci starebbe sopra quasi invisibile,
   quindi la fascia si scurisce e resta la sua forma a dirlo. */
.cal__day.current .cal__band{ background: rgba(9, 3, 51, .78); }
/* Riquadri stretti: in 44px "2 campi, 1 lezione, 2 cene" non ci sta leggibile.
   Restano le sole icone — che è la domanda del mese, "che roba c'è quel giorno" —
   e i numeri li dicono l'etichetta per i lettori di schermo e il giorno aperto. */
@media (max-width: 620px){
    /* Il riquadro rinuncia al margine laterale: serve tutto ai segni, che così
       stanno su una riga sola anche quando i tipi sono quattro. */
    .cal__day{ min-height: 58px; padding: 6px 1px; }
    .cal__marks{ gap: 2px; flex-wrap: nowrap; }
    .cal__mark b{ display: none; }
    .cal__mark svg{ width: 10px; height: 10px; }
    .cal__mark--match svg{ width: 7px; height: 7px; }
}

/* Giorno chiuso nella finestra "blocca giorni": è una scelta, non uno stato */
.cal__grid input[type="checkbox"]{ position: absolute; opacity: 0; width: 0; height: 0; }
.cal__grid input[type="checkbox"]:checked + .cal__day{
    background: var(--ui-danger-dim);
    color: var(--ui-danger);
    box-shadow: inset 0 0 0 2px var(--ui-danger);
}
.cal__grid input[type="checkbox"]:focus-visible + .cal__day{ outline: 2px solid var(--c2); outline-offset: 2px; }

/* Puntini della carosello di Bootstrap, ridisegnati */
.ui-page .carousel-indicators{
    position: static;
    margin: 0 0 10px;
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 6px;
}
/* !important: il foglio globale disegna gli indicatori con !important su
   dimensioni, bordo e fondo, e senza rilanciare vincerebbe lui. */
.ui-page .carousel-indicators [data-bs-target]{
    width: 8px !important;
    height: 8px !important;
    margin: 0;
    border: 0 !important;
    border-radius: 50% !important;
    background-color: rgba(216, 221, 232, .12) !important;
    opacity: 1;
    text-indent: 0;
}
.ui-page .carousel-indicators .active{ background-color: #0eb792 !important; }

/* ---------- Legenda ----------
   Vale per il mese e per le fasce: stesse icone, stessi colori nei due posti. */
.cal__legend{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px 18px;
    padding: 14px 18px;
    border-radius: var(--ui-r-row);
    background: var(--ui-surface);
    font-size: 13px;
    color: var(--ui-ink-soft);
}
.cal__legend__title{
    font-family: var(--ui-mono);
    font-size: 10.5px;
    letter-spacing: .14em;
    text-transform: uppercase;
}
.cal__key{ display: inline-flex; align-items: center; gap: 7px; }
.cal__key svg{ flex-shrink: 0; }
.cal__key--match      svg{ color: var(--c-match); width: 10px; height: 10px; }
.cal__key--lesson     svg{ color: var(--c-lesson); }
.cal__key--tournament svg{ color: var(--c-tournament); }
.cal__key--dinner     svg{ color: var(--c-dinner); }
.cal__key--fixed      svg{ color: var(--ui-ink-soft); }
/* Le due voci senza icona mostrano il segno vero: la fascia e il giorno spento */
.cal__key--band i{ width: 16px; height: 5px; border-radius: 999px; background: var(--c-tournament); }
.cal__key--off i{
    width: 16px; height: 16px;
    border-radius: 5px;
    background: var(--ui-surface-2);
    opacity: .45;
}

/* ---------- Banda del torneo ----------
   Sta sopra i campi perché un torneo occupa la giornata, non una fascia. */
#tournamentBand{ display: grid; gap: 10px; margin-top: 20px; }
.tband{
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    padding: 18px 22px;
    border-radius: var(--ui-r-row);
    background: var(--ui-accent-dim);
}
.tband__icon{ color: var(--ui-accent); line-height: 0; }
.tband__body{ flex: 1 1 260px; display: grid; gap: 7px; min-width: 0; }
.tband__name{
    font-size: 17px;
    font-weight: 700;
    letter-spacing: -.01em;
    color: var(--ui-ink);
    text-decoration: none;
}
.tband__name:hover{ color: var(--ui-accent); }
.tband__meta{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px 12px;
    font-size: 13px;
    color: var(--ui-ink-soft);
}
.tband__meter{ flex: 0 0 120px; }
.tband__actions{ display: flex; align-items: center; gap: 8px; }
@media (max-width: 620px){
    .tband__meter{ flex: 1 1 100%; }
    .tband__actions{ flex: 1 1 100%; }
}

/* ---------- Fasce orarie del giorno ----------
   Tutte le regole sono prefissate con .ui-page: app.scss disegna ancora
   .fields .field .time (fondo chiaro e barra a ::after) con selettori a due
   livelli, e senza prefisso vincerebbe il foglio vecchio. */
.ui-page .fields{ display: grid; gap: 18px; margin: 26px 0 0; padding: 0; }
.ui-page .fields h4{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ui-ink);
}
.ui-page .fields h4 span{
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: var(--ui-mono);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0;
    text-transform: none;
    color: var(--ui-accent);
    opacity: 1;
}
.ui-page .fields .null_p{ margin: 0; font-size: 13px; color: var(--ui-ink-soft); opacity: 1; }

.ui-page .fields .field{
    display: flex;
    align-items: center;
    gap: 8px;
    /* la striscia oraria scorre da sola: la pagina non deve mai scorrere in orizzontale */
    overflow-x: auto;
    padding: 12px;
    border-radius: var(--ui-r-row);
    background-color: var(--ui-surface);
    scrollbar-width: thin;
}
.ui-page .fields .field .time{
    position: relative;
    flex: 0 0 auto;
    padding: 0;
    font-size: inherit;
}
/* La vecchia barra di collegamento fra gli slot: qui la durata la dice la pillola.
   !important perché .trophy.booked::after dichiara content e background con
   !important, e senza rilanciare resterebbe la fascia gialla del torneo. */
.ui-page .fields .field .time::after{ content: none !important; background: none !important; box-shadow: none !important; border: 0 !important; }
.ui-page .fields .field .time input[type="checkbox"]{ position: absolute; opacity: 0; width: 0; height: 0; }

.ui-page .fields .field .time label{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 44px;
    min-width: 68px;
    padding: 0 14px;
    border: 0;
    border-radius: var(--ui-pill);
    background-color: var(--ui-surface-2);
    color: var(--ui-ink);
    font-size: 14px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    text-shadow: none;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color .16s ease, color .16s ease, transform .16s ease;
}
.ui-page .fields .field .time label:hover{ background-color: var(--ui-mute-dim); font-size: 14px; }
.ui-page .fields .field .time label:active{ transform: scale(.97); }
.ui-page .fields .field .time input[type="checkbox"]:checked + label{
    background-color: var(--ui-accent);
    background-image: none;
    color: var(--c1);
    font-weight: 700;
}
.ui-page .fields .field .time input[type="checkbox"]:focus-visible + label{ outline: 2px solid var(--c2); outline-offset: 2px; }
/* Mezzo slot: stessa fascia, mezza importanza */
.ui-page .fields .field .time label.middle{
    min-width: 54px;
    height: 38px;
    font-size: 12.5px;
    color: var(--ui-ink-soft);
    background-color: var(--ui-mute-dim);
}

/* Slot occupato: non è una scelta, è un collegamento alla prenotazione.
   Il tipo si legge su tre canali insieme — icona, tinta e parola — perché uno
   solo lascia sempre indietro qualcuno. La tinta di serie è quella del campo. */
.ui-page .fields .field .time a{
    --c-slot: var(--c-match);
    display: inline-flex;
    align-items: center;
    gap: 9px;
    height: 44px;
    padding: 0 15px;
    border-radius: var(--ui-pill);
    /* La tinta del tipo, appena accennata, con il bordo dello stesso colore: due
       prenotazioni vicine di tipo diverso si distinguono a colpo d'occhio.
       La prima coppia di righe è il ripiego per i browser senza color-mix: lì la
       pillola resta neutra e il tipo lo dicono comunque icona e parola. */
    background-color: var(--ui-surface-2);
    box-shadow: inset 0 0 0 1px var(--ui-rule);
    background-color: color-mix(in srgb, var(--c-slot) 18%, transparent);
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--c-slot) 45%, transparent);
    color: var(--ui-ink) !important;
    text-decoration: none;
    white-space: nowrap;
    transition: opacity .16s ease;
}
.ui-page .fields .field .time a:hover{ opacity: .82; }
/* Il colore lo porta l'icona; il testo resta inchiostro, che si legge sempre */
.ui-page .fields .field .time .slot_icon{ display: inline-flex; line-height: 0; color: var(--c-slot); }
.ui-page .fields .field .time .slot_icon--fixed{ color: var(--ui-ink-soft); }
.ui-page .fields .field .time .time_b{ font-size: 14px !important; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--c3) !important; }
.ui-page .fields .field .time .booking_subject{ font-size: 12.5px; font-weight: 600; color: var(--ui-ink-soft); }

/* Lezione e partita di torneo: cambia solo la tinta di partenza, il resto è uguale */
.ui-page .fields .field .time.lesson a{ --c-slot: var(--c-lesson); }
.ui-page .fields .field .time.trophy a{ --c-slot: var(--c-tournament); }

.ui-page .fields .field .time .slot_tag{
    font-family: var(--ui-mono);
    font-size: 10px;
    font-style: normal;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: var(--ui-pill);
    background: rgba(9, 3, 51, .45);
    color: var(--ui-ink-soft);
}
/* Campo fisso ricorrente: anello chiaro, come il tratteggio della versione precedente */
.ui-page .fields .field .time.fixed_slot a{ box-shadow: inset 0 0 0 2px rgba(216, 221, 232, .35); }

/* Fasce strette: sotto una certa larghezza la parola esce dalla pillola, e
   restano icona e tinta (che la legenda ha già spiegato). */
@media (max-width: 560px){
    .ui-page .fields .field .time .slot_tag{ display: none; }
}

/* Slot riservato a un istruttore: righe con il colore del suo flag */
.ui-page .fields .field .time.trainer_slot label{
    background-image: repeating-linear-gradient(
        135deg,
        var(--flag, rgba(216, 221, 232, .12)) 0 10px,
        rgba(216, 221, 232, .10) 10px 20px
    );
    color: var(--c3);
}

/* Prenotazioni lunghe: la pillola cresce con la durata */
.ui-page .fields .field .time.bk_2 a{ min-width: 190px; }
.ui-page .fields .field .time.bk_3 a{ min-width: 250px; }
/* I margini che il foglio vecchio usava per allungare la barra qui non servono */
.ui-page .fields .field .time.bk_2, .ui-page .fields .field .time.bk_3{ margin-right: 0; }

@media (prefers-reduced-motion: reduce){
    .cal__day, .ui-page .fields .field .time label, .ui-page .fields .field .time a{ transition: none; }
}
</style>
@endonce
