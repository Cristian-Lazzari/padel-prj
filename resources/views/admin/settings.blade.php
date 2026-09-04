@extends('layouts.ui')

@section('title', 'Impostazioni - F+')

@section('styles')
<style>
/* La settimana di un campo: un giorno per riga, orari in linea. */
.ui-page .ui-week{ display: grid; gap: 8px; }
.ui-page .ui-week__row{
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px 10px;
}
.ui-page .ui-week__day{
    flex: 0 0 96px;
    font-size: 13px;
    font-weight: 600;
    color: var(--ui-ink-soft);
}
.ui-page .ui-week__closed{ flex: 0 0 auto; }
.ui-page .ui-week__row input[type="time"]{ width: auto; min-width: 118px; }
.ui-page .ui-week__row input[type="time"]:disabled{ opacity: .35; }
.ui-page .ui-week__sep{ color: var(--ui-ink-soft); }
.ui-page .ui-week__tools{ margin-top: 10px; }
@media (max-width: 640px){
    .ui-page .ui-week__day{ flex-basis: 100%; }
}
</style>
@endsection

@section('contents')

@php
    $property_adv     = json_decode($settings['advanced']['property'], true);
    // I campi arrivano dalle tabelle `fields`/`field_hours`, non più dal JSON.
    $field_set        = \App\Models\Setting::fieldSet();
    $trainer_set      = $property_adv['trainer_set'] ?? [];
    $this_trainer     = $trainer_set[auth()->user()->id] ?? [];
    $is_admin         = auth()->user()->role === 'admin';
    $this_trainer_field     = $this_trainer['field'] ?? 0;
    $this_trainer_field_set = $field_set[$this_trainer_field] ?? [];
    $trainer_span  = \App\Services\FieldSchedule::span($this_trainer_field_set) ?? ['h_start' => '08:00', 'h_end' => '23:00'];
    $trainer_slots = (int) floor(
        \App\Services\FieldSchedule::spanMinutes($this_trainer_field_set)
        / max(1, (int) ($this_trainer_field_set['m_during_client'] ?? 90))
    );

    $property_contatti = json_decode($settings['Contatti']['property'], true);
    $ferie   = json_decode($settings['Periodo di Ferie']['property'], true);
    $cena    = isset($settings['Impostazioni cena'])
        ? json_decode($settings['Impostazioni cena']['property'], true)
        : ['user_mail' => ''];

    $week = [
        'Lunedì' => 1, 'Martedì' => 2, 'Mercoledì' => 3, 'Giovedì' => 4,
        'Venerdì' => 5, 'Sabato' => 6, 'Domenica' => 7,
    ];
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Impostazioni</b>
</nav>

@if (session('message'))
    <div class="ui-flash" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'check-circle-fill', 'size' => 20])
        <span>{{ session('message') }}</span>
        <button type="button" class="ui-flash__close" data-ui-dismiss aria-label="Chiudi avviso">
            @include('admin.partials.ui-icon', ['name' => 'x-lg', 'size' => 14])
        </button>
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
        <h1>Impostazioni</h1>
        <div class="ui-head__count">
            <span>Campi configurati <b>{{ count($field_set) }}</b></span>
            <span>Istruttori <b>{{ count($trainers) }}</b></span>
        </div>
    </div>
</header>

