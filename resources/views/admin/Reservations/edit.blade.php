@extends('layouts.base')

@section('contents')

@php
    // Parsing della stringa
    $datetime = Carbon\Carbon::parse($reservation->date_slot)->locale('it');

    // Variabili separate
    $data = $datetime->translatedFormat('l j F'); // es: giovedì 25 settembre
    $ora = $datetime->format('H:i');              // es: 18:00
    $ora_fine = $datetime->addHour()->addMinutes(30)->format('H:i'); // es: 19:00

    $dinner = json_decode($reservation->dinner, true);

@endphp
    
<div class="page_nav">
    <form class="view_box pt-5" action="{{ route('admin.reservations.update', $reservation) }}"  enctype="multipart/form-data"  method="POST">
        @csrf
        @method('PUT')

        <div class="central">
            <h1>Modifica il MATCH</h1>
            <h2><span>Prenotato da:</span> <a class="my_btn_5" href="{{route('admin.players.show', $reservation->booking_subject)}}">{{$reservation->booking_subject_name}} {{$reservation->booking_subject_surnname}}</a></h2>
            <select name="status" id="">
                <option value="1">Confermata</option>
                <option value="0">Annullata</option>
            </select>
        </div>
        <div class="box_container">
            <div class="box personal">
                <h2 class="field">CAMPO {{$reservation->field}}</h2>
                <div class="time_slot">
                    <span>{{$ora}}</span> -  
                    <span class="second">{{$ora_fine}}
                    </span>
                </div>
                <div class="date">{{$data}}</div>
            </div>
            <div class="box contact ">
                <h2>Cena</h2>
                @if ($dinner['status'])
                <p>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                        <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                    </svg>
                    <input class="w-100" value="{{ $dinner['guests'] }}" type="number" name="guests"  placeholder=" Numero di ospiti ">
                    @error('guest') <p class="error">{{ $message }}</p> @enderror
                </p>
                <p>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                        <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
                        <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                        <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                    </svg>
                    <input class="w-100" value="{{ $dinner['time'] }}" type="time" name="time" id="time" placeholder=" Inserisci un time ">
                    @error('time') <p class="error">{{ $message }}</p> @enderror
                </p>
                @else
                    <p>Non prenotata</p>
                @endif
            </div>
  

        </div>
       
        <div class="box players new_players">
            <h3 class="first">Giocatori presenti alla partita</h3>
            
            @foreach ($players as $p)
                <input type="checkbox" name="players[]" id="{{$p->id}}" value="{{$p->id}}"
                @if ($reservation->players->contains($p)) checked style="order:-1" @endif
                >
                <label for="{{$p->id}}"
                @if ($reservation->players->contains($p)) style="order:-1" @endif
                class="res_item">
                    <div class="left">
                        <div class="time_slot">#{{$p->nickname}}</div>
                        <div class="date">{{$p->name}} {{$p->surname}}</div>
                    </div>
                    <div class="center">
                        <p>{{$p->level}} / 5</p>
                    </div>
                    <div class="actions">
                        
                        <a href="{{route('admin.players.show', $p)}}" class="show">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                            </svg>
                        </a>
                    </div>
                </label>
            @endforeach
            <h3 class="second">Aggiungi giocatori</h3>
        </div>

         <div class="floating bottom">
            <div class="int">
                <button class="my_btn_3"  type="submit">Conferma modifiche</button>
            </div>
        </div>
    </form>
</div>