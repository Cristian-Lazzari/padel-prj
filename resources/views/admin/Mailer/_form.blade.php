{{--
    Campi del modello di email, condivisi da creazione e modifica.
    $model è null in creazione.
--}}
<div class="ui-form__main">
    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Intestazione</h2></div>
        <div class="ui-fields ui-fields--2">
            <div class="ui-field">
                <label for="name">Nome del modello <b>*</b></label>
                <input type="text" name="name" id="name" placeholder="Come lo riconosci tu"
                       value="{{ old('name', $model?->name) }}" required>
                <p class="ui-hint">Serve solo a te: non compare nella mail.</p>
                @error('name') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="sender">Firma del mittente <b>*</b></label>
                <input type="text" name="sender" id="sender" placeholder="Es. Con affetto, Marco Rossi"
                       value="{{ old('sender', $model?->sender) }}" required>
                @error('sender') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="object">Oggetto della mail <b>*</b></label>
                <input type="text" name="object" id="object" placeholder="Quello che si legge nella casella di posta"
                       value="{{ old('object', $model?->object) }}" required>
                @error('object') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
            <div class="ui-field">
                <label for="heading">Titolo dentro la mail <b>*</b></label>
                <input type="text" name="heading" id="heading" placeholder="La prima riga grande del messaggio"
                       value="{{ old('heading', $model?->heading) }}" required>
                @error('heading') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head">
            <h2>Testo</h2>
            <span class="ui-panel__note">\n per andare a capo · /*/ per un nuovo paragrafo</span>
        </div>
        <div class="ui-field">
            <label for="body">Corpo <b>*</b></label>
            <textarea name="body" id="body" style="min-height: 200px;" required>{{ trim(old('body', $model?->body ?? '')) }}</textarea>
            @error('body') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
        <div class="ui-field">
            <label for="ending">Conclusione <b>*</b></label>
            <textarea name="ending" id="ending">{{ trim(old('ending', $model?->ending ?? '')) }}</textarea>
            @error('ending') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
        </div>
    </section>
</div>

<aside class="ui-form__side">
    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Immagine principale</h2></div>
        <div class="ui-media">
            @if ($model?->img_1)
                <img class="ui-media__thumb" src="{{ asset('public/storage/'.$model->img_1) }}" alt="Immagine attuale">
            @else
                <span class="ui-media__thumb" aria-hidden="true">Nessuna</span>
            @endif
            <div class="ui-media__body">
                <label class="ui-vh" for="img_1">Immagine principale</label>
                <input type="file" name="img_1" id="img_1" accept="image/*">
            </div>
        </div>
        @error('img_1') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
    </section>

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Immagine secondaria</h2></div>
        <div class="ui-media">
            @if ($model?->img_2)
                <img class="ui-media__thumb" src="{{ asset('public/storage/'.$model->img_2) }}" alt="Immagine attuale">
            @else
                <span class="ui-media__thumb" aria-hidden="true">Nessuna</span>
            @endif
            <div class="ui-media__body">
                <label class="ui-vh" for="img_2">Immagine secondaria</label>
                <input type="file" name="img_2" id="img_2" accept="image/*">
            </div>
        </div>
        @error('img_2') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
    </section>
</aside>
