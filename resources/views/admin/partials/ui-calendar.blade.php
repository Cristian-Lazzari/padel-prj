{{--
    CSS della schermata Calendario. Sta in un partial suo (e non nel foglio del
    design system) perché serve a una famiglia di pagine sola.
    Le classi create dal JavaScript — .fields, .field, .time, .booked, .lesson,
    .trophy, .trainer_slot, .fixed_slot, .slot-checkbox, .middle, .null_p,
    .time_b, .booking_subject, .bk_2, .bk_3 — sono agganci: non si rinominano.
--}}
@once
<style>
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
.cal__day.current.selected{ box-shadow: inset 0 0 0 2px var(--c1); }
.cal__day.day_off{ opacity: .45; }

.cal__marks{ display: flex; align-items: center; gap: 6px; flex-wrap: wrap; justify-content: center; }
.cal__mark{
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-family: var(--ui-mono);
    font-size: 10px;
    font-weight: 700;
    color: var(--ui-ink-soft);
}
.cal__day.current .cal__mark{ color: rgba(9, 3, 51, .75); }
.cal__mark svg{ width: 10px; height: 10px; }

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
.ui-page .carousel-indicators [data-bs-target]{
    width: 8px; height: 8px;
    margin: 0;
    border: 0;
    border-radius: 50%;
    background: var(--ui-mute-dim);
    opacity: 1;
    text-indent: 0;
}
.ui-page .carousel-indicators .active{ background: var(--ui-accent); }

/* ---------- Fasce orarie del giorno ---------- */
.fields{ display: grid; gap: 18px; }
.fields h4{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.fields h4 span{
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: var(--ui-mono);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0;
    text-transform: none;
    color: var(--ui-accent);
}
.fields h4 span svg{ width: 13px; height: 13px; fill: currentColor; }
.fields .null_p{ margin: 0; font-size: 13px; color: var(--ui-ink-soft); }

.field{
    display: flex;
    align-items: center;
    gap: 8px;
    /* la striscia oraria scorre da sola: la pagina non deve mai scorrere in orizzontale */
    overflow-x: auto;
    padding: 12px;
    border-radius: var(--ui-r-row);
    background: var(--ui-surface);
    scrollbar-width: thin;
}
.time{ position: relative; flex: 0 0 auto; }
.time input[type="checkbox"]{ position: absolute; opacity: 0; width: 0; height: 0; }

.time label{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 44px;
    min-width: 68px;
    padding: 0 14px;
    border-radius: var(--ui-pill);
    background: var(--ui-surface-2);
    color: var(--ui-ink);
    font-size: 14px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color .16s ease, color .16s ease, transform .16s ease;
}
.time label:hover{ background: var(--ui-mute-dim); }
.time label:active{ transform: scale(.97); }
.time input[type="checkbox"]:checked + label{ background: var(--ui-accent); color: var(--c1); font-weight: 700; }
.time input[type="checkbox"]:focus-visible + label{ outline: 2px solid var(--c2); outline-offset: 2px; }
/* Mezzo slot: stessa fascia, mezza importanza */
.time label.middle{ min-width: 54px; height: 38px; font-size: 12.5px; color: var(--ui-ink-soft); }

/* Slot occupato: non è cliccabile come scelta, è un collegamento alla prenotazione */
.time.booked a, .time.lesson a{
    display: inline-flex;
    align-items: center;
    gap: 10px;
    height: 44px;
    padding: 0 16px;
    border-radius: var(--ui-pill);
    background: var(--ui-accent-dim);
    color: var(--ui-accent);
    text-decoration: none;
    white-space: nowrap;
    transition: opacity .16s ease;
}
.time.booked a:hover, .time.lesson a:hover{ opacity: .82; color: var(--ui-accent); }
.time .time_b{ font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--ui-ink); }
.time .booking_subject{ font-size: 12.5px; font-weight: 600; }

/* Lezione: ambra. Torneo: accento con la coppa. */
.time.lesson a{ background: var(--ui-warn-dim); color: var(--ui-warn); }
.time.trophy a::before{ content: "🏆"; font-size: 13px; }
/* Campo fisso ricorrente: bordo tratteggiato, come nella versione precedente */
.time.fixed_slot a{ box-shadow: inset 0 0 0 2px rgba(216, 221, 232, .35); }

/* Slot riservato a un istruttore: righe con il colore del suo flag */
.time.trainer_slot label{
    background: repeating-linear-gradient(
        135deg,
        var(--flag, var(--ui-mute-dim)) 0 10px,
        rgba(216, 221, 232, .10) 10px 20px
    );
    color: var(--c3);
}

/* Prenotazioni lunghe: la pillola cresce con la durata */
.time.bk_2 a{ min-width: 190px; }
.time.bk_3 a{ min-width: 250px; }

@media (prefers-reduced-motion: reduce){
    .cal__day, .time label, .time a{ transition: none; }
}
</style>
@endonce