<form class="ui-form" action="{{ route('admin.settings.updateAll') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="ui-form__main">

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Prenotazioni online</h2></div>

            <div class="ui-chips__area" role="radiogroup" aria-label="Stato del servizio di prenotazione">
                <label class="ui-chips__item">
                    <input type="radio" name="status_service" value="2" @checked($settings['Servizio di Prenotazione Online']['status'] == 2)>
                    <span>Attive</span>
                </label>
                <label class="ui-chips__item">
                    <input type="radio" name="status_service" value="0" @checked($settings['Servizio di Prenotazione Online']['status'] == 0)>
                    <span>Sospese</span>
                </label>
            </div>
            <p class="ui-hint">Da sospese, il sito non accetta più prenotazioni dai clienti.</p>

            <div class="ui-fields ui-fields--2">
                <div class="ui-field">
                    <label for="max_delay_default">Ore minime per annullare</label>
                    <input type="number" name="max_delay_default" id="max_delay_default" min="0"
                           value="{{ $property_adv['max_delay_default'] }}">
                    <p class="ui-hint">Sotto questa soglia il cliente non può più disdire da solo.</p>
                </div>
                <div class="ui-field">
                    <label for="delay_trainer">Ore per liberare il campo</label>
                    <input type="number" name="delay_trainer" id="delay_trainer" min="0"
                           value="{{ $property_adv['delay_trainer'] ?? '' }}">
                    <p class="ui-hint">Dopo quante ore uno slot non confermato torna prenotabile.</p>
                </div>
            </div>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Ristorante affiliato</h2></div>

            <div class="ui-chips__area" role="radiogroup" aria-label="Servizio cena">
                <label class="ui-chips__item">
                    <input type="radio" name="dinner_status" value="2" @checked(isset($settings['Impostazioni cena']) && $settings['Impostazioni cena']['status'] == 2)>
                    <span>Attivo</span>
                </label>
                <label class="ui-chips__item">
                    <input type="radio" name="dinner_status" value="0" @checked(! isset($settings['Impostazioni cena']) || $settings['Impostazioni cena']['status'] == 0)>
                    <span>Non attivo</span>
                </label>
            </div>
            <p class="ui-hint">Con il servizio attivo, chi prenota il campo può aggiungere la cena.</p>

            <div class="ui-field">
                <label for="user_mail">Email del ristoratore</label>
                <input type="email" name="user_mail" id="user_mail" value="{{ $cena['user_mail'] ?? '' }}">
                <p class="ui-hint">Riceve qui le prenotazioni della cena.</p>
            </div>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Ferie</h2></div>

            <div class="ui-chips__area" role="radiogroup" aria-label="Periodo di ferie">
                <label class="ui-chips__item">
                    <input type="radio" name="ferie_status" value="0" @checked($settings['Periodo di Ferie']['status'] == 0)>
                    <span>Aperti</span>
                </label>
                <label class="ui-chips__item">
                    <input type="radio" name="ferie_status" value="1" @checked($settings['Periodo di Ferie']['status'] == 1)>
                    <span>In ferie</span>
                </label>
            </div>

            <div class="ui-fields ui-fields--2">
                <div class="ui-field">
                    <label for="from">Dal</label>
                    <input type="date" name="from" id="from" value="{{ $ferie['from'] ?: '' }}">
                </div>
                <div class="ui-field">
                    <label for="to">Al</label>
                    <input type="date" name="to" id="to" value="{{ $ferie['to'] ?: '' }}">
                </div>
            </div>
        </section>

        {{-- ============ Campi ============ --}}
        <section class="ui-section">
            <div class="ui-section__head">
                <h2>Campi</h2>
                <div class="ui-section__meta">
                    <button type="button" class="ui-btn" id="addFieldBtn">
                        @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                        <span>Nuovo campo</span>
                    </button>
                </div>
            </div>

            @foreach ($field_set as $k => $f)
                <section class="ui-panel">
                    <div class="ui-panel__head">
                        <h2>{{ $k }}</h2>
                        <span class="ui-panel__note">{{ $f['type'] }}</span>
                    </div>

                    <input type="hidden" name="field_set[{{ $k }}][name_field]" value="{{ $k }}">
                    <input type="hidden" name="field_set[{{ $k }}][type]" value="{{ $f['type'] }}">

                    <div class="ui-fields ui-fields--2">
                        <div class="ui-field">
                            <label for="m_during_{{ $loop->index }}">Durata minima (min)</label>
                            <input type="number" id="m_during_{{ $loop->index }}" name="field_set[{{ $k }}][m_during]" value="{{ $f['m_during'] }}" min="5" max="240">
                            <p class="ui-hint">
                                È il passo della griglia: da qui partono sia le fasce del calendario
                                sia gli orari che il cliente può scegliere.
                            </p>
                        </div>
                        <div class="ui-field">
                            <label for="m_during_client_{{ $loop->index }}">Durata fascia (min)</label>
                            <input type="number" id="m_during_client_{{ $loop->index }}" name="field_set[{{ $k }}][m_during_client]" value="{{ $f['m_during_client'] }}" min="5" max="600">
                            <p class="ui-hint">
                                Segna le fasce piene nel calendario. Il cliente non prenota più a
                                fasce: parte da qualsiasi orario libero della griglia e gioca
                                sempre un'ora e mezza.
                            </p>
                        </div>
                    </div>

                    <div class="ui-field">
                        <label>Orari di apertura</label>
                        <div class="ui-week" data-ui-week>
                            @foreach (\App\Models\FieldHour::WEEKDAYS as $wd => $etichetta)
                                @php
                                    // Passa dal servizio, così il modulo si compila
                                    // anche se i campi arrivano dal vecchio JSON.
                                    $ore = \App\Services\FieldSchedule::hours($f, $wd);
                                    $chiuso = $ore === null;
                                @endphp
                                <div class="ui-week__row" data-ui-day>
                                    <span class="ui-week__day">{{ $etichetta }}</span>
                                    <label class="ui-chips__item ui-week__closed">
                                        <input type="checkbox" data-ui-closed
                                               name="field_set[{{ $k }}][hours][{{ $wd }}][closed]"
                                               value="1" @checked($chiuso)>
                                        <span>Chiuso</span>
                                    </label>
                                    <input type="time" aria-label="{{ $etichetta }}, apertura di {{ $k }}"
                                           name="field_set[{{ $k }}][hours][{{ $wd }}][h_start]"
                                           value="{{ $ore['h_start'] ?? '08:00' }}" @disabled($chiuso)>
                                    <span class="ui-week__sep" aria-hidden="true">→</span>
                                    <input type="time" aria-label="{{ $etichetta }}, chiusura di {{ $k }}"
                                           name="field_set[{{ $k }}][hours][{{ $wd }}][h_end]"
                                           value="{{ $ore['h_end'] ?? '23:00' }}" @disabled($chiuso)>
                                </div>
                            @endforeach
                        </div>
                        <div class="ui-week__tools">
                            <button type="button" class="ui-action" data-ui-week-copy>Applica il lunedì a tutta la settimana</button>
                        </div>
                        <p class="ui-hint">Un giorno chiuso sparisce dal calendario e dalle disponibilità online.</p>
                        @foreach (\App\Models\FieldHour::WEEKDAYS as $wd => $etichetta)
                            @error("field_set.$k.hours.$wd.h_start") <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                            @error("field_set.$k.hours.$wd.h_end") <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                        @endforeach
                    </div>
                </section>
            @endforeach

            {{-- Qui il JS appende i campi nuovi, con lo stesso impianto dei pannelli sopra --}}
            <div id="container" style="display:grid; gap:18px;"></div>
        </section>

        @if (auth()->user()->role == 'trainer')
            <section class="ui-panel">
                <div class="ui-panel__head"><h2>I tuoi orari da istruttore</h2></div>

                <div class="ui-field">
                    <label>Campo su cui lavori</label>
                    <div class="ui-chips__area" role="radiogroup" aria-label="Campo dell'istruttore">
                        @foreach ($field_set as $k => $f)
                            @php
                                // Gli orari del campo cambiano da un giorno all'altro:
                                // le fasce dell'istruttore coprono l'arco più largo.
                                $arco = \App\Services\FieldSchedule::span($f);
                                $passi = (int) floor(\App\Services\FieldSchedule::spanMinutes($f) / max(1, (int) $f['m_during_client']));
                            @endphp
                            <label class="ui-chips__item">
                                <input type="radio" name="set_trainer[field]" value="{{ $k }}"
                                       @checked($this_trainer !== [] && $this_trainer['field'] == $k)
                                       data-h_start="{{ $arco['h_start'] ?? '08:00' }}"
                                       data-n_slot="{{ $passi }}"
                                       data-m_during_client="{{ $f['m_during_client'] }}">
                                <span>{{ $k }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="ui-fields ui-fields--2">
                    <div class="ui-field">
                        <label for="h_start_trainer">Inizio</label>
                        <select id="h_start_trainer" name="set_trainer[h_start]">
                            @if ($this_trainer !== [])
                                @php $hour_option_1 = Carbon\Carbon::createFromFormat('H:i', $trainer_span['h_start']); @endphp
                                @for ($i = 0; $i < $trainer_slots; $i++)
                                    <option value="{{ $hour_option_1->copy()->format('H:i') }}" @selected($this_trainer['h_start'] == $hour_option_1->copy()->format('H:i'))>{{ $hour_option_1->copy()->format('H:i') }}</option>
                                    @php $hour_option_1->addMinutes($this_trainer_field_set['m_during_client']); @endphp
                                @endfor
                            @endif
                        </select>
                    </div>
                    <div class="ui-field">
                        <label for="h_end_trainer">Fine</label>
                        <select id="h_end_trainer" name="set_trainer[h_end]">
                            @if ($this_trainer !== [])
                                @php $hour_option_2 = Carbon\Carbon::createFromFormat('H:i', $trainer_span['h_start'])->addMinutes($this_trainer_field_set['m_during_client']); @endphp
                                @for ($i = 0; $i < ($trainer_slots - 1); $i++)
                                    <option value="{{ $hour_option_2->copy()->format('H:i') }}" @selected($this_trainer['h_end'] == $hour_option_2->copy()->format('H:i'))>{{ $hour_option_2->copy()->format('H:i') }}</option>
                                    @php $hour_option_2->addMinutes($this_trainer_field_set['m_during_client']); @endphp
                                @endfor
                            @endif
                        </select>
                        @error('set_trainer.h_end') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="ui-field">
                    <label>Giorni in cui lavori</label>
                    <div class="ui-chips__area">
                        @foreach ($week as $kw => $v)
                            <label class="ui-chips__item">
                                <input type="checkbox" name="set_trainer[day_w][]" value="{{ $v }}"
                                       @checked($this_trainer !== [] && in_array($v, $this_trainer['day_w']))>
                                <span>{{ $kw }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>

    <aside class="ui-form__side">
        <div class="ui-savebar ui-savebar--stack">
            <span class="ui-savebar__note">Le impostazioni valgono per il sito clienti e per il calendario.</span>
            <div class="ui-savebar__actions">
                <button class="ui-btn ui-btn--primary" type="submit">Salva impostazioni</button>
            </div>
        </div>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Contatti e social</h2></div>
            <div class="ui-field">
                <label for="phone">Telefono</label>
                <input type="text" name="phone" id="phone" value="{{ $property_contatti['phone'] ?? '' }}">
            </div>
            <div class="ui-field">
                <label for="email">Email</label>
                <input type="text" name="email" id="email" value="{{ $property_contatti['email'] ?? '' }}">
            </div>
            <div class="ui-field">
                <label for="whatsapp">WhatsApp</label>
                <input type="text" name="whatsapp" id="whatsapp" placeholder="+39001110000" value="{{ $property_contatti['whatsapp'] ?? '' }}">
            </div>
            <div class="ui-field">
                <label for="instagram">Instagram</label>
                <input type="text" name="instagram" id="instagram" placeholder="Link del profilo" value="{{ $property_contatti['instagram'] ?? '' }}">
            </div>
            <div class="ui-field">
                <label for="facebook">Facebook</label>
                <input type="text" name="facebook" id="facebook" placeholder="Link della pagina" value="{{ $property_contatti['facebook'] ?? '' }}">
            </div>
            <div class="ui-field">
                <label for="tiktok">TikTok</label>
                <input type="text" name="tiktok" id="tiktok" placeholder="Link del profilo" value="{{ $property_contatti['tiktok'] ?? '' }}">
            </div>
            <div class="ui-field">
                <label for="youtube">YouTube</label>
                <input type="text" name="youtube" id="youtube" placeholder="Link del canale" value="{{ $property_contatti['youtube'] ?? '' }}">
            </div>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head">
                <h2>Istruttori</h2>
                <span class="ui-panel__note">{{ count($trainers) }}</span>
            </div>
            @forelse ($trainers as $r)
                <div class="ui-name ui-name--media">
                    <span class="ui-avatar" aria-hidden="true" style="color: {{ $r->flag ?? 'inherit' }}">
                        {{ strtoupper(substr($r->name, 0, 1).substr($r->surname, 0, 1)) }}
                    </span>
                    <div class="ui-name__body">
                        <a href="{{ route('admin.players.show', $r) }}">#{{ $r->nickname }}</a>
                        <div class="ui-name__meta">
                            <span>{{ $r->name }} {{ $r->surname }}</span>
                            <span class="ui-code">liv {{ $r->level }}</span>
                        </div>
                    </div>
                    @if ($is_admin && $r->user_id !== auth()->id())
                        {{-- Il modulo sta in fondo alla pagina: qui dentro siamo
                             già dentro il form delle impostazioni. --}}
                        <button type="submit" class="ui-action ui-action--danger" style="margin-left:auto;"
                                form="trainer-del-{{ $r->user_id }}"
                                data-ui-trainer-del
                                data-nome="{{ $r->name }} {{ $r->surname }}"
                                data-nick="{{ $r->nickname }}"
                                aria-label="Rimuovi l'accesso di {{ $r->name }} {{ $r->surname }}">Rimuovi accesso</button>
                    @endif
                </div>
            @empty
                <p class="ui-hint">Nessun istruttore registrato.</p>
            @endforelse

            @if ($is_admin)
                <p class="ui-hint">Rimuovere un istruttore cancella solo il suo accesso al gestionale: la scheda giocatore, le lezioni e le prenotazioni restano in archivio.</p>
            @endif

            <a class="ui-btn" href="{{ route('admin.players.trainer_register') }}">
                @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                <span>Registra un istruttore</span>
            </a>
        </section>
    </aside>

</form>

@if ($is_admin)
    {{-- Moduli separati: i pulsanti dell'elenco li richiamano con form="…",
         perché il pannello vive dentro al form delle impostazioni. --}}
    @foreach ($trainers as $r)
        @continue($r->user_id === auth()->id())
        <form id="trainer-del-{{ $r->user_id }}" action="{{ route('admin.trainers.destroy', $r->user_id) }}" method="post">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
        b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
    });

    // Rimozione di un istruttore: si conferma qui, il modulo è in fondo alla pagina.
    document.querySelectorAll('[data-ui-trainer-del]').forEach((b) => {
        b.addEventListener('click', (ev) => {
            const ok = confirm("Rimuovere l'accesso di " + b.dataset.nome
                + "? La scheda giocatore #" + b.dataset.nick
                + " resta in archivio con le sue lezioni.");
            if (!ok) ev.preventDefault();
        });
    });

    // ---------- Orari dei campi ----------
    // La casella "Chiuso" spegne gli orari di quel giorno: disattivati non
    // vengono nemmeno inviati, e il salvataggio li legge come giorno chiuso.
    const spegniOrari = (riga) => {
        const chiuso = riga.querySelector('[data-ui-closed]');
        riga.querySelectorAll('input[type="time"]').forEach((i) => { i.disabled = chiuso.checked; });
    };

    document.querySelectorAll('[data-ui-day]').forEach((riga) => {
        riga.querySelector('[data-ui-closed]')?.addEventListener('change', () => spegniOrari(riga));
    });

    // Comodità per chi ha la settimana tutta uguale.
    const copiaLunedi = (contenitore) => {
        const righe = Array.from(contenitore.querySelectorAll('[data-ui-day]'));
        const primo = righe[0];
        if (!primo) return;

        const chiuso = primo.querySelector('[data-ui-closed]').checked;
        const orari = Array.from(primo.querySelectorAll('input[type="time"]')).map((i) => i.value);

        righe.slice(1).forEach((riga) => {
            riga.querySelector('[data-ui-closed]').checked = chiuso;
            riga.querySelectorAll('input[type="time"]').forEach((i, n) => { i.value = orari[n]; });
            spegniOrari(riga);
        });
    };

    document.querySelectorAll('[data-ui-week-copy]').forEach((btn) => {
        btn.addEventListener('click', () => copiaLunedi(btn.closest('.ui-field')));
    });

    // ---------- Nuovo campo ----------
    const container = document.getElementById('container');
    const addBtn = document.getElementById('addFieldBtn');
    const settimana = { 'Lunedì': 1, 'Martedì': 2, 'Mercoledì': 3, 'Giovedì': 4, 'Venerdì': 5, 'Sabato': 6, 'Domenica': 7 };
    let counter = 0;

    addBtn?.addEventListener('click', () => {
        counter++;
        const chiave = 'NewField_' + counter;

        const campo = (etichetta, nome, tipo, placeholder = '', aiuto = '') => {
            const id = nome + '_new_' + counter;
            const wrap = document.createElement('div');
            wrap.className = 'ui-field';
            wrap.innerHTML = `
                <label for="${id}">${etichetta}</label>
                <input type="${tipo}" id="${id}" name="field_set[${chiave}][${nome}]" placeholder="${placeholder}">
                ${aiuto ? `<p class="ui-hint">${aiuto}</p>` : ''}`;
            return wrap;
        };

        const sport = () => {
            const id = 'type_new_' + counter;
            const wrap = document.createElement('div');
            wrap.className = 'ui-field';
            wrap.innerHTML = `
                <label for="${id}">Sport</label>
                <select id="${id}" name="field_set[${chiave}][type]">
                    ${['Padel', 'Calcio', 'Tennis', 'Basket'].map((s) => `<option value="${s.toLowerCase()}">${s}</option>`).join('')}
                </select>`;
            return wrap;
        };

        const giorni = () => {
            const wrap = document.createElement('div');
            wrap.className = 'ui-field';
            wrap.innerHTML = `<label>Orari di apertura</label>
                <div class="ui-week" data-ui-week>
                    ${Object.entries(settimana).map(([nome, valore]) => `
                        <div class="ui-week__row" data-ui-day>
                            <span class="ui-week__day">${nome}</span>
                            <label class="ui-chips__item ui-week__closed">
                                <input type="checkbox" data-ui-closed name="field_set[${chiave}][hours][${valore}][closed]" value="1">
                                <span>Chiuso</span>
                            </label>
                            <input type="time" aria-label="${nome}, apertura" name="field_set[${chiave}][hours][${valore}][h_start]" value="08:00">
                            <span class="ui-week__sep" aria-hidden="true">→</span>
                            <input type="time" aria-label="${nome}, chiusura" name="field_set[${chiave}][hours][${valore}][h_end]" value="23:00">
                        </div>`).join('')}
                </div>
                <div class="ui-week__tools">
                    <button type="button" class="ui-action" data-ui-week-copy>Applica il lunedì a tutta la settimana</button>
                </div>`;

            // Gli stessi comandi dei pannelli già in pagina.
            wrap.querySelectorAll('[data-ui-day]').forEach((riga) => {
                riga.querySelector('[data-ui-closed]').addEventListener('change', () => spegniOrari(riga));
            });
            wrap.querySelector('[data-ui-week-copy]').addEventListener('click', () => copiaLunedi(wrap));

            return wrap;
        };

        const panel = document.createElement('section');
        panel.className = 'ui-panel';
        panel.innerHTML = '<div class="ui-panel__head"><h2>Nuovo campo</h2></div>';

        const griglia = document.createElement('div');
        griglia.className = 'ui-fields ui-fields--2';
        griglia.append(
            campo('Nome del campo', 'name_field', 'text', 'Es. Campo 3'),
            sport(),
            campo('Durata minima (min)', 'm_during', 'number', 'Minuti', 'Il passo della griglia degli orari.'),
            campo('Durata fascia (min)', 'm_during_client', 'number', 'Minuti', 'Segna le fasce piene nel calendario.')
        );

        const azioni = document.createElement('div');
        const rimuovi = document.createElement('button');
        rimuovi.type = 'button';
        rimuovi.className = 'ui-action ui-action--danger';
        rimuovi.textContent = 'Rimuovi questo campo';
        rimuovi.addEventListener('click', () => panel.remove());
        azioni.appendChild(rimuovi);

        panel.append(griglia, giorni(), azioni);
        container.appendChild(panel);
        panel.querySelector('input')?.focus();
    });

    // ---------- Orari istruttore ----------
    const radios = document.querySelectorAll('input[name="set_trainer[field]"]');
    const startSelect = document.getElementById('h_start_trainer');
    const endSelect = document.getElementById('h_end_trainer');

    function addMinutes(time, minutes) {
        const [h, m] = time.split(':').map(Number);
        const date = new Date();
        date.setHours(h, m + minutes);
        return date.toTimeString().substring(0, 5);
    }

    function generateOptions(hStart, nSlot, duration) {
        startSelect.innerHTML = '';
        endSelect.innerHTML = '';

        const slots = [];
        let current = hStart;
        for (let i = 0; i < nSlot; i++) {
            slots.push(current);
            current = addMinutes(current, duration);
        }

        slots.forEach((t) => {
            const opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            startSelect.appendChild(opt);
        });

        // La fine parte dallo slot successivo al primo
        for (let i = 1; i < slots.length; i++) {
            const opt = document.createElement('option');
            opt.value = slots[i];
            opt.textContent = slots[i];
            endSelect.appendChild(opt);
        }
    }

    radios.forEach((radio) => {
        radio.addEventListener('change', function () {
            const hStart = this.dataset.h_start;
            const nSlot = parseInt(this.dataset.n_slot);
            const duration = parseInt(this.dataset.m_during_client);
            if (!hStart || !nSlot || !duration || !startSelect || !endSelect) return;
            generateOptions(hStart, nSlot, duration);
        });
    });
});
</script>
@endsection
