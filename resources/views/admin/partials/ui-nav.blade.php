{{--
    Navigazione del back office: barra a pillola ancorata in basso su desktop,
    pillola "dove ti trovi" + pannello a tendina su telefono.
    Niente sidebar: il pollice sta in basso e lo schermo largo non deve perdere
    una colonna intera per sette voci.
--}}
@php
    /** Le sezioni sono tutte allo stesso rango: nessuna nascosta dietro un "altro". */
    $uiNavItems = [
        ['route' => 'admin.dashboard',         'match' => 'admin.dashboard',      'label' => 'Calendario',   'icon' => 'calendar2-week'],
        ['route' => 'admin.reservations.index','match' => 'admin.reservations.*', 'label' => 'Prenotazioni', 'icon' => 'card-checklist'],
        ['route' => 'admin.tournaments.index', 'match' => 'admin.tournaments.*',  'label' => 'Tornei',       'icon' => 'trophy'],
        ['route' => 'admin.players.index',     'match' => 'admin.players.*',      'label' => 'Giocatori',    'icon' => 'people-fill'],
        ['route' => 'admin.fixed-slots.index', 'match' => 'admin.fixed-slots.*',  'label' => 'Campi fissi',  'icon' => 'arrow-repeat'],
        ['route' => 'admin.listings.index',    'match' => 'admin.listings.*',     'label' => 'Bacheca',      'icon' => 'shop'],
        ['route' => 'admin.settings',          'match' => 'admin.settings*',      'label' => 'Impostazioni', 'icon' => 'gear-wide-connected'],
        ['route' => 'admin.mailer.index',      'match' => 'admin.mailer.*',       'label' => 'Comunicazioni','icon' => 'envelope-at'],
        ['route' => 'admin.profile.edit',      'match' => 'admin.profile.*',      'label' => auth()->user()?->name ?? 'Profilo', 'icon' => 'person-badge'],
    ];

    /** Voce corrente: serve sia per aria-current sia per il testo della pillola mobile. */
    $uiNavCurrent = null;
    foreach ($uiNavItems as $item) {
        if (request()->routeIs($item['match'])) {
            $uiNavCurrent = $item;
            break;
        }
    }
    $uiNavCurrent ??= $uiNavItems[0];
@endphp

<nav class="ui-nav" aria-label="Sezioni del gestionale">

    {{-- Desktop: la barra intera. Il guscio non intercetta i click, la pillola sì. --}}
    <div class="ui-nav__bar">
        @foreach ($uiNavItems as $item)
            <a class="ui-nav__link {{ $item === $uiNavCurrent ? 'is-current' : '' }}"
               href="{{ route($item['route']) }}"
               @if ($item === $uiNavCurrent) aria-current="page" @endif>
                @include('admin.partials.ui-icon', ['name' => $item['icon'], 'size' => 20])
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Telefono: una pillola sola che dice dove ti trovi e apre l'elenco completo. --}}
    <button type="button" class="ui-nav__here" id="uiNavTrigger"
            aria-haspopup="dialog" aria-expanded="false" aria-controls="uiNavSheet">
        @include('admin.partials.ui-icon', ['name' => $uiNavCurrent['icon'], 'size' => 20])
        <span>{{ $uiNavCurrent['label'] }}</span>
        @include('admin.partials.ui-icon', ['name' => 'chevron-down', 'size' => 14])
    </button>
</nav>

<div class="ui-sheet" id="uiNavSheet" hidden>
    <div class="ui-sheet__scrim" data-ui-sheet-close></div>
    <div class="ui-sheet__panel" role="dialog" aria-modal="true" aria-label="Sezioni del gestionale">
        <div class="ui-sheet__grab" aria-hidden="true"></div>
        <div class="ui-sheet__list">
            @foreach ($uiNavItems as $item)
                <a class="ui-sheet__link {{ $item === $uiNavCurrent ? 'is-current' : '' }}"
                   href="{{ route($item['route']) }}"
                   @if ($item === $uiNavCurrent) aria-current="page" @endif>
                    @include('admin.partials.ui-icon', ['name' => $item['icon'], 'size' => 20])
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@once
<style>
/* ---------- Guscio ---------- */
.ui-nav{
    position: fixed;
    inset: auto 0 0 0;
    z-index: 200;
    display: flex;
    justify-content: center;
    padding: 0 16px calc(16px + env(safe-area-inset-bottom));
    /* il guscio copre tutta la larghezza ma non deve rubare i click alla pagina */
    pointer-events: none;
}

