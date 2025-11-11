@extends('layouts.base')

@section('contents')
@php
    $pack = ['NON attivo', 'Essentials * 360', 'Work on * 360', 'Boost up * 360', 'Essentials * 30', 'Work on * 30', 'Boost up * 30' ];
    $role = ['admin' => 'Amministratore', 'trainer' => 'Istruttore'];
@endphp
<div class="page_nav">
    <div class="mydash">
        <form class="setting" action="{{ route('admin.settings.updateAll')}}" method="POST" enctype="multipart/form-data">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
                </svg>
            Impostazioni</h2>
            
            <div class="top-set">
                @csrf
                <div class="set">
                    <h4>Servizio</h4>
                    <div class="set-cont">
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="status_service"  @if($settings['Servizio di Prenotazione Online']['status'] == 0) checked  @endif value="0" >
                                <span class="name">Off</span>
                            </label>

                            <label class="radio">
                                <input type="radio" name="status_service"  @if($settings['Servizio di Prenotazione Online']['status'] == 2) checked  @endif value="2" >
                                <span class="name">On</span>
                            </label>
                        </div>
                        @php
                            $property_adv = json_decode($settings['advanced']['property'], true);
                        @endphp
                        <div class="input-group my-3">
                            <label class="input-group-text" id="max_delay_defalt">Min. H per cancellazione</label>
                            <input type="number" class="form-control"  name="max_delay_defalt" value="{{$property_adv['max_delay_default']}}">
                        </div>
                    </div>

                </div>
         

                @php
                    $settings['Periodo di Ferie']['property'] = json_decode($settings['Periodo di Ferie']['property'], true);
                @endphp
                <div class="set">
                    <h4>Ferie</h4>
                    <div class="sets">
                        <div class="radio-inputs">
                            <label class="radio">
                                <input type="radio" name="ferie_status"  @if($settings['Periodo di Ferie']['status'] == 0) checked  @endif value="0" >
                                <span class="name">A lavoro</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="ferie_status"  @if($settings['Periodo di Ferie']['status'] == 1) checked  @endif value="1" >
                                <span class="name">In ferie</span>
                            </label>
                        </div>
                        
                        <div class="input-group flex-nowrap">
                            <label for="form" class="input-group-text" >Da</label>
                            <input name="from" id="form" type="date" class="form-control" placeholder="da" @if($settings['Periodo di Ferie']['property']['from'] !== '') value="{{$settings['Periodo di Ferie']['property']['from']}}"  @endif>
                            <label for="to" class="input-group-text" >A</label>
                            <input name="to" id="to" type="date" class="form-control" placeholder="da" @if($settings['Periodo di Ferie']['property']['to'] !== '') value="{{$settings['Periodo di Ferie']['property']['to']}}"  @endif>
                        </div>
                    </div>
                </div>
                

                    
            </div>
            <div class="bottom-set">
                @php 
                    $property_contatti = json_decode($settings['Contatti']['property'], true);
                    $field_set = json_decode($settings['advanced']['property'], true)['field_set'];
                @endphp
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <div class="accordion-item"> 
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                Contatti e Social
                            </button>
                        </h4>
                        <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <section>
                                    <div class="input-group">
                                        <label for="phone" class="input-group-text">Telefono</label>
                                        <input type="text" class="form-control"  name="phone" @if($property_contatti) value="{{ $property_contatti['phone'] }}" @endif>
                                    </div>
                                    <div class="input-group">
                                        <label for="email" class="input-group-text">Email</label>
                                        <input type="text" class="form-control"  name="email" @if($property_contatti) value="{{ $property_contatti['email'] }}" @endif>
                                    </div>        
                                    <div class="input-group">
                                        <label for="instagram" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
                                                <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di instagram" name="instagram" @if(isset($property_contatti['instagram'])) value="{{ $property_contatti['instagram'] }}" @endif>
                                    </div>        
                                    <div class="input-group">
                                        <label for="facebook" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                                                <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di facebook" name="facebook" @if(isset($property_contatti['facebook'])) value="{{ $property_contatti['facebook'] }}" @endif>
                                    </div>        
                                    <div class="input-group">
                                        <label for="tiktok" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tiktok" viewBox="0 0 16 16">
                                                <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di tiktok" name="tiktok" @if(isset($property_contatti['tiktok'])) value="{{ $property_contatti['tiktok'] }}" @endif>
                                    </div>        
                                    <div class="input-group">
                                        <label for="youtube" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-youtube" viewBox="0 0 16 16">
                                                <path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di youtube" name="youtube" @if(isset($property_contatti['youtube'])) value="{{ $property_contatti['youtube'] }}" @endif>
                                    </div>              
                                    <div class="input-group">
                                        <label for="whatsapp" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control" placeholder="+39001110000"  name="whatsapp" @if(isset($property_contatti['whatsapp'])) value="{{ $property_contatti['whatsapp'] }}" @endif>
                                    </div>        
                                </section>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item"> 
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseField" aria-expanded="false" aria-controls="flush-collapseField">
                                Impostazioni campi
                            </button>
                        </h4>
                        <div id="flush-collapseField" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body cont_field">
                                <div class="cont">
                                    @foreach ($field_set as $k => $f) 
                                        <section class="">
                                            <div class="top">
                                                <div class="name_field">{{$k}} - {{$f['type']}}</div>
                                                <div class="btn-group dropup">
                                                    <button type="button" class="my_btn_1" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                                            <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                                        </svg>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <div class="body">
                                                            <div class="btn delete">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill " viewBox="0 0 16 16">
                                                                    <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="field_set[{{$k}}][name_field]" value="{{$k}}">
                                            <input type="hidden" name="field_set[{{$k}}][type]" value="{{$f['type']}}">
                                            <div class="my_input_group">
                                                <label for="{{$f['h_start']}}" class="input-group-text">Apertura</label>
                                                <input type="text" id="{{$f['h_start']}}" class="form-control" name="field_set[{{$k}}][h_start]" value="{{$f['h_start']}}">
                                            </div>
                                            <div class="my_input_group">
                                                <label for="{{$f['m_during']}}" class="input-group-text">Durata minima</label>
                                                <input type="text" id="{{$f['m_during']}}" class="form-control" name="field_set[{{$k}}][m_during]" value="{{$f['m_during']}}">
                                            </div>
                                            <div class="my_input_group">
                                                <label for="{{$f['m_during_client']}}" class="input-group-text">Durata slot cliente</label>
                                                <input type="text" id="{{$f['m_during_client']}}" class="form-control" name="field_set[{{$k}}][m_during_client]" value="{{$f['m_during_client']}}">
                                            </div>
                                            <div class="my_input_group">
                                                <label for="{{$f['n_slot']}}" class="input-group-text">N slot</label>
                                                <input type="text" id="{{$f['n_slot']}}" class="form-control" name="field_set[{{$k}}][n_slot]" value="{{$f['n_slot']}}">
                                            </div>
                                            <h3 >Giorni di chiusura</h3>
                                            <div class="btn-group" role="group" aria-label="Basic checkbox toggle button group">
                                                @php
                                                    $week = [
                                                        'Lunedì' => 1,
                                                        'Martedì' => 2,
                                                        'Mercoledì' => 3,
                                                        'Giovedì' => 4,
                                                        'Venerdì' => 5,
                                                        'Sabato' => 6,
                                                        'Domenica' => 7,
                                                    ]
                                                @endphp     
                                                @foreach ($week as $kw => $v) 
                                                    <input type="checkbox" class="btn-check"
                                                        name="field_set[{{$k}}][closed_days][]" id="btncheck{{$k}}_{{$v}}_{{$kw}}"
                                                        value="{{$v}}"
                                                        autocomplete="off" 
                                                        @if(in_array($v, $f['closed_days'])) checked @endif
                                                        >
                                                    <label class="btn btn-outline-danger" for="btncheck{{$k}}_{{$v}}_{{$kw}}">{{$kw}}</label>
                                                @endforeach
                                            </div>
                                        </section>
                                    @endforeach
                                </div>
                                <div class="my_btn_7 my-2 " id="addFieldBtn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
                                    </svg>
                                    Nuovo campo</div>  
                                <div class="cont" id="container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <button type="submit" class="my_btn_1  w-75 m-auto">Aggiorna</button> 

        </form>
    </div>
