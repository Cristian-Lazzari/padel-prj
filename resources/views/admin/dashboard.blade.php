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
                                    @if ($d['reserved'] > 0)
                                        <span class="bookings">{{$d['reserved']}} 
                                            <!--!Font Awesome Free v7.1.0 by fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M161 191L228.4 123.6C266.6 85.4 318.4 64 372.4 64C484.9 64 576.1 155.2 576.1 267.6C576.1 314 560.3 358.7 531.6 394.6C508 377.8 479.2 367.9 448.1 367.9C417 367.9 388.2 377.8 364.7 394.5L161 191zM304 512C304 521.7 305 531.1 306.8 540.2C287 535 268.8 524.7 254.1 510C241.9 497.8 222.2 497.8 210 510L160.6 559.4C150 570 135.6 576 120.6 576C89.4 576 64 550.7 64 519.4C64 504.4 70 490 80.6 479.4L130 430C142.2 417.8 142.2 398.1 130 385.9C108.3 364.2 96.1 334.7 96.1 304C96.1 274.6 107.2 246.4 127.2 225L330.6 428.6C313.9 452.1 304 480.9 304 512zM448 416C501 416 544 459 544 512C544 565 501 608 448 608C395 608 352 565 352 512C352 459 395 416 448 416z"/></svg>
                                        </span>
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
                                                        @if ($d['reserved'] > 0)
                                                            <span class="bookings">{{$d['reserved']}} 
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M161 191L228.4 123.6C266.6 85.4 318.4 64 372.4 64C484.9 64 576.1 155.2 576.1 267.6C576.1 314 560.3 358.7 531.6 394.6C508 377.8 479.2 367.9 448.1 367.9C417 367.9 388.2 377.8 364.7 394.5L161 191zM304 512C304 521.7 305 531.1 306.8 540.2C287 535 268.8 524.7 254.1 510C241.9 497.8 222.2 497.8 210 510L160.6 559.4C150 570 135.6 576 120.6 576C89.4 576 64 550.7 64 519.4C64 504.4 70 490 80.6 479.4L130 430C142.2 417.8 142.2 398.1 130 385.9C108.3 364.2 96.1 334.7 96.1 304C96.1 274.6 107.2 246.4 127.2 225L330.6 428.6C313.9 452.1 304 480.9 304 512zM448 416C501 416 544 459 544 512C544 565 501 608 448 608C395 608 352 565 352 512C352 459 395 416 448 416z"/></svg>
                                                            </span>
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
                    const spanm = document.createElement("span");
                    spanm.innerHTML = `${match} ${bookedSvg}`;
                    title.appendChild(spanm);
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
                            }else{
                                timeDiv.classList.add("booked", `bk_${slot.d}`);
                            }
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

