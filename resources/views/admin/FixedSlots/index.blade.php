@extends('layouts.ui')

@section('title', 'Campi fissi - F+')

@section('page_vars', '--ui-cols: minmax(0, 2.2fr) 150px 160px 170px 90px;')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Campi fissi</b>
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

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Campi fissi</h1>
        @if ($slots->isNotEmpty())
            <div class="ui-head__count">
                <span>In tutto <b>{{ $slots->count() }}</b></span>
                <span>Attivi <b>{{ $slots->where('status', 'active')->count() }}</b></span>
            </div>
        @endif
    </div>
    @if ($slots->isNotEmpty())
        <div class="ui-head__actions">
            <a class="ui-btn ui-btn--primary" href="{{ route('admin.fixed-slots.create') }}">
                @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                <span>Nuovo campo fisso</span>
            </a>
        </div>
    @endif
</header>

<div class="ui-list" role="table" aria-label="Elenco dei campi fissi">
    @if ($slots->isNotEmpty())
        <div class="ui-list__head" role="row">
            <span role="columnheader">Assegnatario</span>
            <span role="columnheader">Quando</span>
            <span role="columnheader">Campo</span>
            <span role="columnheader">Validità</span>
            <span role="columnheader" class="ui-vh">Azioni</span>
        </div>
    @endif

    @forelse ($slots as $s)
        @php $minutes = $field_set[$s->field]['m_during'] ?? 30; @endphp
        <article class="ui-row {{ $s->status !== 'active' ? 'ui-row--muted' : '' }}" role="row">
            <div class="ui-name" role="cell">
                <a href="{{ route('admin.fixed-slots.show', $s) }}">#{{ $s->player?->nickname ?? 'senza giocatore' }}</a>
                <div class="ui-name__meta">
                    <span class="ui-status ui-status--{{ $s->status === 'active' ? 'running' : ($s->status === 'suspended' ? 'closed' : 'finished') }}">
                        {{ $s->statusLabel() }}
                    </span>
                    <span>{{ $s->player?->name }} {{ $s->player?->surname }}</span>
                    @if ($s->exceptions_count)
                        <span class="ui-pill ui-pill--warn">{{ $s->exceptions_count }} eccezioni</span>
                    @endif
                </div>
            </div>

            <div class="ui-cell" data-label="Quando" role="cell">
                <strong>{{ $s->weekdayLabel() }}</strong>
                <span>{{ $s->start_time }} → {{ $s->endTime($minutes) }}</span>
            </div>

            <div class="ui-cell" data-label="Campo" role="cell">
                <strong>{{ $s->field }}</strong>
                <span>{{ $s->price ? number_format((float) $s->price, 2, ',', '.').' €' : 'Quota non indicata' }}</span>
            </div>

            <div class="ui-cell" data-label="Validità" role="cell">
                <strong>dal {{ $s->valid_from?->format('d/m/Y') }}</strong>
                <span>{{ $s->valid_to ? 'al '.$s->valid_to->format('d/m/Y') : 'a tempo indeterminato' }}</span>
            </div>

            <div class="ui-actions" role="cell">
                <a class="ui-action ui-action--icon" href="{{ route('admin.fixed-slots.edit', $s) }}"
                   aria-label="Modifica il campo fisso di {{ $s->player?->nickname }}" title="Modifica">
                    @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
                </a>
                <a class="ui-action ui-action--icon" href="{{ route('admin.fixed-slots.show', $s) }}"
                   aria-label="Apri il campo fisso di {{ $s->player?->nickname }}" title="Apri">
                    @include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 16])
                </a>
            </div>
        </article>
    @empty
        <div class="ui-empty">
            <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'arrow-repeat', 'size' => 25])</span>
            <h2>Nessun campo fisso assegnato</h2>
            <p>Un campo fisso blocca ogni settimana lo stesso slot per lo stesso giocatore, e genera da solo le prenotazioni future.</p>
            <a class="ui-btn ui-btn--primary" href="{{ route('admin.fixed-slots.create') }}">
                @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                <span>Nuovo campo fisso</span>
            </a>
        </div>
    @endforelse
</div>

@endsection

@section('scripts')
<script>
document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
    b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
});
</script>
@endsection
