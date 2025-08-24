@extends('layouts.base')

@section('contents')
@php
$week = [
    'lunedì', 'martedì', 'mercoldì', 'giovedì', 'venerdì', 'sabato', 'domenica'
];
$pack = ['', 'Essentials', 'Work on', 'Boost up', 'Mese prova Gratuita' ];
$services_type = [
    'Asporto e/o domicilio' => 1,
    'prenotazione tavoli' => 2,
    'Asporto e/o domicilio e prenotazione tavoli' => 3,
];
$type_rs = [
    "ristorante","trattoria","pizzeria","sushi","steakhouse",
    "osteria","fast food","braceria","vegan","vegetariano","gourmet","fusion",
    "hamburgeria","pescheria","bistrot","tavola calda","wine bar","pasticceria",
    "gelateria","chiosco","taverna","buffet","self-service","pub", "cucina etnica"];
@endphp
@if (session('success'))
    @php
        $step = session('success')
    @endphp
    <div class="alert notify_success alert-dismissible fade show" role="alert">
        Complimenti <strong>{{Auth::user()->name}}</strong> hai completato la prima parte della registrazione
        <button type="button" class="my_btn_3 mx-auto mt-4" data-bs-dismiss="alert" aria-label="Close">Continua la registrazione</button>
        <button type="button" class="btn-close close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

@endif



