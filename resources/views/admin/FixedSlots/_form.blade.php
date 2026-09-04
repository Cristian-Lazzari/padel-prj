{{-- Campi condivisi da creazione e modifica del campo fisso --}}
@php
    // Il campo scelto detta la griglia: gli orari delle due select sono i suoi,
    // dall'apertura alla chiusura. Il javascript qui sotto le rifà al volo
    // quando si cambia campo, questo è quanto serve senza javascript.
    $field_selected = old('field', $slot->field);
    // Se il campo salvato non è più in impostazioni, si riparte dal primo.
    if (! isset($grids[$field_selected])) {
        $field_selected = array_key_first($grids);
    }
    $weekday_selected = (int) old('weekday', $slot->weekday);
    $points = $grids[$field_selected]['days'][$weekday_selected] ?? [];
    $step = $grids[$field_selected]['step'] ?? 30;

    $start_current = substr((string) old('start_time', $slot->start_time), 0, 5);
    if (! in_array($start_current, $points, true)) {
        $start_current = $points[0] ?? '';
    }
    $start_index = array_search($start_current, $points, true);

    $end_current = substr((string) old('end_time', ''), 0, 5);
    if (! in_array($end_current, $points, true) && $start_index !== false) {
        // Proposta: la durata che il campo fisso ha già (o le tre fasce
        // di partenza), tagliata alla chiusura se non ci sta.
        $end_index = min($start_index + max(1, (int) $slot->duration), count($points) - 1);
        $end_current = $points[$end_index] ?? '';
    }
@endphp
<div class="ui-form__main">

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>A chi e dove</h2></div>
        <div class="ui-fields">
            <div class="ui-field">
                <label for="player_id">Giocatore <b>*</b></label>
                {{-- La ricerca filtra le opzioni, ma il campo inviato resta la select --}}
                <input type="text" id="playerSearch" placeholder="Cerca per soprannome, nome o cognome..." autocomplete="off">
                <select name="player_id" id="player_id" required>
                    <option value="">Seleziona un giocatore</option>
                    @foreach ($players as $p)
                        <option value="{{ $p->id }}"
                            data-search="{{ Str::lower($p->nickname.' '.$p->name.' '.$p->surname) }}"
                            @selected((string) old('player_id', $slot->player_id) === (string) $p->id)>
                            #{{ $p->nickname }} — {{ $p->name }} {{ $p->surname }}
                        </option>
                    @endforeach
                </select>
                @error('player_id') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </div>

        <div class="ui-fields ui-fields--2">
            <div class="ui-field">
                <label for="field">Campo <b>*</b></label>
                <select name="field" id="field" required>
                    @foreach ($field_set as $key => $f)
                        <option value="{{ $key }}" @selected(old('field', $slot->field) === $key)>{{ $key }}</option>
                    @endforeach
                </select>
                @error('field') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="weekday">Giorno della settimana <b>*</b></label>
                <select name="weekday" id="weekday" required>
                    @foreach ($weekdays as $value => $label)
                        <option value="{{ $value }}" @selected((string) old('weekday', $slot->weekday) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('weekday') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="start_time">Ora di inizio <b>*</b></label>
                <select name="start_time" id="start_time" required>
                    @foreach (array_slice($points, 0, -1) as $t)
                        <option value="{{ $t }}" @selected($t === $start_current)>{{ $t }}</option>
                    @endforeach
                </select>
                <p class="ui-hint" data-ui-chiuso @if ($points) hidden @endif>Il campo è chiuso in questo giorno: scegline un altro o cambia gli orari in impostazioni.</p>
                <p class="ui-hint" data-ui-passo @if (! $points) hidden @endif>Gli orari sono quelli del campo in questo giorno, uno ogni {{ $step }} minuti.</p>
                @error('start_time') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="end_time">Ora di fine <b>*</b></label>
                <select name="end_time" id="end_time" required>
                    @foreach ($points as $i => $t)
                        @continue($start_index === false || $i <= $start_index)
                        <option value="{{ $t }}" @selected($t === $end_current)>{{ $t }}</option>
                    @endforeach
                </select>
                <p class="ui-hint" data-ui-durata>L'elenco arriva fino alla chiusura del campo: oltre non si va.</p>
                @error('end_time') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Note</h2></div>
        <div class="ui-field">
            <label class="ui-vh" for="note">Note sul campo fisso</label>
            <textarea name="note" id="note" placeholder="Accordi, orari particolari, contatti">{{ old('note', $slot->note) }}</textarea>
            @error('note') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
    </section>
</div>

<aside class="ui-form__side">
    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Validità</h2></div>
        <div class="ui-field">
            <label for="valid_from">Valido dal <b>*</b></label>
            <input type="date" name="valid_from" id="valid_from"
                   value="{{ old('valid_from', optional($slot->valid_from)->format('Y-m-d')) }}" required>
            @error('valid_from') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
        <div class="ui-field">
            <label for="valid_to">Valido fino al <b>*</b></label>
            <input type="date" name="valid_to" id="valid_to"
                   value="{{ old('valid_to', optional($slot->valid_to)->format('Y-m-d')) }}" required>
            <p class="ui-hint">
                Le prenotazioni vengono create tutte adesso, da qui a questa data:
                serve quindi una fine. Per rinnovare l'accordo basta spostarla in avanti
                e salvare. Al massimo due anni.
            </p>
            @error('valid_to') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
        <div class="ui-field">
            <label for="status">Stato <b>*</b></label>
            <select name="status" id="status" required>
                <option value="active" @selected(old('status', $slot->status) === 'active')>Attivo</option>
                <option value="suspended" @selected(old('status', $slot->status) === 'suspended')>Sospeso</option>
                <option value="ended" @selected(old('status', $slot->status) === 'ended')>Concluso</option>
            </select>
            <p class="ui-hint">Solo gli attivi occupano il campo.</p>
            @error('status') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
        <div class="ui-field">
            <label for="price">Quota concordata (€)</label>
            <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price', $slot->price) }}">
            <p class="ui-hint">Si salda in struttura.</p>
            @error('price') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
    </section>
</aside>

<script>
// Filtro rapido sull'elenco giocatori: la select resta il campo inviato.
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('playerSearch');
    const select = document.getElementById('player_id');
    if (!search || !select) return;

    const options = Array.from(select.options).map((o) => ({ el: o, key: o.dataset.search || '' }));

    search.addEventListener('input', () => {
        const term = search.value.toLowerCase().trim();
        options.forEach(({ el, key }) => {
            if (!el.value) return;
            el.hidden = term.length > 0 && !key.includes(term);
        });

        const visible = options.filter(({ el }) => el.value && !el.hidden);
        if (term && visible.length === 1) select.value = visible[0].el.value;
    });
});

