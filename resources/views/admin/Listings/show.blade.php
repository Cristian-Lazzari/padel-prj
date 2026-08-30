@extends('layouts.ui')

@section('title', 'Annuncio - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.listings.index') }}">Bacheca</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>{{ Str::limit($listing->title, 40) }}</b>
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

@if ($listing->reject_reason)
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <span>Rifiutato: {{ $listing->reject_reason }}</span>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>{{ $listing->title }}</h1>
        <div class="ui-head__count">
            <span class="ui-pill {{ $listing->status === 'published' ? 'ui-pill--accent' : ($listing->status === 'pending' ? 'ui-pill--warn' : '') }}">
                {{ $listing->statusLabel() }}
            </span>
            <span>{{ $listing->priceLabel() }}</span>
            <span>{{ $listing->views }} visualizzazioni</span>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.listings.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna alla bacheca</span>
        </a>
    </div>
</header>

<div class="ui-split">
    <div class="ui-split__main">

        @if ($listing->images->count())
            <section class="ui-section">
                <div class="ui-section__head">
                    <h2>Foto</h2>
                    <div class="ui-section__meta"><span class="ui-pill">{{ $listing->images->count() }}</span></div>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    @foreach ($listing->images as $img)
                        <a href="{{ $img->url }}" target="_blank" rel="noopener noreferrer">
                            <img src="{{ $img->url }}" alt="Foto {{ $loop->iteration }} di {{ $listing->title }}"
                                 style="width:140px; height:140px; object-fit:cover; border-radius:20px; display:block;">
                        </a>
                    @endforeach
                </div>
            </section>
        @else
            <p class="ui-hint">Nessuna immagine allegata.</p>
        @endif

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Descrizione</h2></div>
            <p style="white-space: pre-line">{{ $listing->description }}</p>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Moderazione</h2></div>

            @if ($listing->status !== 'published')
                <form action="{{ route('admin.listings.approve', $listing) }}" method="post">
                    @csrf
                    <div class="ui-field" style="max-width: 320px;">
                        <label for="days">Giorni di pubblicazione</label>
                        <input type="number" name="days" id="days" min="1" max="365"
                               placeholder="{{ \App\Models\Listing::defaultDurationDays() }} giorni">
                        <p class="ui-hint">Lascia vuoto per usare la durata di default.</p>
                    </div>
                    <button class="ui-btn ui-btn--primary" type="submit">Approva e pubblica</button>
                </form>
            @endif

            <form action="{{ route('admin.listings.reject', $listing) }}" method="post">
                @csrf
                <div class="ui-field">
                    <label for="reason">Motivo del rifiuto <b>*</b></label>
                    <input type="text" name="reason" id="reason" placeholder="Viene mostrato al venditore" required>
                    @error('reason') <p class="ui-err">@include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 13]) {{ $message }}</p> @enderror
                </div>
                <button class="ui-btn ui-btn--danger" type="submit">Rifiuta l'annuncio</button>
            </form>

            <form action="{{ route('admin.listings.status', $listing) }}" method="post">
                @csrf
                <div class="ui-field" style="max-width: 320px;">
                    <label for="status">Cambia stato a mano</label>
                    <select name="status" id="status">
                        <option value="pending" @selected($listing->status === 'pending')>In attesa</option>
                        <option value="published" @selected($listing->status === 'published')>Pubblicato</option>
                        <option value="sold" @selected($listing->status === 'sold')>Venduto</option>
                        <option value="expired" @selected($listing->status === 'expired')>Scaduto</option>
                    </select>
                </div>
                <button class="ui-btn" type="submit">Applica stato</button>
            </form>
        </section>
    </div>

    <aside class="ui-split__side">
        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Annuncio</h2></div>
            <div class="ui-facts" style="grid-template-columns: 1fr;">
                <div class="ui-fact">
                    <span>Venditore</span>
                    <strong><a href="{{ route('admin.players.show', $listing->player_id) }}">#{{ $listing->player?->nickname }}</a></strong>
                </div>
                <div class="ui-fact"><span>Categoria</span><strong>{{ ucfirst($listing->category) }}</strong></div>
                <div class="ui-fact"><span>Condizione</span><strong>{{ ucfirst($listing->condition) }}</strong></div>
                <div class="ui-fact">
                    <span>Inviato il</span>
                    <strong>{{ $listing->created_at->format('d/m/Y H:i') }}</strong>
                    <small>{{ $listing->expires_at ? 'scade il '.$listing->expires_at->format('d/m/Y') : 'nessuna scadenza' }}</small>
                </div>
            </div>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Contatti nell'annuncio</h2></div>
            <div class="ui-facts" style="grid-template-columns: 1fr;">
                <div class="ui-fact">
                    <span>Telefono</span>
                    <strong>{{ $listing->contact_phone ?: '—' }}</strong>
                    <small>{{ $listing->show_phone ? 'Visibile ai clienti' : 'Nascosto' }}</small>
                </div>
                <div class="ui-fact">
                    <span>Email</span>
                    <strong style="font-size:14px; word-break:break-all;">{{ $listing->contact_mail ?: '—' }}</strong>
                    <small>{{ $listing->show_mail ? 'Visibile ai clienti' : 'Nascosta' }}</small>
                </div>
            </div>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head"><h2>Zona pericolosa</h2></div>
            <p class="ui-hint">Eliminando l'annuncio spariscono anche tutte le immagini caricate.</p>
            <button class="ui-btn ui-btn--danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteListing">
                @include('admin.partials.ui-icon', ['name' => 'trash3-fill', 'size' => 16])
                <span>Elimina annuncio</span>
            </button>
        </section>
    </aside>
</div>

<div class="modal fade ui-modal" id="deleteListing" tabindex="-1" aria-labelledby="deleteListingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <h2 id="deleteListingLabel" style="font-size:19px;font-weight:700;margin-bottom:10px;">
                    Eliminare "{{ $listing->title }}"?
                </h2>
                <p class="ui-hint">Verranno rimosse anche tutte le immagini caricate. L'operazione non si annulla.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="ui-btn" data-bs-dismiss="modal">Lascia com'è</button>
                <form action="{{ route('admin.listings.destroy', $listing) }}" method="post">
                    @method('DELETE')
                    @csrf
                    <button class="ui-btn ui-btn--danger" type="submit">Elimina</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
    b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
});
</script>
@endsection
