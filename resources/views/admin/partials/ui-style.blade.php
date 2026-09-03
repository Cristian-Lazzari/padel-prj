{{--
    Design system del back office: token, shell, tipografia e catalogo componenti.
    Sta in un partial e non nel foglio globale perché app.scss porta ancora tutte le
    classi storiche (.my_btn_*, .res_item, .newtable): tenere i due linguaggi separati
    permette di convertire una famiglia di pagine alla volta senza toccare le altre.
    @once evita che due viste della stessa schermata lo stampino due volte.
--}}
@once
<style>
/* =========================================================================
   1. TOKEN
   Dichiarati sul namespace di pagina e non su :root: il foglio globale
   inverte --c1/--c3 quando data-theme="dark", e il back office deve restare
   scuro qualunque cosa dica il tema salvato nel localStorage.
   ========================================================================= */
.ui-page{
    /* I tre colori del marchio, riaffermati in locale contro l'inversione del tema */
    --c1: #090333;
    --c2: #0eb792;
    --c3: #d8dde8;

    /* Scala tipografica fluida */
    --fs-100: clamp(14px, 0.86rem + 0.12vw, 15px);
    --fs-200: clamp(16px, 0.98rem + 0.22vw, 18px);
    --fs-300: clamp(18px, 1.08rem + 0.34vw, 20px);
    --fs-400: clamp(20px, 1.18rem + 0.55vw, 24px);
    --fs-500: clamp(24px, 1.42rem + 0.95vw, 30px);
    --fs-600: clamp(28px, 1.72rem + 1.4vw, 38px);
    --fs-700: clamp(32px, 2.05rem + 3vw, 64px);

    --ui-surface:    rgba(216, 221, 232, .055);
    --ui-surface-2:  rgba(216, 221, 232, .095);
    --ui-ink:        var(--c3);
    --ui-ink-soft:   rgba(216, 221, 232, .62);
    --ui-accent:     var(--c2);
    --ui-accent-dim: rgba(14, 183, 146, .16);
    --ui-warn:       #f0b64a;
    --ui-warn-dim:   rgba(240, 182, 74, .16);
    --ui-danger:     #ef8181;
    --ui-danger-dim: rgba(233, 90, 90, .16);
    --ui-mute-dim:   rgba(216, 221, 232, .12);
    --ui-rule:       rgba(216, 221, 232, .1);

    /* Tavolozza dei dati: le stesse cinque tinte per i grafici delle Statistiche
       e per i segni del Calendario, così "lezione" è arancio dappertutto.
       Non sono scelte a occhio: contrasto >= 3:1 sul fondo (#090333) e sulla
       superficie dei pannelli (#140f3d), e coppie vicine distinguibili anche
       con daltonismo. Se cambiano, vanno rivalidate. */
    --viz-1: #0ca67f;   /* verde acqua: il colore del marchio, un gradino più scuro */
    --viz-2: #db6f2c;   /* arancio */
    --viz-3: #6688e8;   /* blu */
    --viz-4: #c94a76;   /* magenta */
    --viz-5: #b58b1c;   /* ocra */
    /* La superficie su cui poggiano: serve agli anelli e ai distacchi */
    --viz-surface: #140f3d;

    --ui-r-row: 20px;
    --ui-r-box: 28px;
    --ui-pill:  999px;

    --ui-mono: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace;
}

/* =========================================================================
   2. SHELL
   ========================================================================= */
body.ui-body{
    background-color: #090333;
    color: #d8dde8;
    /* app.scss forza padding-bottom:10% e overflow:scroll sul body: il primo
       raddoppierebbe lo spazio già riservato alla nav, il secondo rende il body
       il contenitore di scorrimento e rompe position:sticky delle barre. */
    padding-bottom: 0 !important;
    overflow: visible !important;
    min-height: 100dvh;
}

