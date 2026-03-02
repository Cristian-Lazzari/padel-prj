@extends('layouts.base')

@section('contents')
@php
    $role = ['admin' => 'Amministratore', 'trainer' => 'Istruttore'];
    $i = 0; 
    $currentDay = date("d");
    $currentMonth = date("m");
    $currentYear = date("Y");
@endphp
<div class="page_nav">
    @if (session('message'))
    @php
        $message = session('message');
    @endphp
    <div class="alert-cont">
        <div class="alert alert-dismissible fade show notify_success" role="alert">
            {{$message}}
            <button type="button" class="btn-close close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif
    @if (session('error'))
    @php
        $error = session('error');
    @endphp
    <div class="alert-cont">
        <div class="alert alert-dismissible fade show notify_success error" role="alert">
            {{$error}}
            <button type="button" class="btn-close close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif
    <h1> <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-calendar2-week mx-3" viewBox="0 0 16 16">
            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
            <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5zM11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-5 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
        </svg> CALENDARIO
    </h1>

    <button  type="button" class="ml-auto my_btn_2 mb-4 btn_delete mt-4" data-bs-toggle="modal" data-bs-target="#exampleModal1">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ban" viewBox="0 0 16 16">
            <path d="M15 8a6.97 6.97 0 0 0-1.71-4.584l-9.874 9.875A7 7 0 0 0 15 8M2.71 12.584l9.874-9.875a7 7 0 0 0-9.874 9.874ZM16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0"/>
            </svg>
        Blocca Giorni
    </button>
    <div id="carouselExampleIndicators" class="carousel slide my_carousel" >
        <div class="carousel-indicators">
            @foreach ($year as $m)
                <button  type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{$i}}"
                @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                    class="active" aria-current="true" 
                @endif
                aria-label="{{ 'Slide ' . $i }}"></button>
                @php $i ++ @endphp
            @endforeach
        </div>
        <div class="top_line">
            <button class="prev_btn" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-left-fill" viewBox="0 0 16 16">
                <path d="m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z"/>
                </svg>
            </button>
            <button class="post_btn" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-right-fill" viewBox="0 0 16 16">
                <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/>
                </svg>
            </button>
        </div>
        <div id="calendar" class="carousel-inner">
            @php $i = 0; @endphp
            @foreach ($year as $m)
                <div class="carousel-item @if ($currentMonth == $m['month'] && $currentYear == $m['year']) active @endif">
                    <h2 class="my">{{['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'][$m['month']]}} - {{$m['year']}}</h2>
                    <div class="calendar">
                        <div class="c-name">
                            @php
                            $day_name = ['lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
                            @endphp
                            @foreach ($day_name as $item)
                                <h4>{{$item}}</h4>
                            @endforeach
                        </div>
                        <div class="calendar_page">
                            @foreach ($m['days'] as $d)
                                <button data-day='@json($d)'
                                class="day  
                                @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) current @endif 
                                @if(!$d['status']) day_off @endif " 
                                style="grid-column-start:{{$d['day_w'] }}">        
                                    <p class="p_day">{{$d['day']}}</p>
                                    @if (($d['reserved_match'] + $d['reserved_lesson'] + $d['reserved_tournament'] + $d['reserved_dinner']) > 0)
                                        <div class="bookings">
                                            <div>
                                                @if ($d['reserved_lesson'] > 0)
                                                    {{$d['reserved_lesson']}} 
                                                    <!--!Font Awesome Free v7.1.0 by fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M192 144C222.9 144 248 118.9 248 88C248 57.1 222.9 32 192 32C161.1 32 136 57.1 136 88C136 118.9 161.1 144 192 144zM176 576L176 416C176 407.2 183.2 400 192 400C200.8 400 208 407.2 208 416L208 576C208 593.7 222.3 608 240 608C257.7 608 272 593.7 272 576L272 240L400 240C417.7 240 432 225.7 432 208C432 190.3 417.7 176 400 176L384 176L384 128L576 128L576 320L384 320L384 288L320 288L320 336C320 362.5 341.5 384 368 384L592 384C618.5 384 640 362.5 640 336L640 112C640 85.5 618.5 64 592 64L368 64C341.5 64 320 85.5 320 112L320 176L197.3 176C151.7 176 108.8 197.6 81.7 234.2L14.3 324.9C3.8 339.1 6.7 359.1 20.9 369.7C35.1 380.3 55.1 377.3 65.7 363.1L112 300.7L112 576C112 593.7 126.3 608 144 608C161.7 608 176 593.7 176 576z"/></svg>
                                                @endif
                                            </div>
                                            <div>
                                                @if ($d['reserved_match'] > 0)
                                                    {{$d['reserved_match']}} 
                                                    <!--!Font Awesome Free v7.1.0 by fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M161 191L228.4 123.6C266.6 85.4 318.4 64 372.4 64C484.9 64 576.1 155.2 576.1 267.6C576.1 314 560.3 358.7 531.6 394.6C508 377.8 479.2 367.9 448.1 367.9C417 367.9 388.2 377.8 364.7 394.5L161 191zM304 512C304 521.7 305 531.1 306.8 540.2C287 535 268.8 524.7 254.1 510C241.9 497.8 222.2 497.8 210 510L160.6 559.4C150 570 135.6 576 120.6 576C89.4 576 64 550.7 64 519.4C64 504.4 70 490 80.6 479.4L130 430C142.2 417.8 142.2 398.1 130 385.9C108.3 364.2 96.1 334.7 96.1 304C96.1 274.6 107.2 246.4 127.2 225L330.6 428.6C313.9 452.1 304 480.9 304 512zM448 416C501 416 544 459 544 512C544 565 501 608 448 608C395 608 352 565 352 512C352 459 395 416 448 416z"/></svg>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="bookings top">
                                            <div>
                                                @if ($d['reserved_tournament'] > 0)
                                                    {{$d['reserved_tournament']}} 
                                                    <!--!Font Awesome Free v7.1.0 by fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M208.3 64L432.3 64C458.8 64 480.4 85.8 479.4 112.2C479.2 117.5 479 122.8 478.7 128L528.3 128C554.4 128 577.4 149.6 575.4 177.8C567.9 281.5 514.9 338.5 457.4 368.3C441.6 376.5 425.5 382.6 410.2 387.1C390 415.7 369 430.8 352.3 438.9L352.3 512L416.3 512C434 512 448.3 526.3 448.3 544C448.3 561.7 434 576 416.3 576L224.3 576C206.6 576 192.3 561.7 192.3 544C192.3 526.3 206.6 512 224.3 512L288.3 512L288.3 438.9C272.3 431.2 252.4 416.9 233 390.6C214.6 385.8 194.6 378.5 175.1 367.5C121 337.2 72.2 280.1 65.2 177.6C63.3 149.5 86.2 127.9 112.3 127.9L161.9 127.9C161.6 122.7 161.4 117.5 161.2 112.1C160.2 85.6 181.8 63.9 208.3 63.9zM165.5 176L113.1 176C119.3 260.7 158.2 303.1 198.3 325.6C183.9 288.3 172 239.6 165.5 176zM444 320.8C484.5 297 521.1 254.7 527.3 176L475 176C468.8 236.9 457.6 284.2 444 320.8z"/></svg>
                                                @endif
                                            </div>
                                            <div>
                                                @if ($d['reserved_dinner'] > 0)
                                                    {{$d['reserved_dinner']}} 
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
                                                        <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                @php $i ++ @endphp
            @endforeach
        </div>
    </div>

    

    <form  action="{{ route('admin.reservations.createFromD')}}"   method="POST">
        @csrf
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content  mymodal_make_res">
                    <div class="modal-body box_container">
                        <div class="top">
                            <h3>Completa la prenotazione</h3>
                            <button type="button" class="btn_close" data-bs-dismiss="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="ml-auto bi bi-arrow-90deg-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1.146 4.854a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H12.5A2.5 2.5 0 0 1 15 6.5v8a.5.5 0 0 1-1 0v-8A1.5 1.5 0 0 0 12.5 5H2.707l3.147 3.146a.5.5 0 1 1-.708.708z"/>
                            </svg>
                            </button>
                        </div>
                        <div class="body creation">
                            <div class="box players new_players">
                                <h3 class="second">Aggiungi giocatori</h3>                            
                                <div class="filters">
                                    <div class="bar"> 
                                        <input type="checkbox" class="check" id="f"> 
                                        <div class="box"> 
                                            <input type="text" id="playerSearch" class="search" placeholder="Cerca player..." > 
                                        </div> 

                                        <label for="f"> 
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel-fill" viewBox="0 0 16 16"> 
                                                <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/> 
                                            </svg> 
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16"> 
                                                <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/> 
                                            </svg> 
                                        </label> 
                                    </div> 
                                </div>
                                @foreach ($players as $p)
                                    <input type="checkbox" name="players[]" id="{{$p->id}}" value="{{$p->id}}">
                                    <label 
                                        for="{{$p->id}}"
                                        data-search="{{ strtolower($p->nickname.' '.$p->name.' '.$p->surname) }}"
                                        class="res_item"
                                    >
                                        <div class="left">
                                            <div class="time_slot">#{{$p->nickname}}</div>
                                            <div class="date">{{$p->name}} {{$p->surname}}</div>
                                        </div>
                                        <div class="player_center">

                                            <div class="line">
                                                <a href="{{route('admin.players.show', $p)}}"  class="donut-wrapper" style="--percent: {{ $p->level / 5 * 100}}">
                                                    <p>
                                                        {{ $p->level }}
                                                    </p>
                                                </a>
                                            </div>
                                            <div class="line">
                                                @if ($p->sex == 'm')
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-standing man" viewBox="0 0 16 16"><path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M6 6.75v8.5a.75.75 0 0 0 1.5 0V10.5a.5.5 0 0 1 1 0v4.75a.75.75 0 0 0 1.5 0v-8.5a.25.25 0 1 1 .5 0v2.5a.75.75 0 0 0 1.5 0V6.5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v2.75a.75.75 0 0 0 1.5 0v-2.5a.25.25 0 0 1 .5 0"/></svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-standing-dres girl" viewBox="0 0 16 16"><path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m-.5 12.25V12h1v3.25a.75.75 0 0 0 1.5 0V12h1l-1-5v-.215a.285.285 0 0 1 .56-.078l.793 2.777a.711.711 0 1 0 1.364-.405l-1.065-3.461A3 3 0 0 0 8.784 3.5H7.216a3 3 0 0 0-2.868 2.118L3.283 9.079a.711.711 0 1 0 1.365.405l.793-2.777a.285.285 0 0 1 .56.078V7l-1 5h1v3.25a.75.75 0 0 0 1.5 0Z"/></svg>
                                                @endif
                                                {{-- <p>{{$r->sex == 'm' ? 'UOMO': 'DONNA'}}</p> --}}
                                            </div>
                                        </div>
                                        {{-- <div class="actions">
                                            
                                            
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                                    <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                                    <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                                                </svg>
                                            </a>
                                        </div> --}}
                                    </label>
                                @endforeach
                                
                            </div>
                            
                            <section class="desc"> 
                                <label class="label_c" for="note">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M161 191L228.4 123.6C266.6 85.4 318.4 64 372.4 64C484.9 64 576.1 155.2 576.1 267.6C576.1 314 560.3 358.7 531.6 394.6C508 377.8 479.2 367.9 448.1 367.9C417 367.9 388.2 377.8 364.7 394.5L161 191zM304 512C304 521.7 305 531.1 306.8 540.2C287 535 268.8 524.7 254.1 510C241.9 497.8 222.2 497.8 210 510L160.6 559.4C150 570 135.6 576 120.6 576C89.4 576 64 550.7 64 519.4C64 504.4 70 490 80.6 479.4L130 430C142.2 417.8 142.2 398.1 130 385.9C108.3 364.2 96.1 334.7 96.1 304C96.1 274.6 107.2 246.4 127.2 225L330.6 428.6C313.9 452.1 304 480.9 304 512zM448 416C501 416 544 459 544 512C544 565 501 608 448 608C395 608 352 565 352 512C352 459 395 416 448 416z"/></svg>
                                    Tipo prenotazione 
                                </label>
                                <select name="lesson" id="">
                                    <option value="0">Partita</option>
                                    <option value="1">Lezione</option>
                                    <option value="2">Torneo</option>
                                </select>
                            </section>
                            <p class="desc"> 
                                <label class="label_c" for="note">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-body-text" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M0 .5A.5.5 0 0 1 .5 0h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 0 .5m0 2A.5.5 0 0 1 .5 2h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m9 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-9 2A.5.5 0 0 1 .5 4h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m5 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-12 2A.5.5 0 0 1 .5 6h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8 0a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-8 2A.5.5 0 0 1 .5 8h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m7 0a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-7 2a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                    </svg>
                                    Note 
                                </label>
                                <textarea name="message" id="note" cols="30" rows="10" ></textarea>
                            </p>
                        </div>
                        <div class="actions w-100">
                            <button class="my_btn_3 ml-auto" name="type_res" value="singola" id="smbt_btn" type="submit">Conferma</button>
                            {{-- <button class="my_btn_1 ml-auto" value="1" name="mail" type="submit">Conferma e Avvisa giocatori</button> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Qui appariranno le fasce orarie -->
        <div id="slots" class="my-3"></div>
    </form>

    <form  action="{{ route('admin.settings.cancelDates')}}"   method="POST">
        @csrf
        <!-- Modal -->
            @php $i= 0; @endphp
        <div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModal1Label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mymodal_calendar">
                <div class="modal-content  mymodal_make_res">
                    <div class="modal-body box_container">
                        <div id="c2" class="carousel slide my_carousel" >
                            <div class="carousel-indicators">
                                @foreach ($year as $m)
                                    <button  type="button" data-bs-target="#c2" data-bs-slide-to="{{$i}}"
                                    @if ($currentMonth == $m['month'] && $currentYear == $m['year']) class="active" aria-current="true"@endif
                                    aria-label="{{ 'Slide ' . $i }}"></button>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                            <div class="top_line">
                                <button class="prev_btn" type="button" data-bs-target="#c2" data-bs-slide="prev">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-left-fill" viewBox="0 0 16 16">
                                    <path d="m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z"/>
                                    </svg>
                                </button>
                                <button class="post_btn" type="button" data-bs-target="#c2" data-bs-slide="next">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-right-fill" viewBox="0 0 16 16">
                                    <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/>
                                    </svg>
                                </button>
                            </div>
                            <div id="calendar" class="carousel-inner">
                                @php $i = 0; @endphp
                                @foreach ($year as $m)
                                    <div class="carousel-item
                                    @if ($currentMonth == $m['month'] && $currentYear == $m['year'])
                                        active 
                                    @endif
                                    ">
                                        <h2 class="my">{{['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'][$m['month']]}} - {{$m['year']}}</h2>
                                        <div class="calendar">
                                        
                                            <div class="c-name">
                                                @php
                                                $day_name = ['lunedì', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'sabato', 'domenica'];
                                                @endphp
                                                @foreach ($day_name as $item)
                                                    <h4>{{$item}}</h4>
                                                @endforeach
                                            </div>
                                            <div class="calendar_page">

                                                @foreach ($m['days'] as $d)

                                                    <input type="checkbox" name="day_off[]" id="{{$d['date']}}" value="{{$d['date']}}"
                                                    @if (!$d['status']) checked @endif
                                                    >
                                                    <label for="{{$d['date']}}"
                                                        class="day  
                                                        @if($currentMonth == $m['month'] && $currentYear == $m['year'] && $currentDay == $d['day']) day-active @endif " 
                                                        style="grid-column-start:{{$d['day_w'] }}"
                                                    >        
                                                        <p class="p_day">{{$d['day']}}</p>
                                                        @if (($d['reserved_match'] + $d['reserved_lesson'] + $d['reserved_tournament'] + $d['reserved_dinner']) > 0)
                                                            <div class="bookings">
                                                                @if ($d['reserved_lesson'] > 0)
                                                                    <div>
                                                                        {{$d['reserved_lesson']}} 
                                                                        <!--!Font Awesome Free v7.1.0 by fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M192 144C222.9 144 248 118.9 248 88C248 57.1 222.9 32 192 32C161.1 32 136 57.1 136 88C136 118.9 161.1 144 192 144zM176 576L176 416C176 407.2 183.2 400 192 400C200.8 400 208 407.2 208 416L208 576C208 593.7 222.3 608 240 608C257.7 608 272 593.7 272 576L272 240L400 240C417.7 240 432 225.7 432 208C432 190.3 417.7 176 400 176L384 176L384 128L576 128L576 320L384 320L384 288L320 288L320 336C320 362.5 341.5 384 368 384L592 384C618.5 384 640 362.5 640 336L640 112C640 85.5 618.5 64 592 64L368 64C341.5 64 320 85.5 320 112L320 176L197.3 176C151.7 176 108.8 197.6 81.7 234.2L14.3 324.9C3.8 339.1 6.7 359.1 20.9 369.7C35.1 380.3 55.1 377.3 65.7 363.1L112 300.7L112 576C112 593.7 126.3 608 144 608C161.7 608 176 593.7 176 576z"/></svg>
                                                                    </div>
                                                                @endif
                                                                @if ($d['reserved_match'] > 0)
                                                                    <div>
                                                                        {{$d['reserved_match']}} 
                                                                        <!--!Font Awesome Free v7.1.0 by fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M161 191L228.4 123.6C266.6 85.4 318.4 64 372.4 64C484.9 64 576.1 155.2 576.1 267.6C576.1 314 560.3 358.7 531.6 394.6C508 377.8 479.2 367.9 448.1 367.9C417 367.9 388.2 377.8 364.7 394.5L161 191zM304 512C304 521.7 305 531.1 306.8 540.2C287 535 268.8 524.7 254.1 510C241.9 497.8 222.2 497.8 210 510L160.6 559.4C150 570 135.6 576 120.6 576C89.4 576 64 550.7 64 519.4C64 504.4 70 490 80.6 479.4L130 430C142.2 417.8 142.2 398.1 130 385.9C108.3 364.2 96.1 334.7 96.1 304C96.1 274.6 107.2 246.4 127.2 225L330.6 428.6C313.9 452.1 304 480.9 304 512zM448 416C501 416 544 459 544 512C544 565 501 608 448 608C395 608 352 565 352 512C352 459 395 416 448 416z"/></svg>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="bookings top">
                                                                @if ($d['reserved_tournament'] > 0)
                                                                    <div>
                                                                        {{$d['reserved_tournament']}} 
                                                                        <!--!Font Awesome Free v7.1.0 by fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M208.3 64L432.3 64C458.8 64 480.4 85.8 479.4 112.2C479.2 117.5 479 122.8 478.7 128L528.3 128C554.4 128 577.4 149.6 575.4 177.8C567.9 281.5 514.9 338.5 457.4 368.3C441.6 376.5 425.5 382.6 410.2 387.1C390 415.7 369 430.8 352.3 438.9L352.3 512L416.3 512C434 512 448.3 526.3 448.3 544C448.3 561.7 434 576 416.3 576L224.3 576C206.6 576 192.3 561.7 192.3 544C192.3 526.3 206.6 512 224.3 512L288.3 512L288.3 438.9C272.3 431.2 252.4 416.9 233 390.6C214.6 385.8 194.6 378.5 175.1 367.5C121 337.2 72.2 280.1 65.2 177.6C63.3 149.5 86.2 127.9 112.3 127.9L161.9 127.9C161.6 122.7 161.4 117.5 161.2 112.1C160.2 85.6 181.8 63.9 208.3 63.9zM165.5 176L113.1 176C119.3 260.7 158.2 303.1 198.3 325.6C183.9 288.3 172 239.6 165.5 176zM444 320.8C484.5 297 521.1 254.7 527.3 176L475 176C468.8 236.9 457.6 284.2 444 320.8z"/></svg>
                                                                    </div>
                                                                @endif
                                                                @if ($d['reserved_dinner'] > 0)
                                                                    <div>
                                                                        {{$d['reserved_dinner']}} 
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fork-knife" viewBox="0 0 16 16">
                                                                            <path d="M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z"/>
                                                                        </svg>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>

                        </div>
                        <div class="actions w-100 mt-3">
                            <button class="my_btn_2 btn_delete" type="button" data-bs-dismiss="modal" >Annulla</button>
                            <button class="my_btn_3" type="submit">Conferma</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Qui appariranno le fasce orarie -->
        <div id="slots" class="my-3"></div>
    </form>


</div>
<script>
document.addEventListener("DOMContentLoaded", () => {

    // === SVG variabili (mettile come vuoi) ===
    const bookedSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M161 191L228.4 123.6C266.6 85.4 318.4 64 372.4 64C484.9 64 576.1 155.2 576.1 267.6C576.1 314 560.3 358.7 531.6 394.6C508 377.8 479.2 367.9 448.1 367.9C417 367.9 388.2 377.8 364.7 394.5L161 191zM304 512C304 521.7 305 531.1 306.8 540.2C287 535 268.8 524.7 254.1 510C241.9 497.8 222.2 497.8 210 510L160.6 559.4C150 570 135.6 576 120.6 576C89.4 576 64 550.7 64 519.4C64 504.4 70 490 80.6 479.4L130 430C142.2 417.8 142.2 398.1 130 385.9C108.3 364.2 96.1 334.7 96.1 304C96.1 274.6 107.2 246.4 127.2 225L330.6 428.6C313.9 452.1 304 480.9 304 512zM448 416C501 416 544 459 544 512C544 565 501 608 448 608C395 608 352 565 352 512C352 459 395 416 448 416z"/></svg>';

    // === Stato globale delle selezioni ===
    const selectedSlots = {};

    const buttons = document.querySelectorAll("#calendar button");
    const slotsContainer = document.getElementById("slots");

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {

            // Salva le selezioni del giorno corrente prima di cambiare
            saveCurrentDaySelections();

            // rimuovo la classe selected da tutte e imposto quella corrente
            buttons.forEach(e => e.classList.remove('selected'));
            const day = JSON.parse(btn.dataset.day);
            btn.classList.add('selected');

            // Pulisco il contenitore e inserisco il bottone
            slotsContainer.innerHTML = `
                <div class="fields" id="fields"></div>
                <div class="mt-4">
                    <button id="unique-btn" type="button" class="m-auto my_btn_7 mt-4" style='display:none;' data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Prenota
                    </button>
                </div>
            `;

            const fieldsContainer = document.getElementById("fields");

            // Ciclo i campi
            Object.entries(day.fields).forEach(([fieldName, fieldData]) => {
                const { times, match } = fieldData;

                // titolo del field
                const title = document.createElement("h4");
                title.classList.add("font-bold", "mb-2");
                title.textContent = fieldName;
                if(match !== undefined){
                    const span = document.createElement("span");
                    span.innerHTML = `${match} ${bookedSvg}`;
                    title.appendChild(span);
                }
                fieldsContainer.appendChild(title);

                const fieldDiv = document.createElement("div");
                fieldDiv.classList.add("field");
                fieldDiv.dataset.field = fieldName;

                if (times.length > 0) {
                    times.forEach(slot => {
                        const safeTime = String(slot.time).replace(/[^a-z0-9]/gi, '_');
                        const inputId = `i_${safeTime}_${fieldName}`;
                        const timeDiv = document.createElement("div");
                        timeDiv.classList.add("time");

                        if (slot.status == 1) {
                            timeDiv.classList.add("trainer_slot");
                            timeDiv.style.setProperty('--flag', slot.flag);
                        }

                        let role = '{{ auth()->user()->role }}';
                        let userId = {{ auth()->user()->id }};

                        if (slot.status == 2) {
                            if(slot.lesson == 1){
                                timeDiv.classList.add("lesson");
                                timeDiv.style.setProperty('--flag', slot.flag);
                            }else if(slot.lesson == 2){
                                timeDiv.classList.add("booked","trophy");
                            }else{
                                timeDiv.classList.add("booked");
                            }
                            timeDiv.classList.add(`bk_${slot.d > 3 ? '3' : slot.d}`);
                            const link = document.createElement("a");
                            link.href = `/admin/reservations/${slot.id}`;

                            const spanTime = document.createElement("span");
                            spanTime.classList.add("time_b");
                            spanTime.textContent = slot.time;

                            const spanSubj = document.createElement("span");
                            spanSubj.classList.add("booking_subject");
                            spanSubj.textContent = `#${slot.booking_subject}`;

                            link.appendChild(spanTime);
                            link.appendChild(spanSubj);
                            timeDiv.appendChild(link);

                        } else if((role == 'admin' || slot.trainer_id.includes(userId) && slot.status == 1) || slot.status == 0) {
                            const input = document.createElement("input");
                            input.type = "checkbox";
                            input.classList.add("slot-checkbox");
                            input.value = `${day.date}/${slot.time}/${fieldName}`;
                            input.id = inputId;

                            const label = document.createElement("label");
                            label.htmlFor = inputId;
                            if(!slot.s) label.classList.add("middle");
                            label.textContent = slot.time;

                            timeDiv.appendChild(input);
                            timeDiv.appendChild(label);

                            // ==== Aggiorna selezioni subito quando si clicca ====
                            input.addEventListener("change", () => {
                                const currentDayBtn = document.querySelector("#calendar button.selected");
                                if (!currentDayBtn) return;
                                const currentDate = JSON.parse(currentDayBtn.dataset.day).date;

                                if (!selectedSlots[currentDate]) selectedSlots[currentDate] = {};
                                if (!selectedSlots[currentDate][fieldName]) selectedSlots[currentDate][fieldName] = [];

                                const arr = selectedSlots[currentDate][fieldName];

                                if (input.checked) {
                                    if (!arr.includes(slot.time)) arr.push(slot.time);
                                } else {
                                    const index = arr.indexOf(slot.time);
                                    if (index > -1) arr.splice(index, 1);
                                    if (arr.length === 0) delete selectedSlots[currentDate][fieldName];
                                    if (Object.keys(selectedSlots[currentDate]).length === 0) delete selectedSlots[currentDate];
                                }

                                checkCheckboxes();
                                console.log(selectedSlots);
                            });

                        } else {
                            const label = document.createElement("label");
                            label.htmlFor = inputId;
                            if(!slot.s) label.classList.add("middle");
                            label.textContent = slot.time;
                            timeDiv.appendChild(label);
                        }

                        fieldDiv.appendChild(timeDiv);
                    });
                } else {
                    const p = document.createElement("p");
                    p.classList.add("null_p");
                    p.textContent = "Campo non disponibile";
                    fieldDiv.appendChild(p);
                }

                fieldsContainer.appendChild(fieldDiv);
            });

            // Ripristina eventuali selezioni salvate
            restoreSelections(day.date);
            checkCheckboxes();
        });
    });

    // ==== Funzioni ausiliarie ====
    function saveCurrentDaySelections() {
        const activeBtn = document.querySelector("#calendar button.selected");
        if (!activeBtn) return;

        const { date } = JSON.parse(activeBtn.dataset.day);
        selectedSlots[date] = {};

        document.querySelectorAll("#fields .field").forEach(fieldDiv => {
            const fieldName = fieldDiv.dataset.field;
            const checked = fieldDiv.querySelectorAll(".slot-checkbox:checked");

            if (!checked.length) return;

            selectedSlots[date][fieldName] = [];
            checked.forEach(cb => {
            selectedSlots[date][fieldName].push(cb.value.split("/")[1]);
            });
        });

        if (!Object.keys(selectedSlots[date]).length) {
            delete selectedSlots[date];
        }
    }

    function restoreSelections(date) {
        if (!selectedSlots[date]) return;

        document.querySelectorAll(".slot-checkbox").forEach(cb => {
            const [, time, fieldName] = cb.value.split("/");
            if (selectedSlots[date][fieldName] && selectedSlots[date][fieldName].includes(time)) {
                cb.checked = true;
            }
        });
    }

    function checkCheckboxes() {
        let totalSelections = 0;
        const uniqueBtn = document.getElementById("unique-btn");

        Object.values(selectedSlots).forEach(fields => {
            Object.values(fields).forEach(times => totalSelections += times.length);
        });

        if (uniqueBtn) {
            uniqueBtn.style.display = totalSelections > 0 ? "block" : "none";

            // Cambia testo se selezioni su più giorni
            uniqueBtn.textContent = Object.keys(selectedSlots).length > 1 ? "Prenotazione multipla" : "Prenota";
            document.querySelector("#smbt_btn").value = Object.keys(selectedSlots).length > 1 ? "multipla" : "singola";
        }
    }

    // ==== Al submit aggiungo input hidden per tutte le selezioni ====
    document.querySelector("form").addEventListener("submit", event => {
        const form = event.target;

        form.querySelectorAll(".dynamic-slot").forEach(e => e.remove());

        Object.entries(selectedSlots).forEach(([date, fields]) => {
            Object.entries(fields).forEach(([field, times]) => {
                times.forEach(time => {
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "times[]";
                    input.value = `${date}/${time}/${field}`;
                    input.classList.add("dynamic-slot");
                    form.appendChild(input);
                });
            });
        });
    });

    const searchInput = document.getElementById('playerSearch');
    const items = document.querySelectorAll('.res_item');

    searchInput.addEventListener('input', function () {
        const value = this.value.toLowerCase().trim();

        items.forEach(item => {
            const searchText = item.dataset.search;

            if (searchText.includes(value)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

});
</script>



@endsection



{{-- <script>
document.addEventListener("DOMContentLoaded", () => {
    const buttons = document.querySelectorAll("#calendar button");
    const slotsContainer = document.getElementById("slots");




    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            // rimuovo la classe selected da tutte e imposto quella corrente
            buttons.forEach(e => e.classList.remove('selected'));
            const day = JSON.parse(btn.dataset.day);
            btn.classList.add('selected');

            // Pulisco il contenitore e inserisco anche il bottone (nascosto)
            slotsContainer.innerHTML = `
                <div class="fields" id="fields"></div>
                <div class="mt-4">
                    <button id="unique-btn" type="button" class="m-auto my_btn_7 mt-4" style='display:none;' data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Prenota
                    </button>
                </div>
            `;

            const fieldsContainer = document.getElementById("fields");
            // Ciclo i campi
            Object.entries(day.fields).forEach(([fieldName, fieldData]) => {
                const { times, match } = fieldData; // <-- NUOVO

                // titolo della sezione
                const title = document.createElement("h4");
                title.classList.add("font-bold", "mb-2");
                title.textContent = fieldName;
                if(match !== undefined){
                    const spanm = document.createElement("span");
                    spanm.innerHTML = `${match} <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 640 640"><path d="M161 191L228.4 123.6C266.6 85.4 318.4 64 372.4 64C484.9 64 576.1 155.2 576.1 267.6C576.1 314 560.3 358.7 531.6 394.6C508 377.8 479.2 367.9 448.1 367.9C417 367.9 388.2 377.8 364.7 394.5L161 191zM304 512C304 521.7 305 531.1 306.8 540.2C287 535 268.8 524.7 254.1 510C241.9 497.8 222.2 497.8 210 510L160.6 559.4C150 570 135.6 576 120.6 576C89.4 576 64 550.7 64 519.4C64 504.4 70 490 80.6 479.4L130 430C142.2 417.8 142.2 398.1 130 385.9C108.3 364.2 96.1 334.7 96.1 304C96.1 274.6 107.2 246.4 127.2 225L330.6 428.6C313.9 452.1 304 480.9 304 512zM448 416C501 416 544 459 544 512C544 565 501 608 448 608C395 608 352 565 352 512C352 459 395 416 448 416z"/></svg>`; // <-- NUOVO
                    title.appendChild(spanm);
                }
                fieldsContainer.appendChild(title);

                // contenitore del field
                const fieldDiv = document.createElement("div");
                fieldDiv.classList.add("field");

                if (times.length > 0) {
                    times.forEach(slot => {
                        // sanitizzo il time per creare un id HTML sicuro
                        const safeTime = String(slot.time).replace(/[^a-z0-9]/gi, '_');
                        const inputId = `i_${safeTime}_${fieldName}`;

                        // contenitore del singolo slot
                        const timeDiv = document.createElement("div");
                        timeDiv.classList.add("time");
                        if (slot.status == 1) {
                            timeDiv.classList.add("trainer_slot");
                        }
                        let role = '{{ auth()->user()->role }}';
                        let userId = {{ auth()->user()->id }};
                        if (slot.status == 2) {
                            // se è già prenotato, creo link e span
                            timeDiv.classList.add("booked", `bk_${slot.d}`);
                            const link = document.createElement("a");
                            link.href = `/admin/reservations/${slot.id}`;

                            const spanTime = document.createElement("span");
                            spanTime.classList.add("time_b");
                            spanTime.textContent = slot.time;

                            const spanSubj = document.createElement("span");
                            spanSubj.classList.add("booking_subject");
                            spanSubj.textContent = `#${slot.booking_subject}`;

                            link.appendChild(spanTime);
                            link.appendChild(spanSubj);
                            timeDiv.appendChild(link);
                            
                        } else if((role == 'admin' || slot.trainer_id.includes(userId) && slot.status == 1) || slot.status == 0) {
                            const input = document.createElement("input");
                            input.type = "checkbox";
                            input.classList.add("slot-checkbox");
                            input.value = `${day.date}/${slot.time}/${fieldName}`;
                            input.name = `times[]`;
                            input.id = inputId;
                            input.addEventListener("change", checkCheckboxes)

                            const label = document.createElement("label");
                            label.htmlFor = inputId;
                            if(!slot.s)label.classList.add("middle");
                            label.textContent = slot.time;

                            timeDiv.appendChild(input);
                            timeDiv.appendChild(label);

                            // esempio: aggiungo event listener qui se vuoi controllare selezioni
                            input.addEventListener("change", () => {
                                const checked = fieldDiv.querySelectorAll(".slot-checkbox:checked");
                                console.log(`Nel campo ${fieldName} ci sono ${checked.length} selezioni`);
                            });
                        } else {
                            const label = document.createElement("label");
                            label.htmlFor = inputId;
                            if(!slot.s)label.classList.add("middle");
                            label.textContent = slot.time;

                            timeDiv.appendChild(label);
                        }

                        fieldDiv.appendChild(timeDiv);
                    });
                } else {
                    // nessuna fascia oraria
                    const p = document.createElement("p");
                    p.classList.add("null_p");
                    p.textContent = "Campo non disponibile";
                    fieldDiv.appendChild(p);
                }

                // aggiungo il blocco completo al container
                fieldsContainer.appendChild(fieldDiv);
            });



            // Assicuro che il bottone sia nascosto dopo il render
            const uniqueBtn = document.getElementById("unique-btn");
            if (uniqueBtn) uniqueBtn.style.display = "none";
        });
    });
    function checkCheckboxes() {
        const checked = document.querySelectorAll("#fields input[type='checkbox']:checked");
        const extraDiv = document.getElementById("unique-btn");

        if (checked.length > 0) {
            extraDiv.style.display = "block"; // mostra
        } else {
            extraDiv.style.display = "none";  // nascondi
        }
    }
});


</script> --}}

