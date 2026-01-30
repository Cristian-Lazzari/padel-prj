@extends('layouts.base')

@section('contents')

@php
    // Parsing della stringa
    $datetime = Carbon\Carbon::parse($reservation->date_slot)->locale('it');

    // Variabili separate
    $data = $datetime->translatedFormat('l j F'); // es: giovedì 25 settembre
    $ora = $datetime->format('H:i');              // es: 18:00
    $ora_fine = $datetime->addMinutes($m_during * $reservation->duration)->format('H:i'); // es: 19:00

    $dinner = json_decode($reservation->dinner, true);

@endphp
    
<div class="page_nav">
    <div class="view_box pt-5">
        <h1>Dettagli {{$reservation->lesson ? ' della LEZIONE' : 'del MATCH'}}</h1>
        <div class="central">
            <h2><span>Prenotato da:</span> <a class="my_btn_5" href="{{route('admin.players.show', $reservation->booking_subject)}}">{{$reservation->booking_subject_name}} {{$reservation->booking_subject_surname}}</a></h2>
            <div class=" my_btn_2 ml-auto 
            @if($reservation->status == 0)  btn_delete @endif
            ">{{$reservation->status == 1 ? 'Confermata' : 'Annullata'}}</div>
        </div>
        <div class="box_container">
            <div class="box ">
                <p>
                    <strong class="field">Campo </strong>
                    <span>{{$reservation->field}}</span>
                </p>
                <p>
                    <strong class="field">Data</strong>
                    <span class="date">{{$data}}</span>
                </p>
                
            </div>
            <div class="box ">
                <p>
                    <strong class="field">Orario </strong>
                    <span>{{$ora}}  -  {{$ora_fine}}</span>
                </p>
                <p>
                    <strong>Cena</strong>
                    @if ($dinner['status'])
                    
                    <span> 
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/>
                        </svg>
                        {{$dinner['guests']}} &nbsp; &nbsp;
                
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                            <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
                            <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
                            <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
                        </svg>
                        {{ $dinner['time'] }}</span>
                    
                    @else
                        <span>Non prenotata</span>
                    @endif
                </p>
            </div>
            
            <div class="box note">
                <div>
                    <h2>Note</h2> 
                    <p>
                        @if ($reservation->message)
                            {{$reservation->message}}
                        @else
                            Nessuna nota
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="box players">
            @if (!$reservation->players->isEmpty()) <h3>Giocatori presenti alla partita</h3> @endif
           
            @foreach ($reservation->players as $p)
                <div class="res_item">
                    <div class="left">
                        <div class="time_slot">#{{$p->nickname}}</div>
                        <div class="date">{{$p->name}} {{$p->surname}}</div>
                    </div>
                    <div class="player_center">
                        <div class="donut-wrapper" style="--percent: {{ $p->level / 5 * 100}}">
                            <p>
                                {{ $p->level }}
                            </p>
                        </div>
                    </div>
                    <div class="actions">
                        
                        <a href="{{route('admin.players.show', $p)}}" class="show">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
            @if ($reservation->players->isEmpty())
                <p>Nessun giocatore associato a questa prenotazione</p>
            @endif
        </div>
        <div class="more_info my-5" >
            <p>
                <strong>Creato il</strong> {{$reservation->created_at->format('d/m/Y')}},
                <strong>Aggiornato il</strong> {{$reservation->updated_at->format('d/m/Y')}}
                <strong>ID prenotazione: </strong> {{$reservation->id}}
            </p>
        </div>
        <div class="action_page">
            <a class="my_btn_7" href="{{ route('admin.reservations.edit', $reservation) }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                </svg>
                Modifica
            </a>
            @if ($reservation->status !== '0')     
                <button class="my_btn_6 btn_delete" type="button" data-bs-toggle="modal" data-bs-target="#exampleModaldelete">
                    Annulla Match
                </button>
            @endif

                
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
                        <h3>Sei sicuro di voler anullare questo Match?</h3>
                        <p>Annullando il match da questa finestra manderai automaticamente la mail con la disdetta a {{$reservation->booking_subject_name}}</p>
                        <form class="w-100" action="{{ route('admin.reservations.cancel') }}" method="post" >
                            @method('POST')
                            @csrf
                            <input value="{{$reservation->id}}" type="hidden" name="id">
                            <button class="my_btn_1 btn_delete m-auto mt-4" type="submit">Annulla</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