.ui-page{
    margin: 0 auto;
    max-width: 1350px;
    /* Il fondo generoso serve alla navigazione che galleggia sopra il contenuto */
    padding: clamp(28px, 3vw, 40px) clamp(18px, 2.8vw, 42px) calc(132px + env(safe-area-inset-bottom));
    display: grid;
    gap: 18px;
    color: var(--ui-ink);
    font-size: var(--fs-100);
    line-height: 1.5;
}
/* Basta un nome lungo per creare scorrimento orizzontale su telefono */
.ui-page *{ min-width: 0; }

@media (max-width: 820px){
    .ui-page{ padding-bottom: calc(72px + 34px + env(safe-area-inset-bottom)); }
}

.ui-page a{ color: inherit; text-decoration: none; }
.ui-page a:hover{ color: inherit; }
.ui-page p{ margin: 0; }
.ui-page h1, .ui-page h2, .ui-page h3{ margin: 0; font-family: inherit; }
.ui-page svg{ flex-shrink: 0; }

/* Focus universale: mai outline:none senza rimpiazzo */
.ui-page :focus-visible,
.ui-nav :focus-visible,
.ui-sheet :focus-visible{
    outline: 2px solid var(--c2, #0eb792);
    outline-offset: 2px;
    border-radius: 6px;
}

/* hidden deve battere il display dei componenti: .ui-row e .ui-list sono grid,
   e senza questa riga l'attributo non nasconderebbe niente. */
.ui-page [hidden]{ display: none !important; }

/* Etichette per sole tecnologie assistive: clip, mai display:none */
.ui-vh{
    position: absolute !important;
    width: 1px; height: 1px;
    margin: -1px; padding: 0;
    overflow: hidden;
    clip: rect(0 0 0 0);
    clip-path: inset(50%);
    white-space: nowrap;
    border: 0;
}

/* =========================================================================
   3. BRICIOLE E INTESTAZIONE DI PAGINA
   ========================================================================= */
.ui-crumbs{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    font-family: var(--ui-mono);
    font-size: 11px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--ui-ink-soft);
}
.ui-crumbs a:hover{ color: var(--ui-ink); }
.ui-crumbs__sep{ opacity: .5; }
.ui-crumbs b{ color: var(--ui-ink); font-weight: 700; }

.ui-head{
    display: flex;
    align-items: end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px 18px;
}
.ui-head__title h1{
    font-size: clamp(26px, 4vw, 34px);
    font-weight: 700;
    letter-spacing: -.025em;
    line-height: 1.1;
}
.ui-head__count{
    display: flex;
    flex-wrap: wrap;
    gap: 4px 16px;
    margin-top: 8px;
    font-family: var(--ui-mono);
    font-size: 12px;
    color: var(--ui-ink-soft);
    font-variant-numeric: tabular-nums;
}
.ui-head__count b{ color: var(--ui-accent); font-weight: 700; }
.ui-head__actions{ display: flex; flex-wrap: wrap; gap: 10px; }

/* =========================================================================
   4. BOTTONI
   ========================================================================= */
.ui-btn{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 44px;
    padding: 0 20px;
    border: 0;
    border-radius: var(--ui-pill);
    background: var(--ui-surface);
    color: var(--ui-ink);
    font-size: 14.5px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: background-color .16s ease, color .16s ease, opacity .16s ease, transform .16s ease;
}
.ui-btn:hover{ background: var(--ui-surface-2); color: var(--ui-ink); }
.ui-btn:active{ transform: scale(.97); }
.ui-btn--primary{ background: var(--ui-accent); color: var(--c1); font-weight: 700; }
.ui-btn--primary:hover{ background: var(--ui-accent); color: var(--c1); opacity: .88; }
.ui-btn--danger:hover{ background: var(--ui-danger-dim); color: var(--ui-danger); }
.ui-btn[disabled], .ui-btn.is-disabled{ opacity: .45; pointer-events: none; }

