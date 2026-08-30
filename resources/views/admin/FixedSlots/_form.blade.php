{{-- Campi condivisi da creazione e modifica del campo fisso --}}
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
                <input type="time" name="start_time" id="start_time" value="{{ old('start_time', $slot->start_time) }}" required>
                @error('start_time') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="duration">Durata <b>*</b></label>
                <input type="number" name="duration" id="duration" min="1" max="12" value="{{ old('duration', $slot->duration) }}" required>
                <p class="ui-hint">In numero di slot: uno slot è la durata minima del campo, di norma 30 minuti.</p>
                @error('duration') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
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
            <label for="valid_to">Valido fino al</label>
            <input type="date" name="valid_to" id="valid_to"
                   value="{{ old('valid_to', optional($slot->valid_to)->format('Y-m-d')) }}">
            <p class="ui-hint">Lascia vuoto per un accordo a tempo indeterminato.</p>
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
</script>