// Orari: ogni campo ha la sua griglia, dall'apertura alla chiusura.
// L'ora di inizio elenca tutti i punti tranne l'ultimo (dopo non ci sta
// nemmeno una fascia), l'ora di fine solo quelli successivi all'inizio:
// il limite della durata è la chiusura del campo, non un numero deciso qui.
document.addEventListener('DOMContentLoaded', () => {
    const griglie = @json($grids);
    const campo = document.getElementById('field');
    const giorno = document.getElementById('weekday');
    const inizio = document.getElementById('start_time');
    const fine = document.getElementById('end_time');
    const nota = document.querySelector('[data-ui-durata]');
    const avvisoChiuso = document.querySelector('[data-ui-chiuso]');
    const notaPasso = document.querySelector('[data-ui-passo]');
    if (!campo || !giorno || !inizio || !fine) return;

    let indiceInizio = -1; // serve a conservare la durata quando si sposta l'inizio

    // Gli orari dipendono dal campo e dal giorno: un campo può aprire alle
    // 08:00 il lunedì, alle 15:00 il sabato e restare chiuso la domenica.
    const punti = () => ((griglie[campo.value] || {}).days || {})[giorno.value] || [];
    const passo = () => (griglie[campo.value] || {}).step || 30;

    function riempi(select, orari, preferito) {
        select.innerHTML = '';
        orari.forEach((t) => {
            const opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            select.appendChild(opt);
        });
        select.value = orari.includes(preferito) ? preferito : (orari[0] || '');
        select.disabled = orari.length === 0;
    }

    function scriviDurata() {
        const chiuso = punti().length < 2;
        if (avvisoChiuso) avvisoChiuso.hidden = !chiuso;
        if (notaPasso) notaPasso.hidden = chiuso;

        if (!nota) return;

        const p = punti();
        const fasce = p.indexOf(fine.value) - p.indexOf(inizio.value);

        if (fasce <= 0) {
            nota.textContent = chiuso
                ? ''
                : "L'elenco arriva fino alla chiusura del campo: oltre non si va.";
            return;
        }

        const minuti = fasce * passo();
        const ore = Math.floor(minuti / 60);
        const resto = minuti % 60;
        const durata = [ore ? ore + (ore === 1 ? ' ora' : ' ore') : '', resto ? resto + ' minuti' : '']
            .filter(Boolean).join(' e ');

        nota.textContent = durata + ' — ' + fasce + (fasce === 1 ? ' fascia' : ' fasce')
            + '. Il campo chiude alle ' + p[p.length - 1] + '.';
    }

    function aggiornaFine(preferito) {
        const p = punti();
        const i = p.indexOf(inizio.value);
        riempi(fine, i < 0 ? [] : p.slice(i + 1), preferito);
        indiceInizio = i;
        scriviDurata();
    }

    function aggiornaTutto() {
        const p = punti();
        riempi(inizio, p.slice(0, -1), inizio.value);
        aggiornaFine(fine.value);
    }

    campo.addEventListener('change', aggiornaTutto);
    giorno.addEventListener('change', aggiornaTutto);

    inizio.addEventListener('change', () => {
        // Spostando l'inizio si tiene la stessa durata, se ci sta ancora.
        const p = punti();
        const fasce = Math.max(1, p.indexOf(fine.value) - indiceInizio);
        const nuovo = p[Math.min(p.indexOf(inizio.value) + fasce, p.length - 1)];
        aggiornaFine(nuovo);
    });

    fine.addEventListener('change', scriviDurata);

    indiceInizio = punti().indexOf(inizio.value);
    scriviDurata();
});
</script>
