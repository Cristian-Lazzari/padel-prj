@extends('layouts.ui')

@section('title', 'Comunicazioni - F+')

@section('contents')

<nav class="ui-crumbs" aria-label="Percorso">
    <a href="{{ route('admin.dashboard') }}">Gestionale</a>
    <span class="ui-crumbs__sep" aria-hidden="true">@include('admin.partials.ui-icon', ['name' => 'chevron-right', 'size' => 10])</span>
    <b>Comunicazioni</b>
</nav>

@foreach (['create_success' => '', 'send_success' => '', 'extra' => ''] as $chiave => $x)
    @if (session($chiave))
        <div class="ui-flash" role="alert">
            @include('admin.partials.ui-icon', ['name' => 'check-circle-fill', 'size' => 20])
            <span>{{ session($chiave) }}</span>
            <button type="button" class="ui-flash__close" data-ui-dismiss aria-label="Chiudi avviso">
                @include('admin.partials.ui-icon', ['name' => 'x-lg', 'size' => 14])
            </button>
        </div>
    @endif
@endforeach

<header class="ui-head">
    <div class="ui-head__title">
        <h1>Comunicazioni</h1>
        <div class="ui-head__count">
            <span>Modelli <b>{{ count($models) }}</b></span>
            <span>Contatti extra <b>{{ count($extra_mail_list) }}</b></span>
        </div>
    </div>
    <div class="ui-head__actions">
        <a class="ui-btn" href="{{ route('admin.mailer.create_model') }}">
            @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
            <span>Nuovo modello</span>
        </a>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.mailer.send_mail') }}">
            @include('admin.partials.ui-icon', ['name' => 'envelope-at', 'size' => 16])
            <span>Avvia campagna</span>
        </a>
    </div>
</header>

{{-- ============ Liste di contatti ============ --}}
<section class="ui-section">
    <div class="ui-section__head"><h2>Liste di contatti</h2></div>

    <div class="ui-cards">
        <section class="ui-panel">
            <div class="ui-panel__head">
                <h2>Contatti extra</h2>
                <span class="ui-panel__note">{{ count($extra_mail_list) }}</span>
            </div>
            <div class="mail_contacts">
                @forelse ($extra_mail_list as $i)
                    <span class="mail_contact"><b>{{ $i->name }}</b>{{ $i->email }}</span>
                @empty
                    <p class="ui-hint">Nessun contatto aggiunto a mano.</p>
                @endforelse
            </div>
            <button type="button" class="ui-btn" data-bs-toggle="modal" data-bs-target="#listaExtra">
                @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
                <span>Modifica la lista</span>
            </button>
        </section>

        <section class="ui-panel">
            <div class="ui-panel__head">
                <h2>Contattati nell'ultima mail</h2>
                <span class="ui-panel__note">{{ count($last_mail_list) }}</span>
            </div>
            <div class="mail_contacts">
                @forelse ($last_mail_list as $i)
                    <span class="mail_contact"><b>{{ $i->name }}</b>{{ $i->email }}</span>
                @empty
                    <p class="ui-hint">Nessuna campagna inviata finora.</p>
                @endforelse
            </div>
        </section>
    </div>
</section>

