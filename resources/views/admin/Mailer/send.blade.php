
@extends('layouts.base')

@section('contents')
@if (session('send_success'))
    @php
        $data = session('send_success')
    @endphp
    <div class="alert alert-primary">
        {{ $data }}
    </div>
@endif





<h1 class="my-4">Invia Mail</h1>

<form class="creation email-m"  action="{{ route('admin.mailer.send_m') }}"  method="POST"  >
    @csrf
        <section class="check_c">
            <h3 class="mb-4">Scegli la lista di destinatari</h3>        
            <label class="label_c" for="type">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks-grid" viewBox="0 0 16 16">
                <path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z"/>
                </svg>
                Liste
            </label>

            <p class="mail-list-check">
                <input type="checkbox" name="recipients[]" class="btn-check" id="3" value="3">
                <label class="btn btn-outline-light" for="3">
                    <span>Contatti aggiunti manualmente</span>
                    <span>{{$n_c[0]}}</span>
                </label>

                <input type="checkbox" name="recipients[]" class="btn-check" id="4" value="4">
                <label class="btn btn-outline-light" for="4">
                    <span>Ultima lista Contatti</span>
                    <span>{{$n_c[1]}}</span>
                </label>
            </p>
            
            
            @error('recipients') <p class="error"> {{ $message }}</p> @enderror
        </section>

        <section class="check_c">
            <h3 class="mb-4">Scegli un modello da inviare</h3>        
            <label class="label_c" for="type">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ui-checks-grid" viewBox="0 0 16 16">
                <path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1m9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1m0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zm0-10a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zM2 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h3a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2zm7 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2zM0 2a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.354.854a.5.5 0 1 0-.708-.708L3 3.793l-.646-.647a.5.5 0 1 0-.708.708l1 1a.5.5 0 0 0 .708 0z"/>
                </svg>
                Modelli Mail
            </label>

            <div class="models">
                @foreach ($models as $m)
                    
                    <div class="model">
                        <input type="radio" name="models[]" class="btn-check" id="{{$m->id}}m"  value="{{$m->id}}">
                        <label class="btn btn-outline-dark m-auto" for="{{$m->id}}m">{{$m->name}}</label>

                       
            
                        <h1>{{$m->heading}}</h1>
                    
                        @if($m->img_1 !== NULL)   
                        <img src="{{ asset('public/storage/' . $m->img_1) }}" alt="">
                        @endif
                        
                        <span>...</span>     
                    </div>
                @endforeach
            </div>
             
            @error('models') <p class="error"> {{ $message }}</p> @enderror
        </section>

       



    <button class="my_btn_2 mb-5  w-75 m-auto" type="submit">invia mail</button>

</form>



@endsection
<script>
    document.addEventListener('DOMContentLoaded', async function() {
        document.getElementById('addEmailsButton').addEventListener('click', function() {
            const emailInput = document.getElementById('emailInput');
            const emailList = document.getElementById('emailList');
            const emails = emailInput.value.split(/[ ,]+/).filter(Boolean); // Dividi per spazio o virgola e rimuovi stringhe vuote

            const existingEmails = Array.from(emailList.querySelectorAll('input[name="recipients[]"]')).map(input => input.value);

            emails.forEach(email => {
                if (validateEmail(email)) {
                    // Controlla se l'email esiste già nella lista
                    if (!existingEmails.includes(email)) {
                        const uniqueId = `email-${Math.random().toString(36).substr(2, 9)}`; // Genera un ID univoco

                        const wrapper = document.createElement('div');
                        wrapper.className = 'd-flex gap-2 wrapper';

                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.checked = true;
                        input.className = 'd-none';
                        input.id = uniqueId;
                        input.name = 'recipients[]';
                        input.value = email;

                        const label = document.createElement('label');
                        //label.className = 'btn btn-outline-light ';
                        label.setAttribute('for', uniqueId);
                        label.textContent = email;

                        const removeButton = document.createElement('button');
                        removeButton.className = 'btn btn-outline-light btn-sm';
                        removeButton.innerHTML = 'Rimuovi'; // Icona cestino
                        removeButton.addEventListener('click', () => {
                            emailList.removeChild(wrapper);
                        });

                        wrapper.appendChild(input);
                        wrapper.appendChild(label);
                        wrapper.appendChild(removeButton);
                        emailList.appendChild(wrapper);
                    } else {
                        alert(`L'email "${email}" è già nella lista!`);
                    }
                } else {
                    alert(`L'email "${email}" non è valida!`);
                }
            });

            emailInput.value = ''; // Svuota l'input
        });

        // Funzione per validare l'email
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    })
</script>
<style>
    .wrapper{
        flex: 1 !important;
        border-radius: 10px;
        background-color: rgba(0, 0, 0, 0.225);
        padding: .6rem .8rem;
        max-width: 100%;
        flex-wrap: wrap
    }
    .wrapper label{
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        background-color: rgba(255, 0, 0, 0) !important;
    }
</style>

