@extends('layouts.base')

@section('contents')
    
<div class="page_nav">
    <div class="view_box pt-5">
        <div class="central">
            <h1>Dettagli di #<span>{{$player->nickname}}</span></h1>
        </div>
        <div class="box_container">
            <div class="box contact ">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg"  fill="currentColor" class="bi bi-envelope-at-fill" viewBox="0 0 16 16">
                        <path d="M2 2A2 2 0 0 0 .05 3.555L8 8.414l7.95-4.859A2 2 0 0 0 14 2zm-2 9.8V4.698l5.803 3.546zm6.761-2.97-6.57 4.026A2 2 0 0 0 2 14h6.256A4.5 4.5 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586zM16 9.671V4.697l-5.803 3.546.338.208A4.5 4.5 0 0 1 12.5 8c1.414 0 2.675.652 3.5 1.671"/>
                        <path d="M15.834 12.244c0 1.168-.577 2.025-1.587 2.025-.503 0-1.002-.228-1.12-.648h-.043c-.118.416-.543.643-1.015.643-.77 0-1.259-.542-1.259-1.434v-.529c0-.844.481-1.4 1.26-1.4.585 0 .87.333.953.63h.03v-.568h.905v2.19c0 .272.18.42.411.42.315 0 .639-.415.639-1.39v-.118c0-1.277-.95-2.326-2.484-2.326h-.04c-1.582 0-2.64 1.067-2.64 2.724v.157c0 1.867 1.237 2.654 2.57 2.654h.045c.507 0 .935-.07 1.18-.18v.731c-.219.1-.643.175-1.237.175h-.044C10.438 16 9 14.82 9 12.646v-.214C9 10.36 10.421 9 12.485 9h.035c2.12 0 3.314 1.43 3.314 3.034zm-4.04.21v.227c0 .586.227.8.581.8.31 0 .564-.17.564-.743v-.367c0-.516-.275-.708-.572-.708-.346 0-.573.245-.573.791"/>
                    </svg>
                    <span>{{$player->mail}}</span>
                </div>
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg"  fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                    </svg>
                    <a href="tel:{{$player->phone}}">{{$player->phone}}</a>
                </div>
                
            </div>
            <div class="box personal">
                <p>
                    <strong>Nome</strong> <span>{{$player->name}}</span>
                </p>
                <p>
                    <strong>Cognome</strong> <span>{{$player->surname}}</span>
                </p>
            </div>
            
            <div class="box doc">
                <div>
                    <strong>Certificato</strong> 
                    @if ($player->certificate)
                        <a href="{{Storage::url($player->certificate)}}" target="_blank" rel="noopener noreferrer">Visualizza certificato</a>
                    @else
                        <span>Nessun documento caricato</span>
                    @endif
                </div>
            
                <div>
                    <strong>Note</strong> 
                    <p>
                        @if ($player->note)
                            {{$player->note}}
                        @else
                            Nessuna nota
                        @endif
                    </p>
                </div>
            </div>
            <div class="box level">
                <strong>Livello</strong>
                <div class="donut-wrapper" style="--percent: {{ $player->level / 5 * 100}}">
                    <p>
                        {{ $player->level }}
                    </p>
                </div>
            </div>
        </div>
        @if(count($player_reservations))  
            <h2>Match prenotati da <strong> #{{$player->nickname}}</strong> </h2>
        @else
            <h2> <strong> #{{$player->nickname}}</strong> non ha prenotato ancora a nessun match</h2>
        @endif
        <div class="newtable w-100">
            @foreach ($player_reservations as $r)
            @php
                // Parsing della stringa
                $datetime = Carbon\Carbon::parse($r->date_slot)->locale('it');

                // Variabili separate
                $data = $datetime->translatedFormat('D j M'); // es: giovedì 25 settembre
                $ora = $datetime->format('H:i');              // es: 18:00
                $ora_fine = $datetime->addHour()->addMinutes(30)->format('H:i'); // es: 19:00
            @endphp
                <div class="res_item"
                    onclick="window.location='{{ route('admin.reservations.show', $r) }}'"
                >
                    <div class="left">
                        <div class="time_slot">
                            {{$ora}}   
                        <span class="second">{{$ora_fine}}
                        </span>
                        
                    </div>
                    <div class="date">{{$data}}</div>
                    </div>
                    <div class="center">
                        <section>
                            @if ($r->message)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-left-text-fill" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4.414a1 1 0 0 0-.707.293L.854 15.146A.5.5 0 0 1 0 14.793zm3.5 1a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1zm0 2.5a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1zm0 2.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z"/></svg>                                
                            @endif
                            @if (count($r->players))
                                <span class="pl">
                                    {{ count($r->players) }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                    </svg>
                                </span>
                            @endif
                            <span class="field">{{ $r->field }}</span>
                            <div class="lesson_type">
                                @if ($r->lesson)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M192 144C222.9 144 248 118.9 248 88C248 57.1 222.9 32 192 32C161.1 32 136 57.1 136 88C136 118.9 161.1 144 192 144zM176 576L176 416C176 407.2 183.2 400 192 400C200.8 400 208 407.2 208 416L208 576C208 593.7 222.3 608 240 608C257.7 608 272 593.7 272 576L272 240L400 240C417.7 240 432 225.7 432 208C432 190.3 417.7 176 400 176L384 176L384 128L576 128L576 320L384 320L384 288L320 288L320 336C320 362.5 341.5 384 368 384L592 384C618.5 384 640 362.5 640 336L640 112C640 85.5 618.5 64 592 64L368 64C341.5 64 320 85.5 320 112L320 176L197.3 176C151.7 176 108.8 197.6 81.7 234.2L14.3 324.9C3.8 339.1 6.7 359.1 20.9 369.7C35.1 380.3 55.1 377.3 65.7 363.1L112 300.7L112 576C112 593.7 126.3 608 144 608C161.7 608 176 593.7 176 576z"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M161 191L228.4 123.6C266.6 85.4 318.4 64 372.4 64C484.9 64 576.1 155.2 576.1 267.6C576.1 314 560.3 358.7 531.6 394.6C508 377.8 479.2 367.9 448.1 367.9C417 367.9 388.2 377.8 364.7 394.5L161 191zM304 512C304 521.7 305 531.1 306.8 540.2C287 535 268.8 524.7 254.1 510C241.9 497.8 222.2 497.8 210 510L160.6 559.4C150 570 135.6 576 120.6 576C89.4 576 64 550.7 64 519.4C64 504.4 70 490 80.6 479.4L130 430C142.2 417.8 142.2 398.1 130 385.9C108.3 364.2 96.1 334.7 96.1 304C96.1 274.6 107.2 246.4 127.2 225L330.6 428.6C313.9 452.1 304 480.9 304 512zM448 416C501 416 544 459 544 512C544 565 501 608 448 608C395 608 352 565 352 512C352 459 395 416 448 416z"/></svg>
                                @endif
                            </div>
                        </section>
                    </div>
                    <div class="actions">
                        @if ($dinner_off)
                            @php
                                $dinner = json_decode($r->dinner, true);
                            @endphp
                            @if ($dinner['status'])
                                <!-- Button trigger modal -->
                                <button onclick="event.stopPropagation()"  type="button" data-bs-toggle="modal" data-bs-target="#exampleModal{{$r->id}}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
                                        <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
                                    </svg>
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal{{$r->id}}" tabindex="-1" aria-labelledby="exampleModal{{$r->id}}Label" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <button onclick="event.stopPropagation()"  type="button" class="btn_close" data-bs-dismiss="modal">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708z"/>
                                                    </svg>
                                                </button>
                                                <p>
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                                                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                                                    </svg>
                                                    <span>{{$dinner['guests']}}</span>
                                                </p>
                                                <p>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                                                        <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
                                                        <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                                                        <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                                                    </svg>
                                                    <span>{{ $dinner['time'] }}</span>
                                                </p>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div onclick="event.stopPropagation()"  class="no_dinner">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
                                        <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
                                    </svg>
                                </div>
                            @endif
                        @endif
                        <a href="{{route('admin.reservations.edit', $r)}}" class="edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>
                        </a>
                        
                    </div>

                </div>
            @endforeach

                
        </div>
        @if(count($player->reservations))
            <h2>Match in cui <strong> #{{$player->nickname}}</strong> ha giocato</h2>
        @else
            <h2> <strong> #{{$player->nickname}}</strong> non ha giocato ancora a nessun match</h2>
        @endif
        <div class="newtable w-100">
            @foreach ($player->reservations as $r)
            @php
                // Parsing della stringa
                $datetime = Carbon\Carbon::parse($r->date_slot)->locale('it');

                // Variabili separate
                $data = $datetime->translatedFormat('D j M'); // es: giovedì 25 settembre
                $ora = $datetime->format('H:i');              // es: 18:00
                $ora_fine = $datetime->addHour()->addMinutes(30)->format('H:i'); // es: 19:00
            @endphp
                <div class="res_item"
                    onclick="window.location='{{ route('admin.reservations.show', $r) }}'"
                >
                    <div class="left">
                        <div class="time_slot">
                            {{ $ora }} <span class="second">{{ $ora_fine }}</span>
                        </div>
                        <div class="date">{{ $data }}</div>
                    </div>
                    <div class="center">
                        <section>
                            @if ($r->message)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-left-text-fill" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4.414a1 1 0 0 0-.707.293L.854 15.146A.5.5 0 0 1 0 14.793zm3.5 1a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1zm0 2.5a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1zm0 2.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z"/></svg>                                
                            @endif
                            @if (count($r->players))
                                <span class="pl">
                                    {{ count($r->players) }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                    </svg>
                                </span>
                            @endif
                            <span class="field">{{ $r->field }}</span>
                            <div class="lesson_type">
                                @if ($r->lesson)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M192 144C222.9 144 248 118.9 248 88C248 57.1 222.9 32 192 32C161.1 32 136 57.1 136 88C136 118.9 161.1 144 192 144zM176 576L176 416C176 407.2 183.2 400 192 400C200.8 400 208 407.2 208 416L208 576C208 593.7 222.3 608 240 608C257.7 608 272 593.7 272 576L272 240L400 240C417.7 240 432 225.7 432 208C432 190.3 417.7 176 400 176L384 176L384 128L576 128L576 320L384 320L384 288L320 288L320 336C320 362.5 341.5 384 368 384L592 384C618.5 384 640 362.5 640 336L640 112C640 85.5 618.5 64 592 64L368 64C341.5 64 320 85.5 320 112L320 176L197.3 176C151.7 176 108.8 197.6 81.7 234.2L14.3 324.9C3.8 339.1 6.7 359.1 20.9 369.7C35.1 380.3 55.1 377.3 65.7 363.1L112 300.7L112 576C112 593.7 126.3 608 144 608C161.7 608 176 593.7 176 576z"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M161 191L228.4 123.6C266.6 85.4 318.4 64 372.4 64C484.9 64 576.1 155.2 576.1 267.6C576.1 314 560.3 358.7 531.6 394.6C508 377.8 479.2 367.9 448.1 367.9C417 367.9 388.2 377.8 364.7 394.5L161 191zM304 512C304 521.7 305 531.1 306.8 540.2C287 535 268.8 524.7 254.1 510C241.9 497.8 222.2 497.8 210 510L160.6 559.4C150 570 135.6 576 120.6 576C89.4 576 64 550.7 64 519.4C64 504.4 70 490 80.6 479.4L130 430C142.2 417.8 142.2 398.1 130 385.9C108.3 364.2 96.1 334.7 96.1 304C96.1 274.6 107.2 246.4 127.2 225L330.6 428.6C313.9 452.1 304 480.9 304 512zM448 416C501 416 544 459 544 512C544 565 501 608 448 608C395 608 352 565 352 512C352 459 395 416 448 416z"/></svg>
                                @endif
                            </div>
                        </section>
                    </div>
                    <div class="actions">
                        @php
                            $dinner = json_decode($r->dinner, true);
                        @endphp
                        @if ($dinner['status'])
                            <!-- Button trigger modal -->
                            <button onclick="event.stopPropagation()" type="button" data-bs-toggle="modal" data-bs-target="#exampleModal{{$r->id}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
                                    <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
                                </svg>
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="exampleModal{{$r->id}}" tabindex="-1" aria-labelledby="exampleModal{{$r->id}}Label" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-body">
                                            <button onclick="event.stopPropagation()" type="button" class="btn_close" data-bs-dismiss="modal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708z"/>
                                                </svg>
                                            </button>
                                            <p>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                                                    <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                                                </svg>
                                                <span>{{$dinner['guests']}}</span>
                                            </p>
                                            <p>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                                                    <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
                                                    <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                                                    <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                                                </svg>
                                                <span>{{ $dinner['time'] }}</span>
                                            </p>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div onclick="event.stopPropagation()" class="no_dinner">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
                                    <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
                                </svg>
                            </div>
                        @endif
                        <a href="{{route('admin.reservations.edit', $r)}}" class="edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                            </svg>
                        </a>
                    </div>

                </div>
            @endforeach

                
        </div>

        <div class="more_info my-3" >
            <p>
                <strong>Creato il</strong> {{$player->created_at->format('d/m/Y')}},
                <strong>Aggiornato il</strong> {{$player->updated_at->format('d/m/Y')}}
            </p>
        </div>

        <div class="action_page">
            <a class="my_btn_7" href="{{ route('admin.players.edit', $player) }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                </svg>
                Modifica
            </a>
            <button class="my_btn_2 btn_delete" type="button" data-bs-toggle="modal" data-bs-target="#exampleModaldelete">
                Elimina
            </button>

                
        </div>
        <!-- Modal -->
        <div class="modal fade" id="exampleModaldelete" tabindex="-1" aria-labelledby="exampleModaldeleteLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="btn_close mb-3" data-bs-dismiss="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-90deg-left" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708z"/>
                            </svg>
                        </button>
                        <h3>Sei sicuro di voler eliminare il giocatore "{{$player->name}}" ?</h3>
                        <form class="w-100" action="{{ route('admin.players.destroy', ['player'=>$player]) }}" method="post" >
                            @method('delete')
                            @csrf
                            <button class="my_btn_1 btn_delete m-auto" type="submit">Elimina</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
    </div>
</div>