{{-- ============ Modelli ============ --}}
<section class="ui-section">
    <div class="ui-section__head">
        <h2>Modelli di email</h2>
        <div class="ui-section__meta"><span class="ui-pill">{{ count($models) }}</span></div>
    </div>

    @if (count($models))
        <div class="ui-cards">
            @foreach ($models as $m)
                <article class="ui-card">
                    <div class="ui-card__head">
                        <span class="ui-pill ui-pill--accent">{{ $m['name'] }}</span>
                        <h3>{{ $m['heading'] }}</h3>
                    </div>

                    <div class="ui-card__body">
                        @if ($m['img_1'] !== null)
                            <img src="{{ asset('public/storage/'.$m['img_1']) }}" alt="" loading="lazy">
                        @endif
                        @foreach (explode('/*/', $m['body']) as $b)
                            <p>{!! nl2br(e(str_replace('\n', "\n", $b))) !!}</p>
                        @endforeach
                        @if ($m['img_2'] !== null)
                            <img src="{{ asset('public/storage/'.$m['img_2']) }}" alt="" loading="lazy">
                        @endif
                        <p>{!! nl2br(e(str_replace('\n', "\n", $m['ending']))) !!}</p>
                    </div>

                    <div class="ui-card__foot">
                        <span class="ui-code">{{ $m['sender'] }}</span>
                        <div class="ui-actions">
                            <a class="ui-action ui-action--icon" href="{{ route('admin.mailer.edit_model', $m) }}"
                               aria-label="Modifica il modello {{ $m['name'] }}" title="Modifica">
                                @include('admin.partials.ui-icon', ['name' => 'pencil-square', 'size' => 16])
                            </a>
                            <button type="button" class="ui-action ui-action--icon ui-action--danger"
                                    data-bs-toggle="modal" data-bs-target="#eliminaModello{{ $m->id }}"
                                    aria-label="Elimina il modello {{ $m['name'] }}" title="Elimina">
                                @include('admin.partials.ui-icon', ['name' => 'trash3-fill', 'size' => 16])
                            </button>
                        </div>
                    </div>
                </article>

                <div class="modal fade ui-modal" id="eliminaModello{{ $m->id }}" tabindex="-1"
                     aria-labelledby="eliminaModello{{ $m->id }}Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body">
                                <h2 id="eliminaModello{{ $m->id }}Label" style="font-size:19px;font-weight:700;margin-bottom:10px;">
                                    Eliminare il modello "{{ $m->name }}"?
                                </h2>
                                <p class="ui-hint">Una volta eliminato non si recupera.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="ui-btn" data-bs-dismiss="modal">Lascia com'è</button>
                                <form action="{{ route('admin.models.delete', $m['id']) }}" method="post">
                                    @method('delete')
                                    @csrf
                                    <button class="ui-btn ui-btn--danger" type="submit">Elimina</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="ui-empty">
            <span class="ui-empty__icon">@include('admin.partials.ui-icon', ['name' => 'envelope-at', 'size' => 25])</span>
            <h2>Nessun modello di email</h2>
            <p>Un modello è la struttura fissa della mail: intestazione, testo, immagini e firma. Ti serve per avviare una campagna.</p>
            <a class="ui-btn ui-btn--primary" href="{{ route('admin.mailer.create_model') }}">
                @include('admin.partials.ui-icon', ['name' => 'plus-lg', 'size' => 16])
                <span>Crea il primo modello</span>
            </a>
        </div>
    @endif
</section>