</div>


<script>

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('container');
    const addBtn = document.getElementById('addFieldBtn');
    let counter = 0;

    addBtn.addEventListener('click', () => {
        counter++;

        // Crea la section principale
        let counter_name = 'NewField_' + counter;

        const section = document.createElement('section');


        // Funzione per creare un gruppo label + input
        const createInputGroup = (labelText, name, type, placeholder = '') => {
            const id = `${name}_${counter}`;

            const div = document.createElement('div');
            div.classList.add('my_input_group');

            const label = document.createElement('label');
            label.setAttribute('for', id);
            label.classList.add('input-group-text');
            label.textContent = labelText;

            const input = document.createElement('input');
            input.type = type;
            input.name = `field_set[${counter_name}][${name}]`;
            input.id = id;
            input.placeholder = placeholder;
            input.classList.add('form-control');

            div.append(label, input);
            return div;
        };
        // Funzione per creare un gruppo label + select
        const createSelectGroup = (labelText, name, options, selectedValue) => {
            const id = `${name}_${counter}`;

            const div = document.createElement('div');
            div.classList.add('my_input_group');

            const label = document.createElement('label');
            label.setAttribute('for', id);
            label.classList.add('input-group-text');
            label.textContent = labelText;

            const select = document.createElement('select');
            select.name = `field_set[${counter_name}][${name}]`;
            select.classList.add('form-control');
            select.id = id;

            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.toLowerCase();
                option.textContent = opt;
                if (opt.toLowerCase() === selectedValue.toLowerCase()) {
                    option.selected = true;
                }
                select.appendChild(option);
            });

            div.append(label, select);
            return div;
        };
 // ✅ Funzione per creare il gruppo dei checkbox "giorni chiusura"
        const createDaysGroup = () => {
            const days = {
                'Lunedì': 1,
                'Martedì': 2,
                'Mercoledì': 3,
                'Giovedì': 4,
                'Venerdì': 5,
                'Sabato': 6,
                'Domenica': 7,
            };

            const div = document.createElement('div');
            div.classList.add('btn-group', 'mt-2');
            div.setAttribute('role', 'group');
            div.setAttribute('aria-label', 'Giorni di chiusura');

            Object.entries(days).forEach(([dayName, dayValue]) => {
                const input = document.createElement('input');
                input.type = 'checkbox';
                input.classList.add('btn-check');
                input.name = `field_set[${counter_name}][closed_days][]`;
                input.id = `btncheck_${counter}_${dayValue}_${dayName}`;
                input.value = dayValue;

                const label = document.createElement('label');
                label.classList.add('btn', 'btn-outline-danger');
                label.setAttribute('for', `btncheck_${counter}_${dayValue}_${dayName}`);
                label.textContent = dayName;

                div.append(input, label);
            });

            return div;
        };
        // Crea i tre campi
        const campoGroup = createInputGroup('Campo', 'name_field', 'text', 'Inserisci nome campo');
        const h_start = createInputGroup('Apertura', 'h_start', 'time', '');
        const durataGroup = createInputGroup('Durata minima', 'm_during', 'number', 'Tempo in minuti');
        const durataClientGroup = createInputGroup('Durata slot cliente', 'm_during_client', 'number', 'Tempo in minuti');
        const numeroGroup = createInputGroup('N° slot', 'n_slot', 'number', '0');
        const sportGroup = createSelectGroup('Sport', 'type', ['Padel', 'Calcio', 'Tennis', 'Basket'], 'Padel');
        const daysGroup = createDaysGroup(); // ✅ nuovo gruppo di checkbox

        // Bottone rimuovi
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
        </svg>`;
        removeBtn.classList.add('my_btn_4', 'null', 'delete');
        removeBtn.addEventListener('click', () => section.remove());
        
        // //duplica
        // const duplicateBtn = document.createElement('button');
        // duplicateBtn.type = 'button';
        // duplicateBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-copy" viewBox="0 0 16 16">
        //     <path fill-rule="evenodd" d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"/>
        // </svg>`;
        // duplicateBtn.classList.add('my_btn_4', 'duplicate-btn');
        // duplicateBtn.addEventListener('click', () => {
        //     // Clona l'intera sezione
        //     const clone = section.cloneNode(true);

        //     // Rimuovi il vecchio bottone di duplicazione dal clone
        //     const oldDuplicate = clone.querySelector('.duplicate-btn');
        //     if (oldDuplicate) oldDuplicate.remove();

        //     const inputs = clone.querySelectorAll('input, select, label');
        //     inputs.forEach(el => {
        //         if (el.id) el.id = el.id.replace(/_\d+$/, `_${counter}`);
                
        //         if (el.name) {
        //             // Trova e sostituisce solo il primo valore tra ['...']
        //             el.name = el.name.replace(/\['([^']+)'\]/, `['campo${counter}']`);
        //         }

        //         if (el.htmlFor) el.htmlFor = el.htmlFor.replace(/_\d+$/, `_${counter}`);
        //     });


        //     const removeBtnClone = clone.querySelector('.delete');
        //     if (removeBtnClone) {
        //         removeBtnClone.addEventListener('click', () => clone.remove());
        //     }

        //     const cont_act = document.createElement('div');
        //     cont_act.classList.add('cont_act');
        //     cont_act.append(removeBtnClone );
        //     clone.append(cont_act);

        //     // Inserisce il clone subito dopo la sezione originale
        //     section.insertAdjacentElement('afterend', clone);
        // });

                
        const cont_act = document.createElement('div');
        cont_act.classList.add('cont_act');
        cont_act.append(removeBtn)
        // Aggiunge tutto alla section
        section.append(
            campoGroup,
            sportGroup,
            h_start,
            durataGroup,
            durataClientGroup,
            numeroGroup,
            daysGroup,
            cont_act,
        );

        // Inserisce la section nel container
        container.appendChild(section);
    });

    const buttons_delete = document.querySelectorAll('.delete');

    buttons_delete.forEach(button => {
        button.addEventListener('click', function() {
            // Trova la sezione genitore più vicina
            const section = this.closest('section');

            // Effetto dissolvenza
            section.style.transition = 'opacity 0.3s ease';
            section.style.opacity = '0';

            setTimeout(() => {
                section.remove(); // rimuove completamente dal DOM
            }, 400);
        });
    });

    
});
</script>

@endsection

