@extends('layouts.ui')

@section('title', 'Torneo - F+')

@section('contents')

@php
    $registrations = $tournament->registrations;
    $confirmed = $registrations->where('status', 'confirmed');
    $waitlist  = $registrations->where('status', 'waitlist');
    $pending   = $registrations->where('status', 'pending');
    $posti     = max((int) $tournament->teams_max, 0);
    $quota     = $posti > 0 ? min(100, round($confirmed->count() / $posti * 100)) : 0;
@endphp

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <a href="{{ route('admin.tournaments.index') }}">Tornei</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>{{ $tournament->name }}</b>
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

@if (session('error_message'))
    <div class="ui-flash ui-flash--error" role="alert">
        @include('admin.partials.ui-icon', ['name' => 'exclamation-triangle-fill', 'size' => 20])
        <span>{{ session('error_message') }}</span>
    </div>
@endif

<header class="ui-head">
    <div class="ui-head__title">
        <h1>{{ $tournament->name }}</h1>
        <div class="ui-head__count">
            <span class="ui-status ui-status--{{ $tournament->status }}">{{ $tournament->statusLabel() }}</span>
            <span>{{ $tournament->starts_at?->locale('it')->translatedFormat('D j M Y, H:i') }}</span>
            <span>Iscritte <b>{{ $confirmed->count() }}</b> su {{ $tournament->teams_max }}</span>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.tournaments.index') }}">
            @include('admin.partials.ui-icon', ['name' => 'arrow-90deg-left', 'size' => 16])
            <span>Torna all'elenco</span>
        </a>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.tournaments.edit', $tournament) }}">
            @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
            <span>Modifica</span>
        </a>
    </div>
</header>

@if ($tournament->cover_url)
    <img class="ui-cover" src="{{ $tournament->cover_url }}" alt="Copertina di {{ $tournament->name }}">
@endif

<div class="ui-facts">
    <div class="ui-fact ui-fact--lead">
        <span>Squadre iscritte</span>
        <strong>{{ $confirmed->count() }}/{{ $tournament->teams_max }}</strong>
        <small>{{ $waitlist->count() }} in lista d'attesa · {{ $quota }}% dei posti</small>
    </div>
    <div class="ui-fact">
        <span>Formula</span>
        <strong>{{ $tournament->formatLabel() }}</strong>
        <small>{{ $tournament->is_pair ? 'A coppie' : 'Singolo' }} · {{ $tournament->levelLabel() }}</small>
    </div>
    <div class="ui-fact">
        <span>Quota</span>
        <strong>{{ $tournament->price ? number_format((float) $tournament->price, 2, ',', '.').' €' : 'Gratuito' }}</strong>
        <small>Si salda in struttura</small>
    </div>
    <div class="ui-fact">
        <span>Campi impegnati</span>
        <strong>{{ $tournament->fieldsLabel() }}</strong>
        <small>{{ count($tournament->occupiedFields()) }} su cui non si prenota altro</small>
    </div>
    <div class="ui-fact">
        <span>Iscrizioni</span>
        <strong>{{ $tournament->registration_opens_at?->format('d/m H:i') ?: 'Sempre' }} → {{ $tournament->registration_closes_at?->format('d/m H:i') ?: 'inizio' }}</strong>
        <small>{{ $tournament->location ?: 'Luogo non indicato' }}</small>
    </div>
    <div class="ui-fact">
        <span>Fine</span>
        <strong>{{ $tournament->ends_at?->locale('it')->translatedFormat('D j M, H:i') ?: '—' }}</strong>
        <small>/tornei/{{ $tournament->slug }}</small>
    </div>
</div>

@if ($tournament->note)
    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Note interne</h2></div>
        <p>{{ $tournament->note }}</p>
    </section>
@endif