<div class="page_nav">

    <div class="client-db ">
        
        <div class="left m-auto">
            <h2 class="title_lg">Dati dell'attività di {{$c->activity_name}}</h2>
            @php 
                if (isset($c->r_property)) {
                    $r_type = json_decode($c->r_property, 1)['r_type'] ;
                }else{
                    $r_type = '';
                }
                if (isset($c->r_property)) {
                    $day_service = json_decode($c->r_property, 1)['day_service'] ;
                }else{
                    $day_service = null;
                }
                if (isset($c->domain)) {
                    $domain = json_decode($c->domain, 1) ;
                }else{
                    $domain = ['domain'=>'', 'type_domain'=> ''];
                }
                if (isset($c->menu)) {
                    $menu = json_decode($c->menu, 1) ;
                }else{
                    $menu = [];
                }
            @endphp
            <div class="anagraphic">
                <div class="top">
                    <h2>{{$c->activity_name}} di {{$user->name}}</h2>
                </div>
                <form class="form-reg form-home" action="{{ route('client.complete_registration') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="step" value="1">
                    <div class="split">
                        <div class="input_form">
                            <label for="type_agency" class="">Tipo di Azienda</label>

                            <div class="select">
                                <input
                                @if ($c->type_agency == 1) checked @endif
                                type="radio" class="btn-check" id="type_agency{{$c->activity_name}}" name="type_agency" value="1" >
                                <label class="my_btn-check" for="type_agency{{$c->activity_name}}">Libero professionista</label>
                                <input
                                @if ($c->type_agency == 2) checked @endif
                                type="radio" class="btn-check" id="type_agency{{$c->activity_name}}1" name="type_agency" value="2" >
                                <label class="my_btn-check" for="type_agency{{$c->activity_name}}1"> Ditta individuale</label>
                                <input
                                @if ($c->type_agency == 3) checked @endif
                                type="radio" class="btn-check" id="type_agency{{$c->activity_name}}2" name="type_agency" value="3" >
                                <label class="my_btn-check" for="type_agency{{$c->activity_name}}2">Azienda</label>
    
                            </div>
                            
                        </div>
                        <div class="input_form">
                            <label for="vat" class="">P. iva</label>
                            <input
                                type="text"
                                id="vat"
                                name="vat"
                                value="{{old('vat', $c->vat)}}"
                                required
                                placeholder="123456789012"
                                autocomplete="vat"
                                value="{{ old('vat') }}"
                            >
                        </div>
                        @error('type_agency') <p class="error w-100">{{ $message }}</p> @enderror
                        @error('vat') <p class="error w-100">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_form long">
                        <label for="address" class="">Sede legale dell'Attività</label>
                        <input
                            type="text"
                            id="address"
                            name="address"
                            required
                            value="{{old('address', $c->address)}}"
                            placeholder="Via, numero civico, città, provincia"
                            autocomplete="address"
                            value="{{ old('address') }}"
                        >
                        @error('address') <p class="error w-100">{{ $message }}</p> @enderror
                    </div>
                    <div class="split">
                        <div class="input_form">
                            <div class="input_form">
                                <label for="owner_phone" class="">Telefono proprietario*</label>
                            <input
                                type="text"
                                id="owner_phone"
                                name="owner_phone"
                                required
                                value="{{old('owner_phone', $c->owner_phone)}}"
                                placeholder="+39 1234567890"
                                autocomplete="owner_phone"
                                value="{{ old('owner_phone', Auth::user()->phone) }}"
                            >
                            </div>
                        </div>
                        <div class="input_form">
                            <label for="pec" class="">Email (preferibilmente pec azienda)</label>
                            <input
                                type="text"
                                id="pec"
                                name="pec"
                                value="{{old('pec', $c->pec)}}"
                                required
                                placeholder="azienda@pec.it"
                                autocomplete="pec"
                                value="{{ old('pec') }}"
                            >
                        </div>
                        
                        @error('owner_phone') <p class="error w-100">{{ $message }}</p> @enderror
                        @error('pec') <p class="error w-100">{{ $message }}</p> @enderror
                    </div>
                    <div class="split">
                        <div class="input_form">
                            <label for="owner_name" class="">Nome proprietario*</label>
                            <input
                                type="text"
                                id="owner_name"
                                name="owner_name"
                                value="{{old('owner_name', $c->owner_name)}}"
                                required
                                placeholder="Nome proprietario"
                                autocomplete="owner_name"
                            >
                            
                        </div>
                        <div class="input_form">
                            <label for="owner_surname" class="">Cognome proprietario*</label>
                            <input
                                type="text"
                                id="owner_surname"
                                name="owner_surname"
                                required
                                placeholder="Cognome proprietario"
                                autocomplete="owner_surname"
                                value="{{ old('owner_surname', $c->owner_surname) }}"
                            >
                        </div>
                        @error('owner_name') <p class="error w-100">{{ $message }}</p> @enderror
                        @error('owner_surname') <p class="error w-100">{{ $message }}</p> @enderror
                    </div>
                    <div class="input_form long">
                        <label for="owner_cf" class="">Codice fiscale proprietario*</label>
                        <input
                            type="text"
                            id="owner_cf"
                            name="owner_cf"
                            required
                            placeholder="ACDSD1212D34DS"
                            autocomplete="owner_cf"
                            value="{{ old('owner_cf', $c->owner_cf) }}"
                        >
                        @error('owner_cf') <p class="error w-100">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="my_btn_1 d-none">Modifica</button>
                </form>
                <form class="form-reg form-home" action="{{ route('client.complete_registration') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="step" value="2">
                    <div class="split">
                        <div class="input_form">
                            <label for="r_type" class="">Tipo di locale</label>
                            <select name="r_type" id="">
                                
                                <option disabled selected class="disabled" value="">Segli il tipo di locale</option>
                                @foreach ($type_rs as $item)
                                <option 
                                @if (old('r_type', $r_type) == $item ) selected @endif
                                 value="{{$item}}">{{$item}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input_form">
                            <label for="services_type" class="">Servizi del locale</label>
                            <select name="services_type" id="">
                                <option disabled selected class="disabled" value="{{$item}}">Seleziona i servizi del locale</option>
                                @foreach ($services_type as $key => $item)
                                <option
                                @if (old('services_type', $c->services_type ) == $item ) selected @endif
                                value="{{$item}}">{{$key}}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('r_type') <p class="error w-100">Seleziona un tipo di locale</p> @enderror
                        @error('services_type') <p class="error w-100">Seleziona i servizi che svolgi nel tuo locale</p> @enderror
                    </div> 
                    <div class="domain">
                        <h3>Hai già un altro sito web per questo locale?</h3>

                        <div class="checkbox-wrapper-35">
                            <input
                            @if ($domain['type_domain']) checked @endif
                             name="type_domain" id="switch" type="checkbox" class="switch">
                            <label for="switch">
                                <span class="switch-x-text">Attualmente </span>
                                <span class="switch-x-toggletext">
                                    <span class="switch-x-unchecked"><span class="switch-x-hiddenlabel">Unchecked: </span>HO GIÀ</span>
                                    <span class="switch-x-checked"><span class="switch-x-hiddenlabel">Checked: </span>NON HO</span>
                                </span>
                                <span class="switch-x-text">un sito web </span>
                            </label>
                        </div>
                        <div class="input_form">
                            <label for="domain" class="old">Inserisci il tuo sito web o il dominio che vorresti avere</label>
                            <input
                                type="text"
                                id="domain"
                                name="domain"
                                placeholder="Https:// dominio. it"
                                autocomplete="domain"
                                value="{{ old('domain', $domain['domain']) }}"
                            >
                        </div>
                        @error('domain') <p class="error w-100">{{ $message }}</p> @enderror
                    </div>
                    <div class="times">
                        <h3> Inserisci gli attuali orari di aperura</h3>
                        <div class="day-block-cont">
                            @foreach ($week as $day)
                            
                            <div class="day-block">
                                <input type="hidden" name="day_service[{{ $day }}]" value="{{ $day }}">
                                <label class="day" for="day{{$day}}">{{ $day }}</label>
                                @php 
                                if(old('day_service', $day_service) !== null) {
                                    $value =  old('day_service', $day_service)[$day] ;
                                } else {
                                    $value = '';
                                }
                                @endphp
                                
                                <div class="input-set">
                                    <input type="text" 
                                    value="{{$value}}"
                                    id="day{{$day}}" 
                                    name="day_service[{{ $day }}]" 
                                    placeholder="Es. 08:00 - 14:00 / 18:00 - 23:00">
                                    <label class="check-close-label
                                    @if ($value == 'Chiuso') dis  @endif
                                    " for="close{{$day . $c->id}}">Chiuso</label>
                                    <input
                                    @if ($value == 'Chiuso') checked @endif
                                    class="check-close" id="close{{$day . $c->id}}" type="checkbox" onchange="toggleTimeInput(this)">
                                </div>
                            </div>
                            @php $errorKey = 'day_service.' . $day; @endphp
    

                            @error($errorKey) <p class="error w-100">Selezionare un orario per {{ $day }}</p> @enderror
                            @endforeach
                        </div>
                    </div>
                    
                    @error('day_service') <p class="error w-100">{{ $message }}</p> @enderror
                    <div class="menu">
                        <h3>Il tuo menu</h3>
                        
                        <p class="w-100">
                            Ha caricato {{count($menu)}} file del tuo menu
                        </p>
                        @if (count($menu))
                            @foreach ($menu as $m)
                                <p>{{$m}}</p>
                            @endforeach
                        @endif
                        <div class="container_file" > 
                            <div class="header_file dropzone"> 
                              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C19.2091 10 21 11.7909 21 14C21 15.4806 20.1956 16.8084 19 17.5M7 10C4.79086 10 3 11.7909 3 14C3 15.4806 3.8044 16.8084 5 17.5M7 10C7.43285 10 7.84965 10.0688 8.24006 10.1959M12 12V21M12 12L15 15M12 12L9 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                                <p class="d-inline d-md-none" >Carica i tuoi file</p>
                                <p class="d-none d-md-inline" >Trascina i tuoi file</p>  
                            </div> 
                        <label for="fileInput2" class="footer_file"> 
                              <svg fill="#000000" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M15.331 6H8.5v20h15V14.154h-8.169z"></path><path d="M18.153 6h-.009v5.342H23.5v-.002z"></path></g></svg> 
                              <p class="filename" >Nessun file selezionato</p> 
                              <div></div>
                            </label> 
                            <input class="fileInput-input" id="fileInput2" type="file" multiple name="menu[]"> 
                          </div>
                    </div>
                    @error('menu') <p class="error w-100">{{ $message }}</p> @enderror
                    @error('menu*') <p class="error w-100">{{ $message }}</p> @enderror
    
                    <button type="submit" class="my_btn_1 d-none">Conferma</button>
    
                </form>
            </div>

        </div>
    </div>
</div>


    






<script>

    document.addEventListener("DOMContentLoaded", function() {
        const modal_1 = document.querySelector('#modal1');
        const modal_2 = document.querySelector('#modal2');
        const close_modal_1 = document.querySelector('#close_modal1');
        const close_modal_2 = document.querySelector('#close_modal2');

        if (close_modal_1) {
            close_modal_1.addEventListener('click', function() {
                close_modal(modal_1);
            });
        }

        if (close_modal_2) {
            close_modal_2.addEventListener('click', function() {
                close_modal(modal_2);
            });
        }

        function close_modal(modal) {
            if (modal) {
                modal.classList.add('d-none');
            }
        }

        const forms = document.querySelectorAll('.form-home');
        
        forms.forEach(form => {
            const modifyButton = form.querySelector('button[type="submit"]');
            const initialFormState = new FormData(form);
            form.addEventListener('input', function() {
                const currentFormState = new FormData(form);

                let formChanged = false;
                
                for (let [key, value] of initialFormState.entries()) {
                    if (currentFormState.get(key) !== value) {
                        // console.log(currentFormState.get(key));
                        console.log(value);
                        formChanged = true;
                        break;
                    }
                }
                console.log(formChanged);

                if (formChanged) {
                    modifyButton.classList.remove('d-none');
                    console.log(modifyButton.classList);
                }else {
                    modifyButton.classList.add('d-none');
                }
            });
        });


        window.toggleTimeInput = function(checkbox) {
            let input = checkbox.closest('.input-set').querySelector('input[name*="day_service"]');
            let label = checkbox.closest('.day-block').querySelector('.check-close-label');
            console.log(label);
            console.log(input);
            if (checkbox.checked) {
                input.value = 'Chiuso';
                label.classList.add('dis');
                input.readOnly = true;
                //console.log(label.classList);
            } else {
                label.classList.remove('dis');
                input.readOnly = false;
                //console.log(label.classList);
                input.value = '';
            }

        }
        const fileInput = document.querySelectorAll(".fileInput-input");

        
        fileInput.forEach(fileInput => {
            let filename = fileInput.closest('.container_file').querySelector(".filename");
            let dropzone = fileInput.closest('.container_file').querySelector(".dropzone");
            console.log(dropzone);
            dropzone.addEventListener("click", () => fileInput.click())
            fileInput.addEventListener("change", (event) => {
                event.preventDefault();
                const files = event.target.files;
                console.log(files);
                updateFileList(files, filename);
            });
        
            dropzone.addEventListener("dragover", (event) => {
                event.preventDefault();
            });
        
            dropzone.addEventListener("drop", (event) => {
                event.preventDefault();
                
                const files = event.dataTransfer.files;
                updateFileList(files, filename);
        
                // Assegna i file droppati all'input file
                const dataTransfer = new DataTransfer();
                for (let i = 0; i < files.length; i++) {
                    dataTransfer.items.add(files[i]);
                }
                fileInput.files = dataTransfer.files; 
            });
        });

        function updateFileList(files, filename) {
            filename.innerHTML = ""; 
            for (let i = 0; i < files.length; i++) {
                filename.innerHTML += ` ${files[i].name} `;
            }
        }



    });
</script>





@endsection