/* ---------- Desktop: barra a pillola ---------- */
.ui-nav__bar{
    pointer-events: auto;
    display: flex;
    align-items: center;
    gap: 4px;
    max-width: 100%;
    padding: 6px;
    border-radius: 999px;
    background: linear-gradient(rgba(216, 221, 232, .07), rgba(216, 221, 232, .07)), rgba(9, 3, 51, .9);
    backdrop-filter: blur(10px);
    box-shadow: 0 5px 30px rgba(0, 0, 0, .38);
}
.ui-nav__link{
    display: inline-flex;
    align-items: center;
    gap: 9px;
    height: 52px;
    padding: 0 16px;
    border-radius: 999px;
    color: rgba(216, 221, 232, .72);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    transition: background-color .16s ease, color .16s ease;
}
.ui-nav__link:hover{ background: rgba(216, 221, 232, .095); color: #d8dde8; }
/* "Sei qui" è una pillola piena di accento, non una lastra bianca */
.ui-nav__link.is-current{ background: #0eb792; color: #090333; font-weight: 700; }

/* Sotto i 1240px restano le icone: l'etichetta la tiene solo la voce corrente */
@media (max-width: 1240px){
    .ui-nav__link{ padding: 0 13px; }
    .ui-nav__link span{ display: none; }
    .ui-nav__link.is-current{ padding: 0 18px; }
    .ui-nav__link.is-current span{ display: inline; }
}

/* ---------- Telefono: pillola singola ---------- */
.ui-nav__here{
    display: none;
    pointer-events: auto;
    align-items: center;
    gap: 10px;
    height: 52px;
    padding: 0 22px;
    border: 0;
    border-radius: 999px;
    background: linear-gradient(rgba(216, 221, 232, .07), rgba(216, 221, 232, .07)), rgba(9, 3, 51, .92);
    backdrop-filter: blur(10px);
    box-shadow: 0 5px 30px rgba(0, 0, 0, .38);
    color: #d8dde8;
    font-family: inherit;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: transform .16s ease;
}
.ui-nav__here:active{ transform: scale(.97); }
.ui-nav__here > svg:first-child{ color: #0eb792; }
.ui-nav__here[aria-expanded="true"] > svg:last-child{ transform: rotate(180deg); }
.ui-nav__here > svg:last-child{ opacity: .6; transition: transform .16s ease; }

@media (max-width: 820px){
    .ui-nav__bar{ display: none; }
    .ui-nav__here{ display: inline-flex; }
}
@media (max-width: 500px){
    .ui-nav{ padding-bottom: calc(12px + env(safe-area-inset-bottom)); }
    .ui-nav__here{ height: 48px; }
}

/* ---------- Pannello a tendina ---------- */
.ui-sheet{ position: fixed; inset: 0; z-index: 300; }
.ui-sheet[hidden]{ display: none; }
.ui-sheet__scrim{
    position: absolute;
    inset: 0;
    background: rgba(9, 3, 51, .62);
    backdrop-filter: blur(2px);
    opacity: 0;
    transition: opacity .16s ease;
}
.ui-sheet.is-open .ui-sheet__scrim{ opacity: 1; }
.ui-sheet__panel{
    position: absolute;
    inset: auto 0 0 0;
    padding: 10px 14px calc(18px + env(safe-area-inset-bottom));
    border-radius: 28px 28px 0 0;
    /* tinta piena: sotto scorre la pagina e il testo deve restare leggibile */
    background: #0d0640;
    box-shadow: 0 -12px 40px rgba(0, 0, 0, .45);
    transform: translateY(100%);
    transition: transform .16s ease;
}
.ui-sheet.is-open .ui-sheet__panel{ transform: translateY(0); }
.ui-sheet__grab{
    width: 44px; height: 4px;
    margin: 4px auto 12px;
    border-radius: 999px;
    background: rgba(216, 221, 232, .25);
}
.ui-sheet__list{ display: grid; gap: 6px; max-height: 70dvh; overflow: auto; }
.ui-sheet__link{
    display: flex;
    align-items: center;
    gap: 14px;
    min-height: 52px;
    padding: 0 18px;
    border-radius: 20px;
    color: rgba(216, 221, 232, .78);
    text-decoration: none;
    font-size: 15.5px;
    font-weight: 600;
}
.ui-sheet__link:hover{ background: rgba(216, 221, 232, .095); color: #d8dde8; }
.ui-sheet__link.is-current{ background: #0eb792; color: #090333; font-weight: 700; }

body.ui-sheet-open{ overflow: hidden; overscroll-behavior: none; }

@media (prefers-reduced-motion: reduce){
    .ui-sheet__panel, .ui-sheet__scrim{ transition: none; }
}
</style>

<script>
(function () {
    const trigger = document.getElementById('uiNavTrigger');
    const sheet   = document.getElementById('uiNavSheet');
    if (!trigger || !sheet) return;

    const panel = sheet.querySelector('.ui-sheet__panel');
    let startY = null;

    function open() {
        sheet.hidden = false;
        // un frame di ritardo: senza, il browser salta la transizione di apertura
        requestAnimationFrame(() => sheet.classList.add('is-open'));
        trigger.setAttribute('aria-expanded', 'true');
        document.body.classList.add('ui-sheet-open');
        (sheet.querySelector('.ui-sheet__link.is-current') || sheet.querySelector('.ui-sheet__link'))?.focus();
    }

    function close() {
        sheet.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('ui-sheet-open');
        panel.style.transform = '';
        setTimeout(() => { sheet.hidden = true; }, 160);
        trigger.focus();
    }

    trigger.addEventListener('click', () => sheet.hidden ? open() : close());
    sheet.querySelectorAll('[data-ui-sheet-close]').forEach((el) => el.addEventListener('click', close));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !sheet.hidden) close();
    });

    // Trascinamento verso il basso per chiudere: sul telefono è il gesto atteso.
    panel.addEventListener('touchstart', (e) => { startY = e.touches[0].clientY; }, { passive: true });
    panel.addEventListener('touchmove', (e) => {
        if (startY === null) return;
        const delta = e.touches[0].clientY - startY;
        if (delta > 0) panel.style.transform = 'translateY(' + delta + 'px)';
    }, { passive: true });
    panel.addEventListener('touchend', (e) => {
        const delta = e.changedTouches[0].clientY - (startY ?? 0);
        startY = null;
        if (delta > 90) close(); else panel.style.transform = '';
    });
})();
</script>
@endonce