{{-- ============ Iscritti ============ --}}
<section class="ui-section">
    <div class="ui-section__head">
        <h2>Iscritti</h2>
        <div class="ui-section__meta">
            <span class="ui-pill ui-pill--accent">{{ $confirmed->count() }} confermate</span>
            @if ($pending->count())  <span class="ui-pill ui-pill--warn">{{ $pending->count() }} da confermare</span> @endif
            @if ($waitlist->count()) <span class="ui-pill">{{ $waitlist->count() }} in attesa</span> @endif
        </div>
    </div>

    @if ($registrations->count())
        <div class="ui-list" role="table" aria-label="Iscrizioni al torneo"
             style="--ui-cols: minmax(0, 2fr) 150px minmax(0, 1.4fr);">
            <div class="ui-list__head" role="row">
                <span role="columnheader">Squadra</span>
                <span role="columnheader">Stato</span>
                <span role="columnheader">Azioni</span>
            </div>

            @foreach ($registrations as $r)
                <article class="ui-row {{ $r->status === 'rejected' ? 'ui-row--muted' : '' }}" role="row">
                    <div class="ui-name" role="cell">
                        <span class="ui-name__title">{{ $r->displayName() }}</span>
                        <div class="ui-name__meta">
                            <span class="ui-code">#{{ $r->player?->nickname }} liv {{ $r->player?->level }}</span>
                            @if ($r->partner)
                                <span class="ui-code">#{{ $r->partner->nickname }} liv {{ $r->partner->level }}</span>
                            @endif
                            <span>iscritta il {{ $r->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <div class="ui-cell" data-label="Stato" role="cell">
                        <strong><span class="ui-pill {{ $r->status === 'confirmed' ? 'ui-pill--accent' : ($r->status === 'pending' ? 'ui-pill--warn' : '') }}">{{ $r->statusLabel() }}</span></strong>
                        <span>{{ $r->paid ? 'Pagata' : 'Da pagare' }}</span>
                    </div>

                    <div class="ui-actions" role="cell">
                        @foreach (['confirmed' => 'Conferma', 'waitlist' => 'In attesa', 'rejected' => 'Rifiuta'] as $status => $label)
                            @continue($r->status === $status)
                            <form action="{{ route('admin.tournaments.registrations.status', ['tournament' => $tournament, 'registration' => $r]) }}" method="post">
                                @csrf
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button class="ui-action {{ $status === 'rejected' ? 'ui-action--danger' : '' }}" type="submit">{{ $label }}</button>
                            </form>
                        @endforeach

                        <form action="{{ route('admin.tournaments.registrations.paid', ['tournament' => $tournament, 'registration' => $r]) }}" method="post">
                            @csrf
                            <input type="hidden" name="paid" value="{{ $r->paid ? 0 : 1 }}">
                            <button class="ui-action" type="submit">{{ $r->paid ? 'Segna non pagata' : 'Segna pagata' }}</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="ui-empty">
            <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'people-fill', 'size' => 25])</span>
            <h2>Nessuna iscrizione ricevuta</h2>
            <p>Le iscrizioni arrivano dal sito quando il torneo è in "Iscrizioni aperte". Qui sotto puoi aggiungerne una a mano.</p>
        </div>
    @endif

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Aggiungi un'iscrizione</h2></div>
        <form action="{{ route('admin.tournaments.registrations.store', $tournament) }}" method="post">
            @csrf
            <div class="ui-fields ui-fields--2">
                <div class="ui-field">
                    <label for="player_id">Giocatore <b>*</b></label>
                    <select name="player_id" id="player_id" required>
                        <option value="">Scegli un giocatore</option>
                        @foreach ($all_players as $p)
                            <option value="{{ $p->id }}">#{{ $p->nickname }} — {{ $p->name }} {{ $p->surname }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($tournament->is_pair)
                    <div class="ui-field">
                        <label for="partner_player_id">Compagno</label>
                        <select name="partner_player_id" id="partner_player_id">
                            <option value="">Scegli il compagno</option>
                            @foreach ($all_players as $p)
                                <option value="{{ $p->id }}">#{{ $p->nickname }} — {{ $p->name }} {{ $p->surname }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="ui-field">
                    <label for="team_name">Nome squadra</label>
                    <input type="text" name="team_name" id="team_name" placeholder="Facoltativo">
                </div>
            </div>
            <div>
                <button class="ui-btn" type="submit">
                    @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                    <span>Aggiungi iscrizione</span>
                </button>
            </div>
        </form>
    </section>
</section>