{{-- ============ Finestra: lista contatti extra ============ --}}
<div class="modal fade ui-modal" id="listaExtra" data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-labelledby="listaExtraLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="{{ route('admin.mailer.extra_list') }}" method="POST">
            @csrf
            <div class="modal-body">
                <h2 id="listaExtraLabel" style="font-size:19px;font-weight:700;margin-bottom:14px;">Contatti extra</h2>

                {{-- id e classi restano quelli di prima: sono agganci del JS qui sotto --}}
                <div class="list" id="emailList">
                    @foreach ($extra_mail_list as $i)
                        <div class="wrappercontact">
                            <input name="recipients[]" id="{{ $i->email }}old" class="btn-check" type="text" value="{{ json_encode($i) }}">
                            <label class="contact" for="{{ $i->email }}old">
                                <span class="name">{{ $i->name }}</span>
                                <span class="mail">{{ $i->email }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="ui-field" style="margin-top:16px;">
                    <label for="emailInput">Aggiungi contatti</label>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <input type="text" id="emailInput" style="flex:1 1 220px;"
                               placeholder="mario@mail.it Mario, lucia@mail.it Lucia">
                        <button type="button" class="ui-btn" id="addEmailsButton">Aggiungi</button>
                    </div>
                    <p class="ui-hint">Email e nome separati da uno spazio, più contatti separati da virgola.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ui-btn" data-bs-dismiss="modal">Chiudi</button>
                <button type="submit" class="ui-btn ui-btn--primary">Aggiorna lista</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* Contatti come pastiglie: le classi .wrappercontact/.contact/.name/.mail
       sono create anche dal JS in fondo alla pagina, quindi non si rinominano. */
    .mail_contacts, #emailList{
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        max-height: 220px;
        overflow: auto;
        padding: 2px;
    }
    .mail_contact, .ui-page .contact{
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 34px;
        padding: 0 14px;
        border-radius: 999px;
        background: rgba(216, 221, 232, .095);
        font-size: 12.5px;
        color: rgba(216, 221, 232, .62);
        white-space: nowrap;
    }
    .mail_contact b, .ui-page .contact .name{ color: #d8dde8; font-weight: 600; }
    .ui-page .wrappercontact{ display: inline-flex; align-items: center; gap: 4px; }
    .ui-page .wrappercontact input{ display: none; }
    /* Il bottone di rimozione lo crea il JS con le classi di Bootstrap */
    .ui-page .wrappercontact .btn-close{
        width: 26px; height: 26px;
        border: 0;
        border-radius: 999px;
        background: rgba(233, 90, 90, .16);
        color: #ef8181;
        font-size: 13px;
        line-height: 1;
        cursor: pointer;
        opacity: 1;
    }
    .ui-page .wrappercontact .btn-close::before{ content: "×"; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-ui-dismiss]').forEach((b) => {
        b.addEventListener('click', () => b.closest('.ui-flash')?.remove());
    });

    const emailList1 = document.getElementById('emailList');
    if (!emailList1) return;

    Array.from(emailList1.querySelectorAll('.wrappercontact')).forEach((e) => {
        e.appendChild(bottoneRimuovi(emailList1, e));
    });

    document.getElementById('addEmailsButton').addEventListener('click', function () {
        const emailInput = document.getElementById('emailInput');
        const emailList = document.getElementById('emailList');
        // Divide mantenendo insieme mail e nome
        const entries = emailInput.value.split(/[ ,]+(?=[^ ,]*@)/).filter(Boolean);

        const existingEmails = Array.from(emailList.querySelectorAll('input[name="recipients[]"]'))
            .map((input) => JSON.parse(input.value).email);

        entries.forEach((entry) => {
            const parts = entry.trim().split(' ');
            const email = parts[0];
            const name = parts.slice(1).join(' ');

            if (!name) {
                avviso('Per ogni email serve anche il nome: "mario@mail.it Mario, lucia@mail.it Lucia"');
                return;
            }
            if (!validateEmail(email)) {
                avviso('Email non valida: ' + email);
                return;
            }
            if (existingEmails.includes(email)) {
                avviso('Email già presente nella lista: ' + email);
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'wrappercontact';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'recipients[]';
            input.value = JSON.stringify({ email: email, name: name });

            const label = document.createElement('label');
            label.className = 'contact';

            const span = document.createElement('span');
            span.className = 'name';
            span.textContent = name;

            const span1 = document.createElement('span');
            span1.className = 'mail';
            span1.textContent = email;

            label.appendChild(span);
            label.appendChild(span1);
            wrapper.appendChild(input);
            wrapper.appendChild(label);
            wrapper.appendChild(bottoneRimuovi(emailList, wrapper));
            emailList.appendChild(wrapper);
            existingEmails.push(email);
        });

        emailInput.value = '';
    });

    function bottoneRimuovi(lista, riga) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'btn-close';
        b.setAttribute('aria-label', 'Rimuovi contatto');
        b.addEventListener('click', () => lista.removeChild(riga));
        return b;
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Avviso nel linguaggio della pagina, non un alert di Bootstrap
    function avviso(messaggio) {
        let cont = document.getElementById('ui-avvisi');
        if (!cont) {
            cont = document.createElement('div');
            cont.id = 'ui-avvisi';
            cont.style.cssText = 'position:fixed;left:50%;top:16px;transform:translateX(-50%);z-index:5550;display:grid;gap:8px;max-width:92vw;';
            document.body.appendChild(cont);
        }
        // Stili in linea: l'avviso vive fuori da .ui-page, dove i token non arrivano
        const el = document.createElement('div');
        el.setAttribute('role', 'alert');
        el.style.cssText = 'padding:14px 20px;border-radius:20px;background:rgba(233,90,90,.16);color:#d8dde8;font-size:14px;font-weight:600;box-shadow:0 12px 28px rgba(0,0,0,.28);';
        el.textContent = messaggio;
        cont.appendChild(el);
        setTimeout(() => el.remove(), 6000);
    }
});
</script>
@endsection