/* Azione dentro una riga: più bassa, 38px è il minimo tollerato dal tocco qui */
.ui-action{
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-width: 38px;
    height: 38px;
    padding: 0 14px;
    border: 0;
    border-radius: var(--ui-pill);
    background: var(--ui-surface-2);
    color: var(--ui-ink);
    font-size: 13.5px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: background-color .16s ease, color .16s ease, transform .16s ease;
}
.ui-action:hover{ background: var(--ui-accent-dim); color: var(--ui-accent); }
.ui-action:active{ transform: scale(.97); }
.ui-action--danger:hover{ background: var(--ui-danger-dim); color: var(--ui-danger); }
.ui-action--icon{ padding: 0; width: 38px; }

/* =========================================================================
   5. AVVISI
   ========================================================================= */
.ui-flash{
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 22px;
    border-radius: var(--ui-r-row);
    background: var(--ui-accent-dim);
    color: var(--ui-ink);
    font-size: 14.5px;
    font-weight: 600;
}
.ui-flash svg{ color: var(--ui-accent); }
.ui-flash--error{ background: var(--ui-danger-dim); }
.ui-flash--error svg{ color: var(--ui-danger); }
.ui-flash--warn{ background: var(--ui-warn-dim); }
.ui-flash--warn svg{ color: var(--ui-warn); }
.ui-flash ul{ margin: 0; padding-left: 18px; font-weight: 500; }
.ui-flash__close{
    margin-left: auto;
    border: 0;
    background: transparent;
    color: inherit;
    opacity: .6;
    cursor: pointer;
    padding: 4px;
    line-height: 0;
}
.ui-flash__close:hover{ opacity: 1; }

/* =========================================================================
   6. BARRA DEI FILTRI
   ========================================================================= */
.ui-filters{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px 14px;
}
.ui-search{ position: relative; flex: 1 1 240px; display: flex; }
.ui-search svg{
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--ui-ink-soft);
    pointer-events: none;
}
.ui-search input{
    width: 100%;
    min-height: 44px;
    padding: 10px 18px 10px 46px;
    border: 1px solid transparent;
    border-radius: var(--ui-pill);
    background: var(--ui-surface-2);
    color: var(--ui-ink);
    font-size: 14.5px;
    font-family: inherit;
}
.ui-search input::placeholder{ color: rgba(216, 221, 232, .38); }
.ui-search input:focus{ outline: none; border-color: var(--ui-accent); background: var(--ui-surface); }

.ui-chip{
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 38px;
    padding: 0 16px;
    border: 0;
    border-radius: var(--ui-pill);
    background: var(--ui-surface);
    color: var(--ui-ink-soft);
    font-size: 13.5px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: background-color .16s ease, color .16s ease, transform .16s ease;
}
.ui-chip:hover{ background: var(--ui-surface-2); color: var(--ui-ink); }
.ui-chip:active{ transform: scale(.97); }
/* Deve saltare all'occhio quale filtro è acceso */
.ui-chip.is-on{ background: var(--ui-accent); color: var(--c1); font-weight: 700; }
.ui-chip.is-on:hover{ background: var(--ui-accent); color: var(--c1); opacity: .88; }
.ui-chip__count{
    font-family: var(--ui-mono);
    font-size: 11.5px;
    opacity: .8;
    font-variant-numeric: tabular-nums;
}

/* =========================================================================
   7. TABELLA-LISTA
   La riga è l'unica superficie: nessun contenitore attorno all'elenco.
   Le larghezze di colonna arrivano inline in --ui-cols sul contenitore.
   ========================================================================= */
.ui-list{ display: grid; gap: 8px; }

.ui-list__head{
    display: none;
    padding: 0 24px 4px;
    font-family: var(--ui-mono);
    font-size: 11px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--ui-ink-soft);
}

.ui-row{
    display: grid;
    gap: 14px;
    padding: 20px 24px;
    border-radius: var(--ui-r-row);
    background: var(--ui-surface);
    transition: background-color .16s ease, opacity .16s ease;
}
.ui-row:hover{ background: var(--ui-surface-2); }
.ui-row--link{ cursor: pointer; }
.ui-row--muted{ opacity: .72; }

.ui-name{ display: grid; gap: 7px; align-content: start; }
.ui-name > a, .ui-name__title{
    font-size: 17px;
    font-weight: 700;
    letter-spacing: -.01em;
    line-height: 1.25;
}
.ui-name > a:hover{ color: var(--ui-accent); }
.ui-name__meta{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px 12px;
    font-size: 13px;
    color: var(--ui-ink-soft);
}

