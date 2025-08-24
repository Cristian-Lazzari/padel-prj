
@extends('layouts.base')

@section('contents')
@if (session('create_success'))
    @php
        $data = session('create_success')
    @endphp
    <div class="alert w-75 m-auto alert-primary">
        {{ $data }}
    </div>
@endif
@if (session('send_success'))
    @php
        $data = session('send_success')
    @endphp
    <div class="alert w-75 m-auto alert-success">
        {{ $data }}
    </div>
@endif
@if (session('extra'))
    @php
        $data = session('extra')
    @endphp
    <div class="alert w-75 m-auto alert-info">
        {{ $data }}
    </div>
@endif
{{-- compact('models', 'last_mail_list', 'extra_mail_list', 'users', 'order_users', 'reservation_users'));    --}}

<div class="email-m pt-5">
    
    <h1 class="mt-5 ">Email Marketing</h1>
    <section class="lists">
        <h2>Le tue liste di contatti</h2>
        <div class="list_wrap">
            <h3>Contatti extra</h3>
            <div class="list">
                @foreach ($extra_mail_list as $i)
                    <div class="contact">
                        <span class="name">{{$i->name}}</span>
                        <span class="mail">{{$i->email}}</span>
                    </div>
                @endforeach
            </div>
            <div class="params act">
                <p>
                    <span>{{count($extra_mail_list)}} contatti</span>
                </p>
                <button type="button" class="my_btn_1" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                    Modifica
                </button>
            </div>
        </div>
        <div class="list_wrap">
            <h3>Contattati nell'ultima mail</h3>
            <div class="list">
                @foreach ($last_mail_list as $i)
                    <div class="contact">
                        <span class="name">{{$i->name}}</span>
                        <span class="mail">{{$i->email}}</span>
                    </div>
                @endforeach
            </div>
            <div class="params act">
                <p>
                    <span>{{count($last_mail_list)}} contatti</span>
                </p>
            </div>
        </div>
    </section>
    
    <section>
        <h2>Modelli per Email</h2>
        <a class="my_btn_2 m-2" href="{{route('admin.mailer.create_model')}}"> Crea un nuovo modello </a>
        <div class="models">
            @foreach ($models as $m)  
                <div class="model">
                    <div class="name my_btn_4 mb-4">{{$m['name']}}</div>
        
                    <h1>{{$m['heading']}}</h1>
                
                    @if($m['img_1'] !== NULL)   
                    <img src="{{ asset('public/storage/' . $m['img_1']) }}" alt="">
                    @endif
                    
                    <div class="corpo">
                        @foreach (explode("/*/", $m['body']) as $b)
                        
                        {{-- <p>{!! Str::markdown($b) !!}</p> --}}
                        <p>{!! nl2br(e(str_replace('\n', "\n", $b))) !!}</p>
                        @endforeach
                    </div>
                    
                    @if($m['img_2'] !== NULL)   
                        <img src="{{ asset('public/storage/' . $m['img_2']) }}" alt="">
                    @endif
        
                    <p class="ending">{!! nl2br(e(str_replace('\n', "\n", $m['ending']))) !!}</p>
        
                    <div class="sender" style="color: #04001d">
                        <p>{{$m['sender']}}</p>
                        <p class="date">martedi 3 gennaio</p>
                    </div>
                    <div class="act">
                        <a class="my_btn_1" href="{{route('admin.mailer.edit_model',  $m)}}">
                            <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>
                        </a>
                        <button type="button" class="my_btn_2" data-bs-toggle="modal" data-bs-target="#staticBackdrop{{$m->id}}">
                            <svg style="vertical-align: sub" xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                            </svg>
                        </button>
                        
                    </div>
                </div>
                  <!-- Modal -->
                <div class="modal fade" id="staticBackdrop{{$m->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdrop{{$m->id}}Label" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered creation">
                        <form action="{{ route('admin.models.delete', $m['id']) }}" class="creation modal-content" method="post" >
                            @method('delete')
                            @csrf
                            <section>
                                <div class="split">
                                    <h1 class="modal-title fs-2" id="staticBackdrop{{$m->id}}Label">Sicuro di voler eliminare il modello?</h1>
                                    <button type="button" class="btn-close my_btn_2" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <p>Il modello: " {{$m->name}}" non potrà piu essere recuperato una volta eliminato.</p>
                                <button class="my_btn_2" type="submit">
                                    Eliminina definitivamente
                                </button>
                            </section>

                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    <a class="my_btn_6 m-auto w-50 m-2" href="{{route('admin.mailer.send_mail')}}"> Avvia Campagna </a>

      
      <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog creation">

            <form class="creation modal-content"  action="{{ route('admin.mailer.extra_list') }}" method="POST">
                @csrf
                <section >

                    <div class="split">
                        <h1 class="modal-title fs-2" id="staticBackdropLabel">Aggiorna manualmente la lista</h1>
                        <button type="button" class="btn-close my_btn_2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="list" id="emailList">
                        @foreach ($extra_mail_list as $i)
                            <div class="wrappercontact">
                                <input name="recipients[]" id="{{$i->email}}old" class="btn-check" type="text" value="{{json_encode($i)}}">
                                <label class="contact" for="{{$i->email}}old">
                                    <span class="name">{{$i->name}}</span>
                                    <span class="mail">{{$i->email}}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="split">
                        <div>
                            <label class="label_c" for="sender">Mail e Nome *</label>   
                            <input value="{{ old('name') }}" type="text"  id="emailInput" class="w-100" placeholder="email@mail.it nome, email1@mail.it nome2, ">
                                @error('name') <p class="error">{{ $message }}</p> @enderror
            
                        </div>
                        <div class="my_btn_3" id="addEmailsButton">Aggiungi</div>
                    </div>
    
                </section>
                <section>
                    <button type="sumbit" class="my_btn_5">Aggiorna lista</button>
                </section>

            </form>
        </div>
    </div>

      
    
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const emailList1 = document.getElementById('emailList');
    const mail_old = Array.from(emailList1.querySelectorAll('.wrappercontact'));

    mail_old.forEach(e => {
        const removeButton = document.createElement('button');
        removeButton.className = 'btn-close my_btn_2';
        
        removeButton.addEventListener('click', () => {
            emailList1.removeChild(e);
        });
        e.appendChild(removeButton);
    });
    document.getElementById('addEmailsButton').addEventListener('click', function() {
        const emailInput = document.getElementById('emailInput');
        const emailList = document.getElementById('emailList');
        const entries = emailInput.value.split(/[ ,]+(?=[^ ,]*@)/).filter(Boolean); // Divide mantenendo mail e nome

        const existingEmails = Array.from(emailList.querySelectorAll('input[name="recipients[]"]')).map(input => JSON.parse(input.value).email);

        const mail_old = Array.from(emailList.querySelectorAll('wrappercontact'));

        mail_old.forEach(e => {
            const removeButton = document.createElement('button');
            removeButton.className = 'btn-close my_btn_2';
            
            removeButton.addEventListener('click', () => {
                emailList.removeChild(e);
            });
            e.appendChild(removeButton);
        });

        console.log(existingEmails)
        entries.forEach(entry => {
            const parts = entry.split(' ');
            const email = parts[0];
            const name = parts.slice(1).join(' ') || 'Senza Nome'; // Default se manca il nome
            if(name !== 'Senza Nome'){
                if (validateEmail(email)) {
                    if (!existingEmails.includes(email)) {
                        const uniqueId = `email-${Math.random().toString(36).substr(2, 9)}`;

                        const wrapper = document.createElement('div');
                        wrapper.className = 'wrappercontact';

                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.checked = true;
                        input.className = 'd-none';
                        input.id = uniqueId;
                        input.name = 'recipients[]';
                        input.value = JSON.stringify({ email: email, name: name });


                        const label = document.createElement('label');
                        label.className = 'contact';
                        //label.setAttribute('for', uniqueId);
                        //label.textContent = `${name} <${email}>`;
                            
                        const span = document.createElement('span');
                        span.className = 'name';
                        span.textContent = `${name}`;

                        const span1 = document.createElement('span');
                        span1.className = 'mail';
                        span1.textContent = `${email}`;
                        label.appendChild(span);
                        label.appendChild(span1);

                        const removeButton = document.createElement('button');
                        removeButton.className = 'btn-close my_btn_2';
                        removeButton.addEventListener('click', () => {
                            emailList.removeChild(wrapper);
                        });

                        wrapper.appendChild(input);
                        wrapper.appendChild(label);
                        wrapper.appendChild(removeButton);
                        emailList.appendChild(wrapper);
                    } else {
                        showAlert('Email gia presente nella lista', 'danger', 8000);
                    }
                } else {
                    showAlert('Email non valida', 'danger', 8000);
                }
            }else{
                showAlert('Per ogni Email inseririre un nome destinatario in questo modo: "mario@mail.it mario, lucia@mail.it lucia"', 'danger', 8000);
            }
        });

        emailInput.value = ''; // Svuota l'input
    });

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    function showAlert(message, type, timeout) {
        // Controlla se esiste già un contenitore per gli alert
        let alertContainer = document.getElementById('alert-container');
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'alert-container';
            alertContainer.style.position = 'fixed';
            alertContainer.style.top = '0';
            alertContainer.style.left = '50%';
            alertContainer.style.transform = 'translateX(-50%)';
            alertContainer.style.width = 'auto';
            alertContainer.style.maxWidth = '90%';
            alertContainer.style.zIndex = '5550'; // Sopra a tutto
            document.body.appendChild(alertContainer);
        }

        // Creazione dell'alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show shadow-lg`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Aggiunge l'alert al contenitore
        alertContainer.appendChild(alertDiv);

        // Rimuove l'alert dopo il timeout (default 5s)
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, timeout);
    }

});
</script>
<style>
    .modal-header h1{
        color: black
    }

</style>