{{-- ============ Calendario incontri ============ --}}
<section class="ui-section">
    <div class="ui-section__head">
        <h2>Calendario incontri</h2>
        <div class="ui-section__meta"><span class="ui-pill">{{ $tournament->matches->count() }} in programma</span></div>
    </div>

    @if ($tournament->matches->count())
        <div class="ui-list" role="table" aria-label="Incontri del torneo"
             style="--ui-cols: minmax(0, 2.2fr) 140px 170px;">
            <div class="ui-list__head" role="row">
                <span role="columnheader">Incontro</span>
                <span role="columnheader">Risultato</span>
                <span role="columnheader">Quando</span>
            </div>

            @foreach ($tournament->matches as $m)
                <article class="ui-row {{ $m->status === 'cancelled' ? 'ui-row--muted' : '' }}" role="row">
                    <div class="ui-name" role="cell">
                        <span class="ui-name__title">
                            {{ $m->teamA?->displayName() ?: 'Da definire' }}
                            <span style="opacity:.5">vs</span>
                            {{ $m->teamB?->displayName() ?: 'Da definire' }}
                        </span>
                        <div class="ui-name__meta">
                            <span>{{ $m->round ?: 'Senza round' }}</span>
                            @if ($m->group_name)<span>Girone {{ $m->group_name }}</span>@endif
                            @if ($m->reservation)<span class="ui-code">campo {{ $m->reservation->field }}</span>@endif
                        </div>
                    </div>

                    <div class="ui-cell" data-label="Risultato" role="cell">
                        <strong>{{ $m->scoreLabel() ?: '—' }}</strong>
                        <span>{{ ['scheduled' => 'Da giocare', 'played' => 'Giocato', 'cancelled' => 'Annullato'][$m->status] ?? $m->status }}</span>
                    </div>

                    <div class="ui-cell" data-label="Quando" role="cell">
                        <strong>{{ $m->played_at?->locale('it')->translatedFormat('D j M') ?: '—' }}</strong>
                        <span>{{ $m->played_at?->format('H:i') }}</span>
                    </div>

                    <details class="ui-inline">
                        <summary>
                            @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 14])
                            Aggiorna incontro
                        </summary>
                        <div class="ui-inline__body">
                            <form action="{{ route('admin.tournaments.matches.update', ['tournament' => $tournament, 'match' => $m]) }}" method="post">
                                @csrf
                                <label class="ui-vh" for="score{{ $m->id }}">Risultato</label>
                                <input type="text" name="score" id="score{{ $m->id }}" value="{{ $m->scoreLabel() }}" placeholder="Risultato: 6-4 6-3">
                                <label class="ui-vh" for="played{{ $m->id }}">Data e ora</label>
                                <input type="datetime-local" name="played_at" id="played{{ $m->id }}" value="{{ optional($m->played_at)->format('Y-m-d\TH:i') }}">
                                <label class="ui-vh" for="res{{ $m->id }}">Slot campo</label>
                                <select name="reservation_id" id="res{{ $m->id }}">
                                    <option value="">Nessuno slot campo</option>
                                    @foreach ($reservations as $res)
                                        <option value="{{ $res->id }}" @selected($m->reservation_id === $res->id)>{{ $res->date_slot }} — {{ $res->field }}</option>
                                    @endforeach
                                </select>
                                <button class="ui-btn" type="submit">Salva</button>
                            </form>

                            <form action="{{ route('admin.tournaments.matches.destroy', ['tournament' => $tournament, 'match' => $m]) }}" method="post"
                                  onsubmit="return confirm('Rimuovere questo incontro?')">
                                @csrf
                                @method('DELETE')
                                <button class="ui-action ui-action--danger" type="submit">Rimuovi incontro</button>
                            </form>
                        </div>
                    </details>
                </article>
            @endforeach
        </div>
    @else
        <div class="ui-empty">
            <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'calendar-check', 'size' => 25])</span>
            <h2>Nessun incontro in calendario</h2>
            <p>Componi il tabellone qui sotto: scegli le due squadre e, se vuoi, lo slot campo su cui si gioca.</p>
        </div>
    @endif

    <section class="ui-panel">
        <div class="ui-panel__head"><h2>Aggiungi un incontro</h2></div>
        <form action="{{ route('admin.tournaments.matches.store', $tournament) }}" method="post">
            @csrf
            <div class="ui-fields ui-fields--2">
                <div class="ui-field">
                    <label for="round">Round</label>
                    <input type="text" name="round" id="round" placeholder="Es. Semifinale">
                </div>
                <div class="ui-field">
                    <label for="group_name">Girone</label>
                    <input type="text" name="group_name" id="group_name" placeholder="Es. A">
                </div>
                <div class="ui-field">
                    <label for="team_a_id">Squadra A</label>
                    <select name="team_a_id" id="team_a_id">
                        <option value="">Da definire</option>
                        @foreach ($teams as $t)
                            <option value="{{ $t->id }}">{{ $t->displayName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ui-field">
                    <label for="team_b_id">Squadra B</label>
                    <select name="team_b_id" id="team_b_id">
                        <option value="">Da definire</option>
                        @foreach ($teams as $t)
                            <option value="{{ $t->id }}">{{ $t->displayName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ui-field">
                    <label for="played_at">Data e ora</label>
                    <input type="datetime-local" name="played_at" id="played_at">
                </div>
                <div class="ui-field">
                    <label for="reservation_id">Slot campo</label>
                    <select name="reservation_id" id="reservation_id">
                        <option value="">Nessuno slot campo</option>
                        @foreach ($reservations as $res)
                            <option value="{{ $res->id }}">{{ $res->date_slot }} — {{ $res->field }}</option>
                        @endforeach
                    </select>
                    <p class="ui-hint">Se scegli uno slot e lasci vuoto l'orario, l'incontro prende quello dello slot.</p>
                </div>
            </div>
            <div>
                <button class="ui-btn" type="submit">
                    @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                    <span>Aggiungi incontro</span>
                </button>
            </div>
        </form>
    </section>
</section>

{{-- ============ Classifica ============ --}}
<section class="ui-section">
    <div class="ui-section__head"><h2>Classifica</h2></div>

    @forelse ($standings as $group)
        <details class="ui-data" @if ($loop->first) open @endif>
            <summary>
                <span>{{ $group['group'] }}</span>
                @include('admin.partials.ui-icon', ['name' => 'chevron-down', 'size' => 16])
            </summary>
            <div class="ui-data__scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Squadra</th>
                            <th>Giocate</th>
                            <th>Vinte</th>
                            <th>Pari</th>
                            <th>Perse</th>
                            <th>Set</th>
                            <th>Game</th>
                            <th>Punti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group['rows'] as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}. {{ $row['team'] }}</td>
                                <td>{{ $row['played'] }}</td>
                                <td>{{ $row['won'] }}</td>
                                <td>{{ $row['drawn'] }}</td>
                                <td>{{ $row['lost'] }}</td>
                                <td>{{ $row['sets_won'] }}-{{ $row['sets_lost'] }}</td>
                                <td>{{ $row['games_won'] }}-{{ $row['games_lost'] }}</td>
                                <td><strong>{{ $row['points'] }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @empty
        <p class="ui-hint">La classifica compare quando inserisci il risultato di almeno un incontro.</p>
    @endforelse
</section>

{{-- ============ Azioni finali ============ --}}
<section class="ui-panel">
    <div class="ui-panel__head"><h2>Altre azioni</h2></div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <form action="{{ route('admin.tournaments.reminder', $tournament) }}" method="post">
            @csrf
            <button class="ui-btn" type="submit">
                @include('admin.partials.ui-icon', ['name' => 'envelope-at', 'size' => 16])
                <span>Invia promemoria alle squadre</span>
            </button>
        </form>
        <button class="ui-btn ui-btn--danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteTournament">
            @include('admin.partials.ui-icon', ['name' => 'trash3-fill', 'size' => 16])
            <span>Elimina torneo</span>
        </button>
    </div>
</section>

<div class="modal fade ui-modal" id="deleteTournament" tabindex="-1" aria-labelledby="deleteTournamentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <h2 id="deleteTournamentLabel" style="font-size:19px;font-weight:700;margin-bottom:10px;">
                    Eliminare "{{ $tournament->name }}"?
                </h2>
                <p class="ui-hint">Spariscono anche tutte le iscrizioni e gli incontri collegati. L'operazione non si annulla.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="ui-btn" data-bs-dismiss="modal">Lascia com'è</button>
                <form action="{{ route('admin.tournaments.destroy', $tournament) }}" method="post">
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