/* Riga con miniatura (foto o iniziali): l'immagine sta a fianco del nome,
   non sopra, così l'altezza della riga non cambia. */
.ui-name--media{ display: flex; align-items: center; gap: 12px; }
.ui-name__body{ display: grid; gap: 7px; min-width: 0; }
.ui-avatar{
    width: 44px; height: 44px;
    flex-shrink: 0;
    border-radius: 50%;
    object-fit: cover;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--ui-surface-2);
    color: var(--ui-ink-soft);
    font-family: var(--ui-mono);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .02em;
}
.ui-avatar--lg{ width: 72px; height: 72px; font-size: 20px; }
/* Miniatura di un oggetto, non di una persona: angoli morbidi, non tondi */
.ui-avatar--square{ width: 52px; height: 52px; border-radius: 14px; }

.ui-cell{ display: grid; gap: 3px; align-content: start; }
.ui-cell strong{
    font-size: 15px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
}
.ui-cell span{ font-size: 13px; color: var(--ui-ink-soft); }
.ui-cell--money strong{ font-family: var(--ui-mono); }

/* L'etichetta si scrive una volta sola: su schermo largo la dà l'intestazione,
   su schermo stretto ricompare qui senza duplicare markup. */
.ui-cell::before,
.ui-meter[data-label]::before{
    content: attr(data-label);
    font-family: var(--ui-mono);
    font-size: 10.5px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--ui-ink-soft);
}

.ui-actions{ display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }

/* Passo intermedio (tablet): le colonne vere non ci stanno ancora, ma impilare
   quattro celle rende la riga alta 380px. Il nome tiene la sua riga, le celle si
   affiancano quante ne entrano. */
@media (min-width: 640px) and (max-width: 1079.98px){
    .ui-row{ grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px 20px; }
    .ui-name, .ui-actions{ grid-column: 1 / -1; }
}

@media (min-width: 1080px){
    .ui-list__head, .ui-row{
        grid-template-columns: var(--ui-cols);
        align-items: center;
        gap: 20px;
    }
    .ui-list__head{ display: grid; }
    .ui-cell::before,
    .ui-meter[data-label]::before{ content: none; }
    .ui-actions{ justify-content: flex-end; }
}

/* =========================================================================
   8. STATI, PILLOLE, MISURE
   ========================================================================= */
.ui-status{
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 14px;
    font-weight: 700;
    color: var(--ui-ink);
}
.ui-status::before{
    content: "";
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--ui-mute-dim);
}
.ui-status--running::before,
.ui-status--open::before    { background: var(--ui-accent); }
.ui-status--closed::before  { background: var(--ui-warn); }
.ui-status--cancelled::before{ background: var(--ui-danger); }
.ui-status--finished::before,
.ui-status--draft::before   { background: rgba(216, 221, 232, .35); }

.ui-pill{
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: var(--ui-pill);
    background: var(--ui-mute-dim);
    color: var(--ui-ink-soft);
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}
.ui-pill--accent{ background: var(--ui-accent-dim); color: var(--ui-accent); }
.ui-pill--warn  { background: var(--ui-warn-dim);   color: var(--ui-warn); }
.ui-pill--danger{ background: var(--ui-danger-dim); color: var(--ui-danger); }

.ui-code{
    font-family: var(--ui-mono);
    font-size: 12px;
    color: var(--ui-ink-soft);
}

