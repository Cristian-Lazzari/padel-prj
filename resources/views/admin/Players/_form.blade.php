{{--
    Campi del giocatore, condivisi da creazione e modifica.
    Prima i due moduli erano copie divergenti: quello di creazione non esponeva
    città, mano, posizione, foto, bio e scadenza del certificato, che però il
    controller salvava già. Un file solo, stessi campi in entrambi i casi.
    Attende: $player (anche nuovo), $nuovo (bool).
--}}
<div class="ui-form__main">

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Identità</h2></div>
        <div class="ui-fields ui-fields--2">
            <div class="ui-field">
                <label for="nickname">Soprannome <b>*</b></label>
                <input type="text" name="nickname" id="nickname" placeholder="Come lo chiamano in campo"
                       value="{{ old('nickname', $player->nickname) }}" required>
                @error('nickname') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="sex">Sesso <b>*</b></label>
                <select name="sex" id="sex" required>
                    <option value="m" @selected(old('sex', $player->sex) === 'm')>Uomo</option>
                    <option value="f" @selected(old('sex', $player->sex) === 'f')>Donna</option>
                </select>
                @error('sex') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="name">Nome <b>*</b></label>
                <input type="text" name="name" id="name" value="{{ old('name', $player->name) }}" required>
                @error('name') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="surname">Cognome <b>*</b></label>
                <input type="text" name="surname" id="surname" value="{{ old('surname', $player->surname) }}" required>
                @error('surname') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="birth_date">Data di nascita</label>
                <input type="date" name="birth_date" id="birth_date"
                       value="{{ old('birth_date', optional($player->birth_date)->format('Y-m-d')) }}">
                @error('birth_date') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="city">Città</label>
                <input type="text" name="city" id="city" value="{{ old('city', $player->city) }}">
                @error('city') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Contatti</h2></div>
        <div class="ui-fields ui-fields--2">
            <div class="ui-field">
                <label for="mail">Email <b>*</b></label>
                <input type="email" name="mail" id="mail" value="{{ old('mail', $player->mail) }}" required>
                <p class="ui-hint">Serve per la verifica e per le comunicazioni del circolo.</p>
                @error('mail') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="phone">Telefono <b>*</b></label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone', $player->phone) }}" required>
                @error('phone') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>In campo</h2></div>
        <div class="ui-fields ui-fields--2">
            <div class="ui-field">
                <label for="level">Livello <b>*</b></label>
                <input type="number" name="level" id="level" min="1" max="5" step="1"
                       value="{{ old('level', $player->level ?: 1) }}" required>
                <p class="ui-hint">Da 1 (principiante) a 5 (agonista).</p>
                @error('level') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="hand">Mano</label>
                <select name="hand" id="hand">
                    <option value="">Non indicata</option>
                    <option value="dx" @selected(old('hand', $player->hand) === 'dx')>Destro</option>
                    <option value="sx" @selected(old('hand', $player->hand) === 'sx')>Mancino</option>
                </select>
                @error('hand') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="preferred_position">Posizione preferita</label>
                <select name="preferred_position" id="preferred_position">
                    <option value="">Non indicata</option>
                    <option value="dritto" @selected(old('preferred_position', $player->preferred_position) === 'dritto')>Dritto</option>
                    <option value="rovescio" @selected(old('preferred_position', $player->preferred_position) === 'rovescio')>Rovescio</option>
                    <option value="indifferente" @selected(old('preferred_position', $player->preferred_position) === 'indifferente')>Indifferente</option>
                </select>
                @error('preferred_position') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </div>

        <div class="ui-field">
            <label for="bio">Bio</label>
            <textarea name="bio" id="bio" placeholder="Due righe sul giocatore">{{ old('bio', $player->bio) }}</textarea>
            @error('bio') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>

        <div class="ui-field">
            <label for="note">Note interne</label>
            <textarea name="note" id="note" placeholder="Visibili solo allo staff">{{ old('note', $player->note) }}</textarea>
            @error('note') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
    </section>
</div>

<aside class="ui-form__side">
    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Foto profilo</h2></div>
        <div class="ui-media">
            @if ($player->img_url)
                <img class="ui-media__thumb ui-media__thumb--round" src="{{ $player->img_url }}" alt="Foto attuale">
            @else
                <span class="ui-media__thumb ui-media__thumb--round" aria-hidden="true">
                    {{ strtoupper(substr($player->name ?? '', 0, 1).substr($player->surname ?? '', 0, 1)) ?: '—' }}
                </span>
            @endif
            <div class="ui-media__body">
                <label class="ui-vh" for="img">Nuova foto profilo</label>
                <input type="file" name="img" id="img" accept="image/jpeg,image/png,image/webp">
                @if ($player->img)
                    <label class="ui-check" for="remove_img">
                        <input type="checkbox" name="remove_img" id="remove_img" value="1">
                        Rimuovi la foto attuale
                    </label>
                @endif
            </div>
        </div>
        @error('img') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Certificato medico</h2></div>
        <div class="ui-field">
            <label for="certificate">Documento</label>
            <input type="file" name="certificate" id="certificate" accept="application/pdf,image/*">
            @if ($player->certificate)
                <p class="ui-hint">
                    <a href="{{ Storage::disk('public')->url($player->certificate) }}" target="_blank" rel="noopener noreferrer">Apri il certificato attuale</a>
                </p>
            @endif
            @error('certificate') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
        <div class="ui-field">
            <label for="certificate_expires_at">Scadenza</label>
            <input type="date" name="certificate_expires_at" id="certificate_expires_at"
                   value="{{ old('certificate_expires_at', optional($player->certificate_expires_at)->format('Y-m-d')) }}">
            <p class="ui-hint">A 30 giorni dalla scadenza il giocatore compare come "in scadenza" nell'elenco.</p>
            @error('certificate_expires_at') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
    </section>
</aside>
