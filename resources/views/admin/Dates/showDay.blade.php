@extends('layouts.base')

@section('contents')
@if (session('success'))
@php
    $data = session('success');
@endphp
<div class="alert alert-success">
    {{ $data }}
</div>
@endif
@php
    $status = [
        'Annullata',
        'Confermata',
        'Da vedere!',
        'Pagata da vedere! ',
        '',
        'Confermata e Pagata',
        'Annullata e Rimborsata',
    ]
@endphp
 

<h1 class="p-3">{{$day[0]->day}} - {{['', 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre'][$day[0]->month]}}</h1>
<div class="slim_cont">
    @foreach ($day as $t)
    @php
        if($t->reserving !== '0'){
            $reserving = json_decode($t->reserving);
        }else{
            $reserving = false;
        }
        if($t->availability !== '0'){
            $availability = json_decode($t->availability);
        }else{
            $availability = false;
        }
        if($t->visible !== '0'){
            $visible = json_decode($t->visible);
        }else{
            $visible = false;
        }
    @endphp

    <div class="slim_time ">
        <section class="s1">
            <div class="top">
                <h3>{{$t->time}}</h3>
                <button type="button" class="my_btn_1" data-bs-toggle="modal" data-bs-target="#exampleModal{{$t->id}}">Vedi dettagli</button>
                <div class="dati">
                    @if (isset($reserving->table) && $reserving->table !== 0)
                        <p>Tavoli: {{$reserving->table}} / {{$availability->table}}</p>   
                    @endif
    
                    @if (isset($reserving->table_1) && $reserving->table_1 !== 0)
                        <p> {{config('configurazione.set_time_dt')[0]}} : {{$reserving->table_1}} / {{$availability->table_1}}</p>   
                    @endif
                    @if (isset($reserving->table_2) && $reserving->table_2 !== 0)
                        <p> {{config('configurazione.set_time_dt')[1]}} : {{$reserving->table_2}} / {{$availability->table_2}}</p>   
                    @endif
    
                    @if (isset($reserving->cucina_1) && $reserving->cucina_1 !== 0)
                        <p>{{config('configurazione.set_time')[1]}}: {{$reserving->cucina_1}} / {{$availability->cucina_1}}</p>   
                    @endif
                    @if (isset($reserving->cucina_2) && $reserving->cucina_2 !== 0)
                        <p>{{config('configurazione.set_time')[2]}}: {{$reserving->cucina_2}} / {{$availability->cucina_2}}</p>   
                    @endif
                    @if (isset($reserving->asporto) && $reserving->asporto !== 0)
                        <p>{{config('configurazione.set_time_2')[1]}}: {{$reserving->asporto}} / {{$availability->asporto}}</p>   
                    @endif
                    
                    @if (isset($reserving->domicilio) && $reserving->domicilio !== 0)
                        <p>Domicilio: {{$reserving->domicilio}} / {{$availability->domicilio}}</p>                
                    @endif
                    
                    
                </div>
            </div>
            <div class="bottom">
                @if (count($t->or))
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="main_svg bi bi-inboxes-fill" viewBox="0 0 16 16">
                        <path d="M4.98 1a.5.5 0 0 0-.39.188L1.54 5H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0A.5.5 0 0 1 10 5h4.46l-3.05-3.812A.5.5 0 0 0 11.02 1zM3.81.563A1.5 1.5 0 0 1 4.98 0h6.04a1.5 1.5 0 0 1 1.17.563l3.7 4.625a.5.5 0 0 1 .106.374l-.39 3.124A1.5 1.5 0 0 1 14.117 10H1.883A1.5 1.5 0 0 1 .394 8.686l-.39-3.124a.5.5 0 0 1 .106-.374zM.125 11.17A.5.5 0 0 1 .5 11H6a.5.5 0 0 1 .5.5 1.5 1.5 0 0 0 3 0 .5.5 0 0 1 .5-.5h5.5a.5.5 0 0 1 .496.562l-.39 3.124A1.5 1.5 0 0 1 14.117 16H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .121-.393z"/>
                    </svg>
                    <div class="tikets">
                        @foreach ($t->or as $tk) 
                        <a href="{{ route('admin.orders.show', $tk->id) }}" class="tiket {{ in_array($tk->status, [0, 6]) ? 'null' : '' }} ">
                            <p>{{$tk->name}}</p>
                            @if ($tk->comune)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="or_svg bi bi-truck" viewBox="0 0 16 16">
                                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="or_svg bi bi-bag-fill" viewBox="0 0 16 16">
                                    <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4z"/>
                                </svg>
                            @endif
                            <div class="person">
                                <div>€{{$tk->tot_price / 100}}</div>
                            </div>
                            <p class="status {{in_array($tk->status, [2, 3]) ? 's-1' : ''}}">
                                {{$status[$tk->status]}}
                            </p>
                            @if ($tk->message)  
                            <div class="message">
                                NOTE: {{$tk->message}}
                            </div>
                            @endif
                        </a>
                        @endforeach
                    </div>
                @endif
                @if (count($t->res))
                    {{-- <h4>Prenotazioni al tavolo</h4> --}}
                    <svg xmlns="http://www.w3.org/2000/svg"  fill="currentColor" class="main_svg bi bi-person-lines-fill" viewBox="0 0 16 16">
                        <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/>
                    </svg>                      
                    <div class="tikets">
                        @foreach ($t->res as $tk) 
                        <a href="{{ route('admin.reservations.show', $tk->id) }}" class="tiket {{ in_array($tk->status, [0, 6]) ? 'null' : '' }} ">
                            <p>{{$tk->name}}</p>
                            @if (config('configurazione.double_t'))
                            <p class="sala" >{{config('configurazione.set_time_dt')[$tk->sala - 1] }}</p>   
                            @endif
                            <div class="person">
                                @php $p = json_decode($tk->n_person, 1);
                                @endphp
                                <div style="{{$p['adult'] ? '' : 'opacity:.3'}}">  
                                    <span>{{$p['adult']}}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                    </svg>
                                </div>
                                <div style="{{$p['child'] ? '' : 'opacity:.3'}}">  
                                    <span>{{$p['child']}}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-person-arms-up child" viewBox="0 0 16 16">
                                        <path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                                        <path d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="status {{in_array($tk->status, [2, 3]) ? 's-1' : ''}}">{{$status[$tk->status]}}</p>
                            @if ($tk->message)  
                            <div class="message">
                                NOTE: {{$tk->message}}
                            </div>
                            @endif
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        
        <form action="{{ route('admin.dates.status') }}" method="POST" class="modal fade data_set" id="exampleModal{{$t->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            @csrf
            <input type="hidden" name="id" value="{{$t->id}}">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title" id="exampleModalLabel">MOODIFICA slot orario {{$t->time}}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body data_set_body">
                        <section>
                            <h3>Prenotazioni a quest'orario</h3>
                            @php $i = 0 @endphp
                            <div class="cont">
                                @foreach ($reserving as $key => $value)
                                <div class="not-set">
                                        @if(config('configurazione.typeOfOrdering') && config('configurazione.pack') !== 3 && config('configurazione.double_t'))
                                            <h5>{{config('configurazione.set_time_dt')[$i]}}:</h5>
                                        @elseif(config('configurazione.typeOfOrdering') && config('configurazione.pack') == 3 && config('configurazione.double_t'))
                                            <h5>{{config('configurazione.set_time_dt')[$i + 1]}}:</h5>
                                        @elseif(config('configurazione.typeOfOrdering') && config('configurazione.pack') !== 3)
                                            <h5>{{config('configurazione.set_time')[$i]}}:</h5>
                                        @elseif(config('configurazione.typeOfOrdering') && config('configurazione.pack') == 3) 
                                            <h5>{{config('configurazione.set_time')[$i + 1]}}:</h5>
                                        @elseif(config('configurazione.double_t') && !config('configurazione.typeOfOrdering'))
                                            <h5>{{config('configurazione.set_time_2_dt')[$i]}}:</h5>
                                        @else
                                            <h5>{{config('configurazione.set_time_2')[$i]}}:</h5>
                                        @endif
                                        <span class="">{{$value}}</span>
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                        </section>
                        <section>
                            <h3>Modifica le disponibilità</h3>
                            @php $i = 0 @endphp
                            <div class="cont">
                                @foreach ($availability as $key => $value)
                                    <div class="set">
                                         @if(config('configurazione.typeOfOrdering') && config('configurazione.pack') !== 3 && config('configurazione.double_t'))
                                            <h5>{{config('configurazione.set_time_dt')[$i]}}:</h5>
                                        @elseif(config('configurazione.typeOfOrdering') && config('configurazione.pack') == 3 && config('configurazione.double_t'))
                                            <h5>{{config('configurazione.set_time_dt')[$i + 1]}}:</h5>
                                        @elseif(config('configurazione.typeOfOrdering') && config('configurazione.pack') !== 3)
                                            <h5>{{config('configurazione.set_time')[$i]}}:</h5>
                                        @elseif(config('configurazione.typeOfOrdering') && config('configurazione.pack') == 3) 
                                            <h5>{{config('configurazione.set_time')[$i + 1]}}:</h5>
                                        @elseif(config('configurazione.double_t') && !config('configurazione.typeOfOrdering'))
                                            <h5>{{config('configurazione.set_time_2_dt')[$i]}}:</h5>
                                        @else
                                            <h5>{{config('configurazione.set_time_2')[$i]}}:</h5>
                                        @endif
                                        <input type="number" name="av{{$key}}" value="{{$value}}" class="">   
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                        </section>
                        <section>
                            <h3>Modifica le visibilità</h3>
                            @php $i = 0 @endphp
                            <div class="cont">
                                @foreach ($visible as $key => $value)
                                    <div class="set">
                                         @if(config('configurazione.typeOfOrdering') && config('configurazione.pack') !== 3 && config('configurazione.double_t'))
                                            <h5>{{config('configurazione.set_time_dt')[$i]}}:</h5>
                                        @elseif(config('configurazione.typeOfOrdering') && config('configurazione.pack') == 3 && config('configurazione.double_t'))
                                            <h5>{{config('configurazione.set_time_dt')[$i + 1]}}:</h5>
                                        @elseif(config('configurazione.typeOfOrdering') && config('configurazione.pack') !== 3)
                                            <h5>{{config('configurazione.set_time')[$i]}}:</h5>
                                        @elseif(config('configurazione.typeOfOrdering') && config('configurazione.pack') == 3) 
                                            <h5>{{config('configurazione.set_time')[$i + 1]}}:</h5>
                                        @elseif(config('configurazione.double_t') && !config('configurazione.typeOfOrdering'))
                                            <h5>{{config('configurazione.set_time_2_dt')[$i]}}:</h5>
                                        @else
                                            <h5>{{config('configurazione.set_time_2')[$i]}}:</h5>
                                        @endif
                                        <select name="vis{{$key}}" class="">
                                            <option @if ($value) selected @endif value="1">SI</option>
                                            <option @if (!$value) selected @endif value="0">NO</option>
                                        </select>
                                    </div>
                                    @php $i ++ @endphp
                                @endforeach
                            </div>
                        </section>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="my_btn_2">Modifica</button>
                        <button type="button" class="my_btn_1" data-bs-dismiss="modal">Annulla</button>
                    </div>
                </div>
            </div>
        </form>
        
    </div>
    @endforeach
</div>

@endsection