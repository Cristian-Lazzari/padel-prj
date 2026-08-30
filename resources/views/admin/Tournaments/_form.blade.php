{{-- Campi condivisi da creazione e modifica del torneo --}}
<div class="ui-form__main">

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Il torneo</h2></div>
        <div class="ui-fields ui-fields--2">
            <div class="ui-field">
                <label for="name">Nome <b>*</b></label>
                <input type="text" name="name" id="name" placeholder="Es. Torneo di primavera"
                       value="{{ old('name', $tournament->name) }}" required>
                @error('name') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="type">Sport</label>
                <input type="text" name="type" id="type" placeholder="Padel" value="{{ old('type', $tournament->type) }}">
                @error('type') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="format">Formula <b>*</b></label>
                <select name="format" id="format" required>
                    <option value="gironi" @selected(old('format', $tournament->format) === 'gironi')>Gironi</option>
                    <option value="eliminazione" @selected(old('format', $tournament->format) === 'eliminazione')>Eliminazione diretta</option>
                    <option value="americano" @selected(old('format', $tournament->format) === 'americano')>Americano</option>
                </select>
                @error('format') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="is_pair">Si gioca</label>
                <select name="is_pair" id="is_pair">
                    <option value="1" @selected(old('is_pair', $tournament->is_pair ? '1' : '0') == '1')>A coppie</option>
                    <option value="0" @selected(old('is_pair', $tournament->is_pair ? '1' : '0') == '0')>Singolo</option>
                </select>
                @error('is_pair') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="teams_max">Posti disponibili <b>*</b></label>
                <input type="number" name="teams_max" id="teams_max" min="2" max="128"
                       value="{{ old('teams_max', $tournament->teams_max) }}" required>
                <p class="ui-hint">Quante squadre (o giocatori, se singolo) entrano nel tabellone.</p>
                @error('teams_max') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="location">Luogo</label>
                <input type="text" name="location" id="location" placeholder="Free Sport Chiaravalle"
                       value="{{ old('location', $tournament->location) }}">
                @error('location') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Quando</h2></div>
        <div class="ui-fields ui-fields--2">
            <div class="ui-field">
                <label for="starts_at">Inizio <b>*</b></label>
                <input type="datetime-local" name="starts_at" id="starts_at"
                       value="{{ old('starts_at', optional($tournament->starts_at)->format('Y-m-d\TH:i')) }}" required>
                @error('starts_at') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="ends_at">Fine</label>
                <input type="datetime-local" name="ends_at" id="ends_at"
                       value="{{ old('ends_at', optional($tournament->ends_at)->format('Y-m-d\TH:i')) }}">
                @error('ends_at') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="registration_opens_at">Apertura iscrizioni</label>
                <input type="datetime-local" name="registration_opens_at" id="registration_opens_at"
                       value="{{ old('registration_opens_at', optional($tournament->registration_opens_at)->format('Y-m-d\TH:i')) }}">
                @error('registration_opens_at') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="registration_closes_at">Chiusura iscrizioni</label>
                <input type="datetime-local" name="registration_closes_at" id="registration_closes_at"
                       value="{{ old('registration_closes_at', optional($tournament->registration_closes_at)->format('Y-m-d\TH:i')) }}">
                <p class="ui-hint">Oltre questa data i giocatori non possono più iscriversi né ritirarsi da soli.</p>
                @error('registration_closes_at') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Testi</h2></div>
        <div class="ui-field">
            <label for="description">Descrizione</label>
            <textarea name="description" id="description" placeholder="Presentazione del torneo">{{ old('description', $tournament->description) }}</textarea>
            @error('description') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
        <div class="ui-field">
            <label for="regulation">Regolamento</label>
            <textarea name="regulation" id="regulation" placeholder="Regole di gioco, formula, premi...">{{ old('regulation', $tournament->regulation) }}</textarea>
            @error('regulation') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
        <div class="ui-field">
            <label for="note">Note interne</label>
            <textarea name="note" id="note" placeholder="Non mostrate ai clienti">{{ old('note', $tournament->note) }}</textarea>
            @error('note') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
    </section>
</div>

<aside class="ui-form__side">
    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Pubblicazione</h2></div>
        <div class="ui-field">
            <label for="status">Stato <b>*</b></label>
            <select name="status" id="status" required>
                @foreach (['draft' => 'Bozza', 'open' => 'Iscrizioni aperte', 'closed' => 'Iscrizioni chiuse', 'running' => 'In corso', 'finished' => 'Concluso', 'cancelled' => 'Annullato'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $tournament->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="ui-hint">In "Bozza" il torneo non compare sul sito clienti.</p>
            @error('status') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
        <div class="ui-field">
            <label for="price">Quota di iscrizione (€)</label>
            <input type="number" name="price" id="price" step="0.01" min="0" placeholder="Es. 25.00"
                   value="{{ old('price', $tournament->price) }}">
            <p class="ui-hint">Si salda in struttura: non è previsto il pagamento online.</p>
            @error('price') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Livello ammesso</h2></div>
        <div class="ui-fields ui-fields--2">
            <div class="ui-field">
                <label for="level_min">Da</label>
                <select name="level_min" id="level_min">
                    <option value="">Nessuno</option>
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected((string) old('level_min', $tournament->level_min) === (string) $i)>{{ $i }}</option>
                    @endfor
                </select>
                @error('level_min') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="level_max">A</label>
                <select name="level_max" id="level_max">
                    <option value="">Nessuno</option>
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected((string) old('level_max', $tournament->level_max) === (string) $i)>{{ $i }}</option>
                    @endfor
                </select>
                @error('level_max') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </div>
        <p class="ui-hint">Lasciando "Nessuno" il torneo è aperto a tutti i livelli.</p>
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Copertina</h2></div>
        <div class="ui-media">
            @if ($tournament->cover_url)
                <img class="ui-media__thumb" src="{{ $tournament->cover_url }}" alt="Copertina attuale">
            @else
                <span class="ui-media__thumb" aria-hidden="true">Nessuna</span>
            @endif
            <div class="ui-media__body">
                <label class="ui-vh" for="cover">Nuova copertina</label>
                <input type="file" name="cover" id="cover" accept="image/jpeg,image/png,image/webp">
                @if ($tournament->cover)
                    <label class="ui-check" for="remove_cover">
                        <input type="checkbox" name="remove_cover" id="remove_cover" value="1">
                        Rimuovi la copertina
                    </label>
                @endif
            </div>
        </div>
        @error('cover') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
    </section>
</aside>
