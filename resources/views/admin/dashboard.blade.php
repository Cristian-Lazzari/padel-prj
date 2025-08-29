@extends('layouts.base')

@section('contents')
@php
    $pack = ['NON attivo', 'Essentials * 360', 'Work on * 360', 'Boost up * 360', 'Essentials * 30', 'Work on * 30', 'Boost up * 30' ];
    $role = ['admin' => 'Amministratore', 'trainer' => 'Istruttore'];
@endphp
<div class="page_nav">


        <a class="my_btn_3 my-4 mx-auto" href="{{route('admin.mailer.index')}}"> 
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-arrow-up" viewBox="0 0 16 16">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4.5a.5.5 0 0 1-1 0V5.383l-7 4.2-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h5.5a.5.5 0 0 1 0 1H2a2 2 0 0 1-2-1.99zm1 7.105 4.708-2.897L1 5.383zM1 4v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1"/>
                <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.354-5.354 1.25 1.25a.5.5 0 0 1-.708.708L13 12.207V14a.5.5 0 0 1-1 0v-1.717l-.28.305a.5.5 0 0 1-.737-.676l1.149-1.25a.5.5 0 0 1 .722-.016"/>
                </svg> 
            <span>PROMOZIONI</span>
        </a>

    <div class="mydash">
        <div class="tables">
            <div class="my-table">
                <div class="head">
                    <p>Giocatore</p>
                    <p class="">Nick-Name</p>
                    <p class="">Livello</p>
                    <a class="my_btn_3" href="{{route('admin.players.create')}}"> 
                       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z"/>
                        </svg> CREA
                    </a>
                </div>
                <div class="body">
                    @foreach ($players as $c)
                        <div class="client">
                           
                            <p class="name">
                              {{$c->name}} {{$c->surname}}
                            </p>
                            <p class="pack ">{{$c->nickname}}</p>
                            <p class="pack ">{{$c->level}}</p>
                            <div class="actions">

                                <a class="my_btn_1" href="{{route('admin.players.show', $c->id)}}"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                    </svg>
                                </a>
                                <a class="my_btn_1" href="{{route('admin.players.edit', $c->id)}}"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                    </svg>
                                </a>
                                <form action="{{route('admin.players.destroy', $c->id)}}" method="post">
                                    @method('delete')
                                    @csrf
                                    <button type="submit" class="my_btn_2"><svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                        <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/>
                                    </svg></button>
                                </form>
                            </div>
                              
                           
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="my-table user">
                <div class="head" >
                    <p>Collaboratore</p>
                    <p class="">Ruolo</p>
                    <p>Info</p>
                </div>
                <div class="body">
                    @foreach ($users as $c)
                        <div class="client">
                            <p class="name">{{$c->name}}</span></p>
                            <p class="date ">{{$role[$c->role]}}</p>
                            <p class="link">
                                <a class="dt" href="{{route('admin.players.show', $c->id)}}"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                        <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                    </svg>
                                </a>
                            </p>
                        </div>
                    @endforeach
          
                </div>
            </div>
            <div class="my-table">
                <div class="head" >
                    <p>Data</p>
                    <p>campo</p>
                    <p>Cena</p>
                    <p class="">Giocatori</p>
                </div>
                <div class="body">
                    
          
                </div>
            </div>
        </div>
       
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
                                <input type="radio" name="status_service"  @if($settings['Servizio di Prenotazione Online']['status'] == 1) checked  @endif value="1" >
                                <span class="name">Chiamate</span>
                            </label>
                            <label class="radio">
                                <input type="radio" name="status_service"  @if($settings['Servizio di Prenotazione Online']['status'] == 2) checked  @endif value="2" >
                                <span class="name">Web App</span>
                            </label>
                        </div>
                        @php
                            $property_adv = json_decode($settings['advanced']['property'], true);
                        @endphp
                        <div class="input-group my-3">
                            <label class="input-group-text" id="max_delay_defalt">Minimo ore per cancellazione</label>
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
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <div class="accordion-item">
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                            Giorni e orari d'apertura
                            </button>
                        </h4>
                        <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                @php 
                                    $property_orari = json_decode($settings['Orari di attività']['property'], true);
                                    $property_contatti = json_decode($settings['Contatti']['property'], true);
                                @endphp
                                <section class="activity-day">
                                    @foreach (['lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'] as $giorno)
                                        <div class="input-group mb-3">
                                            <label class="input-group-text">{{ $giorno }}</label>
                                            <input id="{{$giorno}}" type="time" class="form-control"  name="{{ $giorno }}_from"  @if($property_orari) value="{{ $property_orari[$giorno]['from'] }}" @endif>
                                            <input id="{{$giorno}}" type="time" class="form-control"  name="{{ $giorno }}_to"  @if($property_orari) value="{{ $property_orari[$giorno]['to'] }}" @endif>
                                        </div>
                                    @endforeach
                                </section>
                               
                            </div>
                        </div>
                    </div>
                   

                    <div class="accordion-item"> 
                        <h4 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
                                Contatti e Social
                            </button>
                        </h4>
                        <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                                <section>
                                    <div class="input-group mb-3">
                                        <label for="telefono" class="input-group-text">Telefono</label>
                                        <input type="text" class="form-control"  name="telefono" @if($property_contatti) value="{{ $property_contatti['telefono'] }}" @endif>
                                    </div>
                                    <div class="input-group mb-3">
                                        <label for="email" class="input-group-text">Email</label>
                                        <input type="text" class="form-control"  name="email" @if($property_contatti) value="{{ $property_contatti['email'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
                                        <label for="instagram" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-instagram" viewBox="0 0 16 16">
                                                <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di instagram" name="instagram" @if(isset($property_contatti['instagram'])) value="{{ $property_contatti['instagram'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
                                        <label for="facebook" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-facebook" viewBox="0 0 16 16">
                                                <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di facebook" name="facebook" @if(isset($property_contatti['facebook'])) value="{{ $property_contatti['facebook'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
                                        <label for="tiktok" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tiktok" viewBox="0 0 16 16">
                                                <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di tiktok" name="tiktok" @if(isset($property_contatti['tiktok'])) value="{{ $property_contatti['tiktok'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
                                        <label for="youtube" class="input-group-text">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-youtube" viewBox="0 0 16 16">
                                                <path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.01 2.01 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.01 2.01 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31 31 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.01 2.01 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A100 100 0 0 1 7.858 2zM6.4 5.209v4.818l4.157-2.408z"/>
                                            </svg>
                                        </label>
                                        <input type="text" class="form-control"  placeholder="Link di youtube" name="youtube" @if(isset($property_contatti['youtube'])) value="{{ $property_contatti['youtube'] }}" @endif>
                                    </div>        
                                    <div class="input-group mb-3">
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
                   
                </div>
            </div>

            <button type="submit" class="my_btn_1 my_btn_1 w-75 m-auto">Aggiorna</button> 

        </form>
    </div>
</div>



@endsection