/* Mai una percentuale nuda: 18/40 dice quanto e su cosa */
.ui-meter{ display: grid; gap: 6px; align-content: start; }
.ui-meter__value{
    font-family: var(--ui-mono);
    font-size: 15px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.ui-meter__value small{ font-size: 13px; font-weight: 600; color: var(--ui-ink-soft); }
.ui-meter__track{
    height: 4px;
    border-radius: var(--ui-pill);
    background: var(--ui-mute-dim);
    overflow: hidden;
}
.ui-meter__fill{
    display: block;
    height: 100%;
    border-radius: inherit;
    background: var(--ui-accent);
}
.ui-meter__fill--warn{ background: var(--ui-warn); }
.ui-meter--empty .ui-meter__value{ font-weight: 500; color: var(--ui-ink-soft); }

/* Sezione: un titolo e il suo contenuto, senza pannello attorno.
   Serve nelle pagine di dettaglio lunghe, dove ogni blocco è già fatto di righe. */
.ui-section{ display: grid; gap: 12px; }
.ui-section__head{
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px 16px;
    flex-wrap: wrap;
}
.ui-section__head h2{ font-size: 19px; font-weight: 700; letter-spacing: -.015em; }
.ui-section__meta{ display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

/* Immagine di testata di una scheda */
.ui-cover{
    width: 100%;
    max-height: 260px;
    object-fit: cover;
    border-radius: var(--ui-r-box);
    display: block;
}

/* Modulo che si apre dentro una riga: la riga resta leggibile e i campi
   compaiono solo quando servono davvero. */
.ui-inline{ grid-column: 1 / -1; }
.ui-inline > summary{
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 34px;
    padding: 0 14px;
    border-radius: var(--ui-pill);
    background: var(--ui-surface-2);
    color: var(--ui-ink-soft);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    list-style: none;
    width: max-content;
}
.ui-inline > summary::-webkit-details-marker{ display: none; }
.ui-inline > summary:hover{ background: var(--ui-mute-dim); color: var(--ui-ink); }
.ui-inline[open] > summary{ background: var(--ui-accent-dim); color: var(--ui-accent); }
.ui-inline__body{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}
.ui-inline__body input,
.ui-inline__body select{ flex: 1 1 170px; min-height: 42px; }
.ui-inline__body form{ display: contents; }

/* =========================================================================
   9. RIQUADRI NUMERICI
   ========================================================================= */
.ui-facts{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 10px;
}
.ui-fact{
    display: grid;
    gap: 4px;
    align-content: start;
    padding: 18px 20px;
    border-radius: var(--ui-r-row);
    background: var(--ui-surface);
}
.ui-fact > span{
    font-family: var(--ui-mono);
    font-size: 10.5px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--ui-ink-soft);
}
.ui-fact > strong{
    font-size: 17px;
    font-weight: 700;
    letter-spacing: -.01em;
    font-variant-numeric: tabular-nums;
}
.ui-fact > small{ font-size: 12.5px; color: var(--ui-ink-soft); }
.ui-fact--lead > strong{ font-size: 26px; }

/* Schede di contenuto: si usano solo dove l'immagine o il testo lungo SONO il
   contenuto (modelli di mail, annunci con foto). Per i dati tabellari resta la lista. */
.ui-cards{
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(280px, 100%), 1fr));
    gap: 12px;
}
.ui-card{
    display: grid;
    grid-template-rows: auto 1fr auto;
    gap: 12px;
    padding: 20px;
    border-radius: var(--ui-r-box);
    background: var(--ui-surface);
    overflow: hidden;
}
.ui-card__head{ display: grid; gap: 6px; }
.ui-card__head h3{ font-size: 17px; font-weight: 700; letter-spacing: -.01em; }
.ui-card__body{ font-size: 13.5px; color: var(--ui-ink-soft); line-height: 1.55; max-height: 200px; overflow: hidden; }
.ui-card__body img{ max-width: 100%; border-radius: 14px; margin: 8px 0; }
.ui-card__foot{ display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.ui-card--muted{ opacity: .72; }

/* Scelta a schede (radio o checkbox): la scheda intera è cliccabile e quella
   scelta si accende, senza un bordo permanente attorno a tutte. */
.ui-pick{ display: block; cursor: pointer; }
.ui-pick input{ position: absolute; opacity: 0; width: 1px; height: 1px; }
.ui-pick__box{
    display: grid;
    gap: 8px;
    padding: 18px 20px;
    border-radius: var(--ui-r-box);
    background: var(--ui-surface);
    transition: background-color .16s ease, box-shadow .16s ease;
}
.ui-pick:hover .ui-pick__box{ background: var(--ui-surface-2); }
.ui-pick input:checked + .ui-pick__box{
    background: var(--ui-accent-dim);
    /* box-shadow e non border: il bordo sposterebbe il contenuto di 2px */
    box-shadow: inset 0 0 0 2px var(--ui-accent);
}
.ui-pick input:focus-visible + .ui-pick__box{ outline: 2px solid var(--c2); outline-offset: 2px; }
.ui-pick__box h3{ font-size: 16px; font-weight: 700; }
.ui-pick__box small{ font-size: 12.5px; color: var(--ui-ink-soft); }

/* =========================================================================
   10. VUOTO
   ========================================================================= */
.ui-empty{
    display: grid;
    justify-items: center;
    gap: 10px;
    padding: clamp(40px, 7vw, 70px) 28px;
    border-radius: var(--ui-r-box);
    background: var(--ui-surface);
    text-align: center;
}
.ui-empty__icon{
    width: 62px; height: 62px;
    border-radius: 50%;
    background: var(--ui-accent-dim);
    color: var(--ui-accent);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.ui-empty h2{ font-size: 19px; font-weight: 700; }
.ui-empty p{ max-width: 46ch; font-size: 14px; color: var(--ui-ink-soft); }
.ui-empty .ui-btn{ margin-top: 6px; }

/* =========================================================================
   11. MODULI
   ========================================================================= */
/* Stesso impianto a due colonne per i moduli (.ui-form) e per le pagine di
   dettaglio (.ui-split): contenuto a sinistra, riepilogo e azioni a destra. */
.ui-form, .ui-split{ display: grid; gap: 18px; align-items: start; }
@media (min-width: 1000px){
    .ui-form, .ui-split{ grid-template-columns: minmax(0, 1fr) 340px; }
    .ui-form__side, .ui-split__side{ position: sticky; top: 16px; display: grid; gap: 18px; }
}
.ui-form__main, .ui-split__main{ display: grid; gap: 18px; min-width: 0; }
.ui-split__side, .ui-form__side{ display: grid; gap: 18px; min-width: 0; }

.ui-panel{
    display: grid;
    gap: 16px;
    align-content: start;
    padding: clamp(20px, 2.6vw, 26px);
    border-radius: var(--ui-r-box);
    background: var(--ui-surface);
}
.ui-panel__head{
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.ui-panel__head h2{
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.ui-panel__note{ font-family: var(--ui-mono); font-size: 11.5px; color: var(--ui-ink-soft); }

.ui-fields{ display: grid; gap: 16px; }
@media (min-width: 560px){
    .ui-fields--2{ grid-template-columns: 1fr 1fr; }
}
.ui-field{ display: grid; gap: 7px; align-content: start; }
.ui-field > label{ font-size: 13.5px; font-weight: 600; }
.ui-field > label b{ color: var(--ui-accent); font-weight: 600; }
.ui-hint{ font-size: 12.5px; color: var(--ui-ink-soft); line-height: 1.45; }
.ui-err{
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: var(--ui-danger);
}

.ui-page input[type="text"], .ui-page input[type="email"], .ui-page input[type="tel"],
.ui-page input[type="number"], .ui-page input[type="password"], .ui-page input[type="date"],
.ui-page input[type="time"], .ui-page input[type="datetime-local"], .ui-page input[type="url"],
.ui-page select, .ui-page textarea{
    width: 100%;
    /* senza min-width:0 un campo sporge dalla griglia su telefono */
    min-width: 0;
    min-height: 48px;
    padding: 12px 16px;
    border: 1px solid transparent;
    border-radius: var(--ui-r-row);
    background: var(--ui-surface-2);
    color: var(--c3);
    font-size: 14.5px;
    font-family: inherit;
    text-align: left;
    transition: background-color .16s ease, border-color .16s ease;
}
.ui-page input::placeholder, .ui-page textarea::placeholder{ color: rgba(216, 221, 232, .38); }
.ui-page input:focus, .ui-page select:focus, .ui-page textarea:focus{
    outline: none;
    border-color: var(--ui-accent);
    /* al focus il campo si schiarisce meno, non di più: il bordo basta a dire dove sei */
    background: var(--ui-surface);
}
.ui-page select{
    appearance: none;
    padding-right: 44px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23d8dde8' viewBox='0 0 16 16'%3E%3Cpath d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
}
.ui-page select option{ background: #120c3a; color: var(--c3); }
.ui-page textarea{ min-height: 120px; resize: vertical; line-height: 1.55; }

/* Campo file: il widget nativo non si stila, ma la sua cornice sì.
   Il bottone interno lo ridipingiamo con ::file-selector-button. */
.ui-page input[type="file"]{
    width: 100%;
    min-width: 0;
    padding: 11px 14px;
    border: 1px solid transparent;
    border-radius: var(--ui-r-row);
    background: var(--ui-surface-2);
    color: var(--ui-ink-soft);
    font-size: 13.5px;
    font-family: inherit;
}
.ui-page input[type="file"]::file-selector-button{
    margin-right: 12px;
    padding: 8px 14px;
    border: 0;
    border-radius: var(--ui-pill);
    background: var(--ui-mute-dim);
    color: var(--ui-ink);
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}
.ui-page input[type="file"]::file-selector-button:hover{ background: var(--ui-surface-2); }

/* Casella di spunta semplice, per le scelte secondarie dentro un campo */
.ui-check{
    display: inline-flex;
    align-items: center;
    gap: 9px;
    font-size: 13px;
    color: var(--ui-ink-soft);
    cursor: pointer;
}
.ui-check input{
    width: 18px; height: 18px;
    min-height: 0;
    accent-color: var(--c2);
    flex-shrink: 0;
}

/* Anteprima quadrata di un file già caricato (copertina, foto profilo) */
.ui-media{ display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.ui-media__thumb{
    width: 120px; height: 74px;
    flex-shrink: 0;
    border-radius: 14px;
    object-fit: cover;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--ui-surface-2);
    color: var(--ui-ink-soft);
    font-size: 12px;
}
.ui-media__thumb--round{ width: 64px; height: 64px; border-radius: 50%; }
.ui-media__body{ flex: 1 1 200px; display: grid; gap: 8px; }

/* Interruttore: l'intera riga è cliccabile */
.ui-switch{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 18px;
    border-radius: var(--ui-r-row);
    background: var(--ui-surface-2);
    cursor: pointer;
}
.ui-switch__text{ display: grid; gap: 3px; }
.ui-switch__text strong{ font-size: 14px; font-weight: 600; }
.ui-switch__text span{ font-size: 12.5px; color: var(--ui-ink-soft); }
.ui-switch input{
    position: absolute;
    opacity: 0;
    width: 1px; height: 1px;
}
.ui-switch__rail{
    position: relative;
    flex-shrink: 0;
    width: 46px; height: 26px;
    border-radius: var(--ui-pill);
    background: var(--ui-mute-dim);
    transition: background-color .16s ease;
}
.ui-switch__rail::after{
    content: "";
    position: absolute;
    top: 3px; left: 3px;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: var(--ui-ink-soft);
    transition: transform .16s ease, background-color .16s ease;
}
.ui-switch input:checked + .ui-switch__rail{ background: var(--ui-accent-dim); }
.ui-switch input:checked + .ui-switch__rail::after{ background: var(--ui-accent); transform: translateX(20px); }
.ui-switch input:focus-visible + .ui-switch__rail{ outline: 2px solid var(--c2); outline-offset: 2px; }

/* Selezione multipla a pastiglie: al posto di un <select multiple> illeggibile.
   Il campo di ricerca filtra le pastiglie, il contatore dice quante ne hai scelte. */
.ui-chips{ display: grid; gap: 12px; }
.ui-chips__head{ display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.ui-chips__head .ui-search{ flex: 1 1 200px; }
.ui-chips__area{
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    max-height: 280px;
    overflow: auto;
    /* 2px di respiro: senza, l'anello di focus della prima pastiglia viene tagliato */
    padding: 2px;
}
.ui-chips__item{ margin: 0; cursor: pointer; }
.ui-chips__item input{ position: absolute; opacity: 0; width: 1px; height: 1px; }
.ui-chips__item span{
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 38px;
    padding: 0 16px;
    border-radius: var(--ui-pill);
    background: var(--ui-surface-2);
    color: var(--ui-ink);
    font-size: 13.5px;
    font-weight: 600;
    transition: background-color .16s ease, color .16s ease;
}
.ui-chips__item span small{ font-family: var(--ui-mono); font-size: 11.5px; opacity: .7; }
.ui-chips__item:hover span{ background: var(--ui-mute-dim); }
.ui-chips__item input:checked + span{ background: var(--ui-accent); color: var(--c1); font-weight: 700; }
.ui-chips__item input:checked + span small{ opacity: .8; }
.ui-chips__item input:focus-visible + span{ outline: 2px solid var(--c2); outline-offset: 2px; }
.ui-chips__empty{ font-size: 13px; color: var(--ui-ink-soft); }

/* Barra di salvataggio: statica, chiude il flusso della pagina senza sfondo proprio */
.ui-savebar{
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 18px 0 0;
    border-top: 1px solid var(--ui-rule);
    background: transparent;
}
.ui-savebar__note{ font-size: 13px; color: var(--ui-ink-soft); }
.ui-savebar__actions{ display: flex; gap: 10px; flex-wrap: wrap; }
@media (max-width: 500px){
    .ui-savebar__actions{ width: 100%; }
    .ui-savebar__actions .ui-btn{ flex: 1 1 auto; }
}

/* =========================================================================
   12. TABELLA DI DATI GREZZI
   ========================================================================= */
.ui-data{
    border-radius: var(--ui-r-box);
    background: var(--ui-surface);
    overflow: hidden;
}
.ui-data > summary{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 22px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    list-style: none;
}
.ui-data > summary::-webkit-details-marker{ display: none; }
.ui-data > summary svg{ transition: transform .16s ease; color: var(--ui-ink-soft); }
.ui-data[open] > summary svg{ transform: rotate(180deg); }
.ui-data__scroll{ max-height: 420px; overflow: auto; }
.ui-data table{ width: 100%; border-collapse: collapse; font-variant-numeric: tabular-nums; }
.ui-data th, .ui-data td{
    padding: 10px 16px;
    text-align: right;
    font-size: 13px;
    white-space: nowrap;
    /* separatori come ombra interna: un border sposterebbe le celle sticky */
    box-shadow: inset 0 1px 0 var(--ui-rule);
}
.ui-data th:first-child, .ui-data td:first-child{ text-align: left; }
.ui-data thead th{
    position: sticky;
    top: 0;
    z-index: 1;
    /* tinta piena e non trasparente: sotto ci scorrono le righe */
    background: #120c3a;
    font-family: var(--ui-mono);
    font-size: 10.5px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--ui-ink-soft);
}

/* =========================================================================
   13. FINESTRE (override di Bootstrap)
   ========================================================================= */
.ui-modal .modal-content{
    border: 0;
    border-radius: var(--ui-r-box);
    background: rgba(9, 3, 51, .96);
    color: var(--c3);
    overflow: hidden;
}
.ui-modal .modal-body{ max-height: min(86vh, 920px); overflow: auto; padding: clamp(20px, 3vw, 28px); }
.ui-modal .modal-footer{
    position: sticky;
    bottom: 0;
    border: 0;
    /* tinta piena: le azioni non devono mai sparire dietro il contenuto che scorre */
    background: #0d0640;
    gap: 10px;
}
.ui-modal .modal-header{ border: 0; padding: 22px 26px 0; }

/* =========================================================================
   14. MOVIMENTO RIDOTTO
   ========================================================================= */
@media (prefers-reduced-motion: reduce){
    .ui-page *, .ui-nav *{
        transition: none !important;
        animation: none !important;
        transform: none !important;
    }
}
</style>
@endonce
